# PHP Implementation of Zabbix Sender

- ✅ Unencrypted connection to Zabbix Server
- ✅ TLS PSK connection to Zabbix Server
- ❌ TLS RSA connection to Zabbix Server (in progress)

All sender options in zabbix docs https://www.zabbix.com/documentation/current/en/manpages/zabbix_sender

## Install

```shell
composer require webmasterskaya/php-zabbix-sender
```

## Quick start

1. Create trapper item on Zabbix Server (https://www.zabbix.com/documentation/current/en/manual/config/items/itemtypes/trapper)
2. Init connection
  ```php
  $sender = new \Webmasterskaya\ZabbixSender\ZabbixSender([
		'server' => '127.0.0.1',
		'host' => 'testhost',
	]);
  ```
3. Send data
  ```php
  $sender->send('testtrapper', 'testdata');
  ```
4. Check result
  ```php
  echo $sender->getLastResponseInfo()->getTotal(); #1  
  ```