<?php

namespace Webmasterskaya\ZabbixSender\Resolver;

/**
 * Provides functionality for resolving data before sending it to Zabbix.
 */
final class DataResolver
{
	/**
	 * Resolves data before sending.
	 *
	 * @param   array  $options  An array of data.
	 *
	 * @return array
	 */
	public static function resolve(array $options): array
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
