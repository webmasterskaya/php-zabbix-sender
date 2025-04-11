<?php

namespace Webmasterskaya\ZabbixSender\Resolver;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

/**
 * Provides functionality for resolving options of ZabbixSender.
 */
final class OptionsResolver
{
	/**
	 * Resolves ZabbixSender options.
	 */
	public static function resolve(array $options): array
	{
		static $resolver;

		if (!isset($resolver))
		{
			$resolver = new \Symfony\Component\OptionsResolver\OptionsResolver();

			$resolver
				->define('host')
				->allowedTypes('string');

			$resolver
				->define('server')
				->allowedTypes('string')
				->required()
				->allowedValues(
					Validation::createIsValidCallable(new Assert\Hostname(requireTld: true)),
					Validation::createIsValidCallable(new Assert\Ip(version: Assert\Ip::ALL))
				);

			$resolver
				->define('port')
				->default(10051)
				->allowedTypes('int');

			$resolver
				->define('timeout')
				->default(30)
				->allowedTypes('int');

			$resolver->setIgnoreUndefined();
		}

		return $resolver->resolve($options);
	}
}