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
	 * @param array $options An array of data.
	 *
	 * @return array
	 */
	public static function resolve(array $options, ZabbixSenderInterface $zabbixSender): array
	{
		$resolver = new \Symfony\Component\OptionsResolver\OptionsResolver();

		$resolver
			->define('host')
			->allowedTypes('string');

		$resolver
			->define('key')
			->allowedTypes('string')
			->required();

		$resolver
			->define('value')
			->allowedTypes('string')
			->required();

		$resolver->setIgnoreUndefined();

		if (!$zabbixSender->getOption('host')) {
			$resolver->setRequired('host');
		} else {
			$resolver->setDefault('host', $zabbixSender->getOption('host'));
		}

		return $resolver->resolve($options);
	}
}
