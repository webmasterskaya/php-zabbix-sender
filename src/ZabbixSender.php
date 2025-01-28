<?php

namespace Webmasterskaya\ZabbixSender;

use Webmasterskaya\ZabbixSender\Options\Resolver;

class ZabbixSender
{
    /**
     * @var array
     */
    private array $options = [];

    /**
     * @var resource
     */
    private $socket;

    private ?string $_lastResponseInfo = null;
    private ?array $_lastResponseArray = null;
    private ?int $_lastProcessed = null;
    private ?int $_lastFailed = null;
    private ?float $_lastSpent = null;
    private ?int $_lastTotal = null;

    public function __construct(array $options = [])
    {
        $this->options = Resolver::resolve($options);
    }

    public function send(
        string $key,
        string|array|object $value,
        ?string $host = null
    ): bool {
        $data = [];

        if (!empty($host)) {
            $data['host'] = trim($host);
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

        $data = $this->dataEncode($data);

        $this->open();

        $data_size = strlen($data);
        $sent_size = $this->write($data);

        if ($sent_size === false || $sent_size != $data_size) {
            throw new \RuntimeException('cannot receive response');
        }

        $response = $this->read();

        if ($response === false) {
            throw new \RuntimeException('cannot receive response');
        }

        $this->close();

        if (!str_starts_with($response, "ZBXD")) {
            $this->_clearLastResponseData();
            throw new \RuntimeException('invalid protocol header in receive data');
        }

        $responseData  = substr($response, 13);
        $responseArray = json_decode($responseData, true);
        if (is_null($responseArray)) {
            throw new \RuntimeException('invalid json data in receive data');
        }
        $this->_lastResponseArray = $responseArray;
        $this->_lastResponseInfo  = $responseArray['info'];
        $parsedInfo               = $this->_parseResponseInfo($this->_lastResponseInfo);
        $this->_lastProcessed     = $parsedInfo['processed'];
        $this->_lastFailed        = $parsedInfo['failed'];
        $this->_lastSpent         = $parsedInfo['spent'];
        $this->_lastTotal         = $parsedInfo['total'];
        if ($responseArray['response'] == "success") {
            return true;
        } else {
            $this->_clearLastResponseData();

            return false;
        }
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

    public function getOptions()
    {
        return $this->getOptions();
    }

    protected function open(): void
    {
        $options = Resolver::resolve($this->options, true);

        $this->socket = @fsockopen($options['server'],
            $options['port'],
            $error_code,
            $error_message,
            5);

        if (!$this->socket) {
            throw new \RuntimeException(sprintf('%s, %s', $error_code, $error_message));
        }
    }

    protected function write(string $data): false|int
    {
        if (!$this->socket) {
            $this->open();
        }

        $total_written = 0;
        $length        = strlen($data);
        while ($total_written < $length) {
            $written = @fwrite($this->socket, $data);
            if ($written === false) {
                return false;
            } else {
                $total_written += $written;
                $data          = substr($data, $written);
            }
        }

        return $total_written;
    }

    protected function read(): false|string
    {
        if (!$this->socket) {
            $this->open();
        }

        $data = "";
        while (!feof($this->socket)) {
            $buffer = fread($this->socket, 8192);
            if ($buffer === false) {
                return false;
            }
            $data .= $buffer;
        }

        return $data;
    }

    protected function close(): void
    {
        if ($this->socket) {
            fclose($this->socket);
        }
    }

    protected function dataEncode(array $data): string
    {
        $data = json_encode([
            'request' => 'sender data',
            'data'    => [$data]
        ]);

        $data_length = strlen($data);

        $data_header = "ZBXD\1" . pack("VV", $data_length, 0x00);

        return ($data_header . $data);
    }

    protected function _parseResponseInfo($info = null): ?array
    {
        # info: "Processed 1 Failed 1 Total 2 Seconds spent 0.000035"
        $parsedInfo = null;
        if (isset($info)) {
            list(, $processed, , $failed, , $total, , , $spent) = explode(" ", $info);
            $parsedInfo = [
                "processed" => (int)$processed,
                "failed"    => (int)$failed,
                "total"     => (int)$total,
                "spent"     => (float)$spent,
            ];
        }

        return $parsedInfo;
    }

    private function _clearLastResponseData(): void
    {
        $this->_lastResponseInfo  = null;
        $this->_lastResponseArray = null;
        $this->_lastProcessed     = null;
        $this->_lastFailed        = null;
        $this->_lastSpent         = null;
        $this->_lastTotal         = null;
    }
}