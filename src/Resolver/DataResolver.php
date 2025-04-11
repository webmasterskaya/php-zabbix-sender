<?php

namespace Webmasterskaya\ZabbixSender\Resolver;

use Webmasterskaya\ZabbixSender\ZabbixSenderInterface;

final class DataResolver
{
	public static function resolve(array $options, ZabbixSenderInterface $zabbixSender): array
	{
		static $resolver;

		if (!isset($resolver))
		{
			$resolver = new \Symfony\Component\OptionsResolver\OptionsResolver();

			$resolver
				->define('host')
				->allowedTypes('string')
				->required();

			$resolver
				->define('key')
				->allowedTypes('string')
				->required();

			$resolver
				->define('value')
				->allowedTypes('string')
				->required();

			$resolver->setIgnoreUndefined();
		}

		return $resolver->resolve($options);
	}
}