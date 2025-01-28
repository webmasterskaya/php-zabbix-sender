<?php

namespace Webmasterskaya\ZabbixSender\Options;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

abstract class Resolver
{
    protected static function getResolver(): OptionsResolver
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
                ->required()
                ->allowedValues(
                    Validation::createIsValidCallable(new Assert\Hostname(requireTld: true)),
                    Validation::createIsValidCallable(new Assert\Ip(version: Assert\Ip::ALL)));

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

        return $resolver;
    }

    public static function resolve(array $options): array
    {
        return static::getResolver()->resolve($options);
    }

    public static function resolveData(array $data, array $config): array
    {
        static $resolver;

        if (!isset($resolver)) {
            $resolver = new OptionsResolver();

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

        return $resolver->resolve($data);
    }
}