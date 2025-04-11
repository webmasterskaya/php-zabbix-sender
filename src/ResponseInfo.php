<?php

namespace Webmasterskaya\ZabbixSender;

class ResponseInfo implements ResponseInfoInterface
{
	private ?int $processed;
	private ?int $failed;
	private ?int $total;
	private ?float $spent;

	public function __construct(string $response)
	{
		# response: "Processed 1 Failed 1 Total 2 Seconds spent 0.000035"
		list(, $processed, , $failed, , $total, , , $spent) = explode(" ", $response);

		$this->processed = (int) $processed;
		$this->failed    = (int) $failed;
		$this->total     = (int) $total;
		$this->spent     = (float) $spent;
	}

	public function getProcessed(): ?int
	{
		return $this->processed;
	}

	public function getFailed(): ?int
	{
		return $this->failed;
	}

	public function getSpent(): ?float
	{
		return $this->spent;
	}

	public function getTotal(): ?int
	{
		return $this->total;
	}
}