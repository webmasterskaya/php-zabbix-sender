<?php

namespace Webmasterskaya\ZabbixSender;

/**
 * Defines methods for sending data to Zabbix Server.
 */
interface ZabbixSenderInterface
{
	/**
	 * Starts batch processing of data.
	 *
	 * @return static The current instance of the class.
	 */
	public function batch(): static;

	/**
	 * Executes the data sending process to Zabbix.
	 *
	 * @return bool The result of the operation (true for success, false for failure).
	 */
	public function execute(): bool;

	/**
	 * Sends data to Zabbix.
	 *
	 * @param null|string $key The data key.
	 * @param null|string|array|object $value The data value.
	 * @param null|string $host The host name (optional).
	 * @return bool The result of the operation (true for success, false for failure).
	 */
	public function send(
		null|string $key,
		null|string|array|object $value,
		null|string $host = null
	): bool;

	/**
	 * Returns an array of current options.
	 *
	 * @return array An array of options.
	 */
	public function getOptions(): array;

	/**
	 * Returns the value of a specific option.
	 *
	 * @param string $option The name of the option.
	 * @param mixed $default The default value (if the option is not set).
	 * @return mixed The value of the option or the default value.
	 */
	public function getOption(string $option, mixed $default = null): mixed;
}