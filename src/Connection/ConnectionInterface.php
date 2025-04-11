<?php

namespace Webmasterskaya\ZabbixSender\Connection;

interface ConnectionInterface
{
    public function open();

    public function read(): string;

    public function write(string $data);

    public function close();
}