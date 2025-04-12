<?php

namespace Webmasterskaya\ZabbixSender;

/**
 * Provides methods for working with Zabbix response information.
 */
class ResponseInfo implements ResponseInfoInterface
{
	/**
	 * The number of successfully processed items.
	 *
	 * @var int|null
	 */
	private ?int $processed;

	/**
	 * The number of failed processing attempts.
	 *
	 * @var int|null
	 */
	private ?int $failed;

	/**
	 * The total number of processed items.
	 *
	 * @var int|null
	 */
	private ?int $total;

	/**
	 * The time spent on processing.
	 *
	 * @var float|null
	 */
	private ?float $spent;

	/**
	 * Constructor for the ResponseInfo class.
	 *
	 * @param   string  $response  A Zabbix response string in the format "Processed X Failed Y Total Z Seconds spent N".
	 */
	public function __construct(string $response)
	{
		# response: "Processed 1 Failed 1 Total 2 Seconds spent 0.000035"
		[, $processed, , $failed, , $total, , , $spent] = explode(" ", $response);

		$this->processed = (int) $processed;
		$this->failed    = (int) $failed;
		$this->total     = (int) $total;
		$this->spent     = (float) $spent;
	}

	/**
	 * Returns the number of successfully processed items.
	 *
	 * @return int|null The number of successfully processed items, or null if data is unavailable.
	 */
	public function getProcessed(): ?int
	{
		return $this->processed;
	}

	/**
	 * Returns the number of failed processing attempts.
	 *
	 * @return int|null The number of failed attempts, or null if data is unavailable.
	 */
	public function getFailed(): ?int
	{
		return $this->failed;
	}

	/**
	 * Returns the time spent on processing.
	 *
	 * @return float|null The time spent on processing (in seconds), or null if data is unavailable.
	 */
	public function getSpent(): ?float
	{
		return $this->spent;
	}

	/**
	 * Returns the total number of processed items.
	 *
	 * @return int|null The total number of processed items, or null if data is unavailable.
	 */
	public function getTotal(): ?int
	{
		return $this->total;
	}
}
