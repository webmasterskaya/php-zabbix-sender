<?php

namespace Webmasterskaya\ZabbixSender\Options;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class Resolver
{
    public static function resolve(array $options): array
    {
        static $resolver;

        if (!isset($resolver)) {
            $resolver = new OptionsResolver();

            $resolver
                ->define('host')
                ->allowedTypes('string');

            $resolver
                ->define('server')
                ->allowedTypes('string')
                ->normalize(fn(Options $options, string $value) => explode(',', $value)[0]);

            $resolver
                ->define('port')
                ->default(1051)
                ->allowedTypes('int');

            $resolver
                ->define('tls-connect')
                ->allowedTypes('string')
                ->allowedValues(['unencrypted', 'psk', 'cert'])
                ->default('unencrypted');

            $resolver->setIgnoreUndefined();
        }

        return $resolver->resolve($options);
    }

    public static function resolveData(array $data, array $config): array
    {
        static $resolver;

        if (!isset($resolver)) {
            $resolver = new OptionsResolver();

            $resolver
                ->define('hostname')
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

        return $resolver->resolve($data);
    }
}