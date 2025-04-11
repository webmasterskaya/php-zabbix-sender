<?php

namespace Webmasterskaya\ZabbixSender;

/**
 * Defines methods for retrieving Zabbix response information.
 */
interface ResponseInfoInterface
{
	/**
	 * Returns the number of successfully processed items.
	 *
	 * @return int|null The number of successfully processed items, or null if data is unavailable.
	 */
	public function getProcessed(): ?int;

	/**
	 * Returns the number of failed processing attempts.
	 *
	 * @return int|null The number of failed attempts, or null if data is unavailable.
	 */
	public function getFailed(): ?int;

	/**
	 * Returns the time spent on processing.
	 *
	 * @return float|null The time spent on processing (in seconds), or null if data is unavailable.
	 */
	public function getSpent(): ?float;

	/**
	 * Returns the total number of processed items.
	 *
	 * @return int|null The total number of processed items, or null if data is unavailable.
	 */
	public function getTotal(): ?int;
}