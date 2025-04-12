<?php

namespace Webmasterskaya\ZabbixSender\Connection;

/**
 * Interface defines methods for working connection from the host.
 */
interface ConnectionInterface
{
	/**
	 * Opens the connection.
	 */
	public function open();

	/**
	 * Reads data from the connection.
	 *
	 * @return false|string Data or false in case of an error.
	 */
	public function read(): false|string;

	/**
	 * Writes data to the connection.
	 *
	 * @param   string  $data  Data to write.
	 *
	 * @return false|int The number of bytes written or false in case of an error.
	 */
	public function write(string $data): false|int;

	/**
	 * Closes the connection.
	 */
	public function close();
}
