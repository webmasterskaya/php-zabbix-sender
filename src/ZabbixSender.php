<?php

namespace Webmasterskaya\ZabbixSender;

use Webmasterskaya\ZabbixSender\Options\Resolver;

class ZabbixSender
{
    public function __construct(array $options = [])
    {
        $this->options = Resolver::resolve($options);
    }

    /**
     * @var array
     */
    private array $options = [];

    public function send(
        ?string $hostname,
        string $key,
        string|array|object $value
    ): bool {
        $data = [];

        if (!empty($hostname)) {
            $data['host'] = trim($hostname);
        }

        if (empty($data['host']) || $data['host'] === '-') {
            $data['host'] = $this->options['host'];
        }

        $data['key'] = trim($key);

        if (is_object($value)) {
            $value = match (true) {
                $value instanceof \JsonSerializable => json_encode($value,
                    JSON_FORCE_OBJECT | JSON_BIGINT_AS_STRING | JSON_UNESCAPED_UNICODE),
                $value instanceof \ArrayAccess => (array)$value,
                default => get_object_vars($value),
            };
        }

        if (is_array($value)) {
            $value = json_encode($value, JSON_FORCE_OBJECT | JSON_BIGINT_AS_STRING | JSON_UNESCAPED_UNICODE);
        }

        $data['value'] = trim($value);

        $data = Resolver::resolveData($data, $this->options);

        return false;
    }

    public function whitHost(string $host): static
    {
        return new static(array_merge_recursive($this->getOptions(), ['host' => $host]));
    }

    public function whitPort(int $port): static
    {
        return new static(array_merge_recursive($this->getOptions(), ['port' => $port]));
    }

    public function whitServer(string $server): static
    {
        return new static(array_merge_recursive($this->getOptions(), ['server' => $server]));
    }

    public function whitTlsConnect(string $tls_connect): static
    {
        return new static(array_merge_recursive($this->getOptions(), ['tls-connect' => $tls_connect]));
    }

    public function getOptions()
    {
        return $this->getOptions();
    }

    protected function getTransport()
    {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        if (!$socket) {
            throw new \RuntimeException("Can't create TCP socket");
        }

        $packet = "ZBXD\1" . pack('V', strlen($data)) . "\0\0\0\0" . $data;

        $socketConnected = socket_connect(
            $socket,
            $this->options['server'],
            $this->options['port']
        );
    }
}