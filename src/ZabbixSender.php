<?php

namespace Webmasterskaya\ZabbixSender;

use Webmasterskaya\ZabbixSender\Options\Resolver;

class ZabbixSender
{
    /**
     * @var array
     */
    private array $options = [];

    public function __construct(array $options = [])
    {
        $this->options = Resolver::resolve($options);
    }

    public function send(
        ?array $data = null,
        ?string $hostname = null,
        ?string $key = null,
        ?string $timestamp = null,
        ?string $ns = null,
        ?string $value = null
    ): bool {
        if (!isset($data)) {
            $data = [];
        }

        if (isset($hostname)) {
            $data['host'] = $hostname;
        }

        if (empty($data['host']) || trim($data['host']) === '-') {
            $data['host'] = $this->options['host'];
        }

        if (isset($key)) {
            $data['key'] = $key;
        }

        if (isset($value)) {
            $data['value'] = $value;
        }

        $data = Resolver::resolveData($data, $this->options);

        return false;
    }

    public function whitHost(string $host): static
    {
        $clone = clone $this;

        return $clone->setHost($host);
    }

    public function setHost(string $host): static
    {
        $this->options['host'] = $host;
        $this->options         = Resolver::resolve($this->options);

        return $this;
    }

    public function whitPort(int $port): static
    {
        $clone = clone $this;

        return $clone->setPort($port);
    }

    public function setPort(int $port): static
    {
        $this->options['port'] = $port;
        $this->options         = Resolver::resolve($this->options);

        return $this;
    }
}