<?php

namespace Webmasterskaya\ZabbixSender\Connection;

use RuntimeException;
use Webmasterskaya\ZabbixSender\Resolver\OptionsResolver;

use function is_resource;
use function sprintf;

/**
 * Implements PKS connections from host.
 *
 * @internal
 */
final class PskConnection implements ConnectionInterface
{
	/**
	 * @var resource|null
	 */
	private $process = null;

	private array $pipes = [];

	private string $command;


	public function __construct(array $options)
	{
		$options = OptionsResolver::resolve($options);

		$this->command = sprintf(
			'openssl s_client -connect %s:%d -psk_identity %s -psk %s',
			escapeshellarg($options['server']),
			(int)$options['port'],
			escapeshellarg($options['tls-psk-identity']),
			escapeshellarg($options['tls-psk'])
		);
	}

	/**
	 * @inheritDoc
	 */
	public function open(): void
	{
		if ($this->process !== null) {
			throw new RuntimeException('Connection is already open.');
		}

		$descriptors = [
			0 => ['pipe', 'r'], // stdin
			1 => ['pipe', 'w'], // stdout
			2 => ['pipe', 'w'], // stderr
		];

		$this->process = proc_open($this->command, $descriptors, $this->pipes);

		if (!is_resource($this->process)) {
			throw new RuntimeException('Failed to open connection.');
		}
	}

	/**
	 * @inheritDoc
	 */
	public function read(): false|string
	{
		if ($this->process === null || !isset($this->pipes[1])) {
			return false;
		}

		$output = stream_get_contents($this->pipes[1]);
		return $output ?: false;
	}

	/**
	 * @inheritDoc
	 */
	public function write(string $data): false|int
	{
		if ($this->process === null || !isset($this->pipes[0])) {
			return false;
		}

		// Write data to stdin
		$bytesWritten = fwrite($this->pipes[0], $data);
		return $bytesWritten ?: false;
	}

	/**
	 * @inheritDoc
	 */
	public function close(): void
	{
		if ($this->process === null) {
			return;
		}

		foreach ($this->pipes as $pipe) {
			if (is_resource($pipe)) {
				fclose($pipe);
			}
		}

		proc_close($this->process);
		$this->process = null;
		$this->pipes = [];
	}
}
