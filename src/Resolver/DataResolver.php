<?php

namespace Webmasterskaya\ZabbixSender\Resolver;

use Webmasterskaya\ZabbixSender\ZabbixSenderInterface;

/**
 * Provides functionality for resolving data before sending it to Zabbix.
 */
final class DataResolver
{
	/**
	 * Resolves data before sending.
	 *
	 * @param   array                                               $options An array of data.
	 * @param   \Webmasterskaya\ZabbixSender\ZabbixSenderInterface  $zabbixSender An instance of ZabbixSender.
	 *
	 * @return array
	 */
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