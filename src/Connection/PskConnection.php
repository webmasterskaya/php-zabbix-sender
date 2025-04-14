<?php

namespace Webmasterskaya\ZabbixSender\Connection;

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

	/**
	 * @var array Connection options
	 */
	private array $options;

	public function __construct(array $options)
	{
		if (!function_exists('proc_open')) {
			throw new \RuntimeException('ProcOpen function is not available');
		}

		$this->options = $options;
	}

	public function open()
	{
		$command = sprintf(
			'openssl s_client -connect %s:%d -psk_identity %s -psk %s',
			escapeshellarg($this->options['server']),
			(int)$this->options['port'],
			escapeshellarg($this->options['tls-psk-identity']),
			escapeshellarg($this->options['tls-psk'])
		);

		if ($this->process !== null) {
			throw new \RuntimeException('Connection is already open.');
		}

		$descriptors = [
			0 => ['pipe', 'r'], // stdin
			1 => ['pipe', 'w'], // stdout
			2 => ['pipe', 'w'], // stderr
		];

		$this->process = proc_open($command, $descriptors, $this->pipes);

		if (!is_resource($this->process)) {
			throw new \RuntimeException('Failed to open connection.');
		}
	}

	public function read(): string
	{
		if ($this->process === null || !isset($this->pipes[1])) {
			return false;
		}

		$output = stream_get_contents($this->pipes[1]);
		return $output ?: false;
	}

	public function write(string $data): false|int
	{
		if ($this->process === null || !isset($this->pipes[0])) {
			return false;
		}

		// Write data to stdin
		$bytesWritten = fwrite($this->pipes[0], $data);
		return $bytesWritten ?: false;
	}

	public function close()
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
