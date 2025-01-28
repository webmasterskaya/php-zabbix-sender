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
    /**
     * @var true
     */
    private bool $batch = false;

    protected array $data;

    public function __construct(array $options = [])
    {
        $this->options = Resolver::resolve($options);
    }

    public function batch(): static
    {
        $this->batch = true;

        return $this;
    }

    public function execute(): bool
    {
        $data = $this->packedData($this->data);

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
            $this->data = [];
            $this->batch = false;

            return true;
        } else {
            $this->_clearLastResponseData();

            return false;
        }
    }

    protected function prepareData(
        string $key,
        string|array|object $value,
        ?string $host = null
    ) {
        if (!empty($host)) {
            $data['host'] = trim($host);
        }

        if (empty($data['host']) || $data['host'] === '-') {
            $data['host'] = $this->getOptions()['host'];
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

        return Resolver::resolveData($data, $this->options);
    }

    public function send(
        null|string $key,
        null|string|array|object $value,
        null|string $host = null
    ): bool {
        $data = $this->prepareData($key, $value, $host);

        if ($this->batch) {
            $this->data[] = $data;
        } else {
            $this->data = [$data];

            return $this->execute();
        }

        return true;
    }

    public function getOptions(): array
    {
        return Resolver::resolve($this->options);
    }

    protected function open(): void
    {
        $this->socket = @fsockopen($this->options['server'],
            $this->options['port'],
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

    protected function packedData(array $data): string
    {
        $data = json_encode([
            'request' => 'sender data',
            'data'    => $data
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