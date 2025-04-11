<?php

namespace Webmasterskaya\ZabbixSender\Resolver;

final class OptionsResolver
{
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