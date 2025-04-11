<?php

namespace Webmasterskaya\ZabbixSender;

use Webmasterskaya\Utility\String\CasesHelper;
use Webmasterskaya\ZabbixSender\Connection\ConnectionInterface;
use Webmasterskaya\ZabbixSender\Resolver\DataResolver;
use Webmasterskaya\ZabbixSender\Resolver\OptionsResolver;

/**
 * Provide functionality for sending data to Zabbix Server.
 */
class ZabbixSender implements ZabbixSenderInterface
{
	protected array $data;
	/**
	 * @var array
	 */
	private array $options = [];
	private ConnectionInterface $connection;
	private ?ResponseInfoInterface $lastResponseInfo = null;
	/**
	 * @var true
	 */
	private bool $batch = false;

	public function __construct(array $options = [])
	{
		$this->options = OptionsResolver::resolve($options);

		$connection      = trim($this->options['connection_type'] ?? 'no-encryption').'-connection';
		$connectionClass = __NAMESPACE__.'\\Connection\\'.CasesHelper::classify($connection);

		if (!class_exists($connectionClass))
		{
			throw new \RuntimeException('Unable to create a Connection instance: '.$connectionClass);
		}

		$this->connection = new $connectionClass($this->options);
	}

	public function getLastResponseInfo(): ?ResponseInfoInterface
	{
		if ($this->batch)
		{
			throw new \RuntimeException('Unable to get last response info during batch processing.');
		}

		return $this->lastResponseInfo;
	}

	public function batch(): static
	{
		$this->batch = true;

		return $this;
	}

	public function send(
		null|string $key,
		null|string|array|object $value,
		null|string $host = null
	): bool {
		$data = $this->prepareData($key, $value, $host);

		if ($this->batch)
		{
			$this->data[] = $data;
		}
		else
		{
			$this->data = [$data];

			return $this->execute();
		}

		return true;
	}

	protected function prepareData(
		string $key,
		string|array|object $value,
		?string $host = null
	): array {
		if (!empty($host))
		{
			$data['host'] = trim($host);
		}

		if (empty($data['host']) || $data['host'] === '-')
		{
			$data['host'] = $this->getOptions()['host'];
		}

		$data['key'] = trim($key);

		if (is_object($value))
		{
			$value = match (true)
			{
				$value instanceof \JsonSerializable => json_encode(
					$value,
					JSON_FORCE_OBJECT | JSON_BIGINT_AS_STRING | JSON_UNESCAPED_UNICODE
				),
				$value instanceof \ArrayAccess => (array) $value,
				default => get_object_vars($value),
			};
		}

		if (is_array($value))
		{
			$value = json_encode($value, JSON_FORCE_OBJECT | JSON_BIGINT_AS_STRING | JSON_UNESCAPED_UNICODE);
		}

		$data['value'] = trim($value);

		return DataResolver::resolve($data, $this);
	}

	public function getOptions(): array
	{
		return $this->options;
	}

	public function execute(): bool
	{
		$data = $this->packedData($this->data);

		$this->open();

		$data_size = strlen($data);
		$sent_size = $this->write($data);

		if ($sent_size === false || $sent_size != $data_size)
		{
			throw new \RuntimeException('cannot receive response');
		}

		$response = $this->read();

		if ($response === false)
		{
			throw new \RuntimeException('cannot receive response');
		}

		$this->close();

		if (!str_starts_with($response, "ZBXD"))
		{
			$this->lastResponseInfo = null;
			throw new \RuntimeException('invalid protocol header in receive data');
		}

		$responseData  = substr($response, 13);
		$responseArray = json_decode($responseData, true);
		if (is_null($responseArray))
		{
			throw new \RuntimeException('invalid json data in receive data');
		}

		$this->lastResponseInfo = new ResponseInfo($responseArray['info']);

		if ($responseArray['response'] == "success")
		{
			$this->data  = [];
			$this->batch = false;

			return true;
		}
		else
		{
			$this->lastResponseInfo = null;

			return false;
		}
	}

	protected function packedData(array $data): string
	{
		$data = json_encode([
			'request' => 'sender data',
			'data'    => $data,
		]);

		$data_length = strlen($data);

		$data_header = "ZBXD\1".pack("VV", $data_length, 0x00);

		return ($data_header.$data);
	}

	protected function open(): void
	{
		$this->connection->open();
	}

	protected function write(string $data): false|int
	{
		return $this->connection->write($data);
	}

	protected function read(): false|string
	{
		return $this->connection->read();
	}

	protected function close(): void
	{
		$this->connection->close();
	}

	public function getOption(string $option, mixed $default = null): mixed
	{
		return $this->options[$option] ?? $default;
	}
}