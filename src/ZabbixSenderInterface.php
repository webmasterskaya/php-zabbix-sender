<?php

namespace Webmasterskaya\ZabbixSender;

interface ZabbixSenderInterface
{
	public function batch(): static;

	public function execute(): bool;

	public function send(
		null|string $key,
		null|string|array|object $value,
		null|string $host = null
	): bool;

	public function getOptions(): array;

	public function getOption(string $option, mixed $default = null): mixed;
}