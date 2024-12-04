<?php

namespace Webmasterskaya\ZabbixSender\Config;

abstract class Reader
{
    public static function read($filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException("File $filePath not exists.");
        }

        $config = [];

        $file = fopen($filePath, 'r');

        if (!$file) {
            throw new \RuntimeException("Could not read file $filePath");
        }

        while (($line = fgets($file)) !== false) {
            $line = trim($line);

            if (empty($line) || str_starts_with($line, '#')) {
                continue;
            }

            $parts = explode('=', $line, 2);

            if (count($parts) === 2) {
                // Убираем пробелы вокруг ключа и значения
                $key   = trim($parts[0]);
                $value = trim($parts[1]);

                // Записываем значение в массив
                $config[$key] = $value;
            }
        }

        fclose($file);

        return $config;
    }
}