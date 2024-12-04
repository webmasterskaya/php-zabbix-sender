<?php

use Symfony\Component\Validator\Constraints\Hostname;
use Symfony\Component\Validator\Constraints\Ip;
use Symfony\Component\Validator\Validation;
use Webmasterskaya\ZabbixSender\Config\Reader;

require_once dirname(__FILE__) . '/src/Config/Reader.php';
require_once dirname(__FILE__) . '/src/Config/Resolver.php';
require_once dirname(__FILE__) . '/vendor/autoload.php';

var_dump(\Webmasterskaya\ZabbixSender\Config\Resolver::resolveOptions(['server' => 'google.com,aasd.fd', 'foo' => 'bar']));