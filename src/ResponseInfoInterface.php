<?php

namespace Webmasterskaya\ZabbixSender;

interface ResponseInfoInterface
{
	public function getProcessed(): ?int;

	public function getFailed(): ?int;

	public function getSpent(): ?float;

	public function getTotal(): ?int;
}