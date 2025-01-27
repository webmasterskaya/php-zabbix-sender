<?php


use Webmasterskaya\ZabbixSender\Options\Resolver;

require_once dirname(__FILE__) . '/src/Options/Resolver.php';
require_once dirname(__FILE__) . '/vendor/autoload.php';

var_dump(Resolver::resolve(['server' => '::1', 'host' => 'dsaadsdas324', 'foo' => 'bar', 'tls-connect' => 'psk']));