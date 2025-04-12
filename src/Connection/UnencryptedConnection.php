<?php

namespace Webmasterskaya\ZabbixSender\Connection;

use RuntimeException;

use function sprintf;
use function strlen;

/**
 * Implements no encryption connections from host.
 *
 * @internal
 */
final class UnencryptedConnection implements ConnectionInterface
{
	/**
	 * @var ?resource The resource of the connection.
	 */
	private $socket;

	/**
	 * @var array Connection options
	 */
	private array $options;

	public function __construct(array $options = [])
	{
		$this->options = $options;
	}

	/**
	 * @inheritDoc
	 */
	public function write(string $data): false|int
	{
		if (!$this->socket) {
			$this->open();
		}

		$total_written = 0;
		$length        = strlen($data);
		while ($total_written < $length) {
			$written = @fwrite($this->socket, $data);
			if ($written === false) {
				return false;
			}


			$total_written += $written;
			$data          = substr($data, $written);

		}

		return $total_written;
	}


	/**
	 * @inheritDoc
	 */
	public function open(): void
	{
		$this->socket = @fsockopen(
			$this->options['server'],
			$this->options['port'],
			$error_code,
			$error_message,
			5
		);

		if (!$this->socket) {
			throw new RuntimeException(sprintf('%s, %s', $error_code, $error_message));
		}
	}


	/**
	 * @inheritDoc
	 */
	public function read(): false|string
	{
		if (!$this->socket) {
			$this->open();
		}

		$data = "";
		while (!feof($this->socket)) {
			$buffer = fread($this->socket, 8192);
			if ($buffer === false) {
				return false;
			}
			$data .= $buffer;
		}

		return $data;
	}


	/**
	 * @inheritDoc
	 */
	public function close(): void
	{
		if ($this->socket) {
			fclose($this->socket);
		}
	}
}
