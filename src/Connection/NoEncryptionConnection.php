<?php

namespace Webmasterskaya\ZabbixSender\Connection;

use RuntimeException;

/**
 * @internal
 */
final class NoEncryptionConnection implements ConnectionInterface
{

	private $socket;

	private array $options;

	public function __construct(array $options = [])
	{
		$this->options = $options;
	}

	public function write(string $data): false|int
	{
		if (!$this->socket)
		{
			$this->open();
		}

		$total_written = 0;
		$length        = strlen($data);
		while ($total_written < $length)
		{
			$written = @fwrite($this->socket, $data);
			if ($written === false)
			{
				return false;
			}
			else
			{
				$total_written += $written;
				$data          = substr($data, $written);
			}
		}

		return $total_written;
	}

	public function open()
	{
		$this->socket = @fsockopen(
			$this->options['server'],
			$this->options['port'],
			$error_code,
			$error_message,
			5
		);

		if (!$this->socket)
		{
			throw new RuntimeException(sprintf('%s, %s', $error_code, $error_message));
		}
	}

	public function read(): false|string
	{
		if (!$this->socket)
		{
			$this->open();
		}

		$data = "";
		while (!feof($this->socket))
		{
			$buffer = fread($this->socket, 8192);
			if ($buffer === false)
			{
				return false;
			}
			$data .= $buffer;
		}

		return $data;
	}

	public function close(): void
	{
		if ($this->socket)
		{
			fclose($this->socket);
		}
	}
}