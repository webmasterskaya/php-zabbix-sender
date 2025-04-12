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
				->allowedValues(
					Validation::createIsValidCallable(new Assert\Hostname(requireTld: true)),
					Validation::createIsValidCallable(new Assert\Ip(version: Assert\Ip::ALL))
				);

			$resolver
				->define('port')
				->default(1051)
				->allowedTypes('int');

			$resolver
				->define('tls-connect')
				->allowedTypes('string')
				->info(
					'How to connect to server or proxy. Values: unencrypted - connect without encryption (default); psk - connect using TLS and a pre-shared key; cert - connect using TLS and a certificate.'
				)
				->allowedValues('unencrypted', 'psk', 'cert')
				->default('unencrypted');

			$resolver
				->define('tls-ca-file')
				->allowedTypes('string')
				->info('Full pathname of a file containing the top-level CA(s) certificates for peer certificate verification.')
				->allowedValues(Validation::createIsValidCallable(new Assert\File()));

			$resolver
				->define('tls-crl-file')
				->allowedTypes('string')
				->info('Full pathname of a file containing revoked certificates.')
				->allowedValues(Validation::createIsValidCallable(new Assert\File()));

			$resolver
				->define('tls-server-cert-issuer')
				->allowedTypes('string')
				->info('Allowed server certificate issuer.');

			$resolver
				->define('tls-server-cert-subject')
				->allowedTypes('string')
				->info('Allowed server certificate subject.');

			$resolver
				->define('tls-cert-file')
				->allowedTypes('string')
				->info('Full pathname of a file containing the certificate or certificate chain.')
				->allowedValues(Validation::createIsValidCallable(new Assert\File()));

			$resolver
				->define('tls-key-file')
				->allowedTypes('string')
				->info('Full pathname of a file containing the private key.')
				->allowedValues(Validation::createIsValidCallable(new Assert\File()));

			$resolver
				->define('tls-psk-identity')
				->allowedTypes('string')
				->info('PSK-identity string.');

			$resolver
				->define('tls-psk-file')
				->allowedTypes('string')
				->info('Full pathname of a file containing the pre-shared key.')
				->allowedValues(Validation::createIsValidCallable(new Assert\File()));

			$resolver
				->define('tls-cipher')
				->allowedTypes('string')
				->info('GnuTLS priority string (for TLS 1.2 and up) or OpenSSL cipher string (only for TLS 1.2). Override the default ciphersuite selection criteria.');

			$resolver
				->define('tls-cipher13')
				->allowedTypes('string')
				->info(
					'Cipher string for OpenSSL 1.1.1 or newer for TLS 1.3. Override the default ciphersuite selection criteria. This option is not available if OpenSSL version is less than 1.1.1.'
				);

			$resolver
				->define('connection_type')
				->allowedTypes('string')
				->allowedValues('unencrypted', 'psk', 'cert')
				->default('unencrypted');
		}

		$connection_type = 'unencrypted';
		if (isset($options['tls-connect']))
		{
			switch ($options['tls-connect'])
			{
				case 'psk':
					$resolver->setRequired(['tls-psk-identity', 'tls-psk-file']);
					$connection_type = 'psk';
					break;
				case 'cert':
					$resolver->setRequired(['tls-ca-file', 'tls-cert-file', 'tls-key-file', 'tls-key-file']);
					$connection_type = 'certificate';
					break;
			}
		}

		return array_merge(['connection_type' => $connection_type], $resolver->resolve($options));
	}
}
