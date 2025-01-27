<?php

$psk_identity = "test sender";
$psk_key      = file_get_contents(dirname(__FILE__) . '/.zabbix_agentd.psk'); // 32-byte hex string for a 256-bit key

// Create a socket
$host = 'zabbix.webmasterskaya.xyz'; // Replace with your server
$port = 10051; // Replace with your port

// Create a stream context for TLS
$context = stream_context_create([
    'ssl' => [
        'crypto_method' => STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT,
        'peer_name'     => $host,
        'psk'           => $psk_key,
        'psk_identity'  => $psk_identity,
        /**
         * OpenSSL 1.1.0 or newer. TLS 1.2
         * ZBX_CIPHERS_PSK_ECDHE    "kECDHEPSK+AES128:"
         * ZBX_CIPHERS_PSK        "kPSK+AES128"
         *
         *
         */
        // tls 1.2 https://github.com/zabbix/zabbix/blob/442095864cc5f218fcf7bfb129c5d59d63d3e4ce/src/libs/zbxcomms/tls_openssl.c#L887 'ciphers' => 'TLS_AES_256_GCM_SHA384:TLS_CHACHA20_POLY1305_SHA256:TLS_AES_128_GCM_SHA256:ECDHE-PSK-AES128-CBC-SHA256:ECDHE-PSK-AES128-CBC-SHA',
        // tls 1.2 https://github.com/zabbix/zabbix/blob/442095864cc5f218fcf7bfb129c5d59d63d3e4ce/src/libs/zbxcomms/tls_openssl.c#L888C28-L888C39 'ciphers' => 'TLS_AES_256_GCM_SHA384:TLS_CHACHA20_POLY1305_SHA256:TLS_AES_128_GCM_SHA256:PSK-AES128-GCM-SHA256:PSK-AES128-CCM8:PSK-AES128-CCM:PSK-AES128-CBC-SHA256:PSK-AES128-CBC-SHA'
        'ciphers'       => 'TLS_AES_256_GCM_SHA384:TLS_CHACHA20_POLY1305_SHA256:TLS_AES_128_GCM_SHA256:ECDHE-PSK-AES128-CBC-SHA256:ECDHE-PSK-AES128-CBC-SHA'
//        'ciphers'       => 'ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:DHE-RSA-AES128-GCM-SHA256:DHE-RSA-AES256-SHA256:ECDHE-ECDSA-AES128-SHA256:ECDHE-RSA-AES128-SHA256:DHE-RSA-AES128-SHA256:RSA-PSK-AES128-GCM-SHA256:DHE-PSK-AES128-GCM-SHA256:AES128-GCM-SHA256:PSK-AES128-GCM-SHA256:AES256-SHA256:AES128-SHA256'
    ],
]);

function zbx_create_header($plain_data_size, $compressed_data_size = null)
{
    $protocol = "ZBXD";
    $flags    = 0x01;
    if (is_null($compressed_data_size)) {
        $datalen  = $plain_data_size;
        $reserved = 0;
    } else {
        $flags    |= 0x02;
        $datalen  = $compressed_data_size;
        $reserved = $plain_data_size;
    }

    return $protocol . chr($flags) . pack("VV", $datalen, $reserved);
}



$data = json_encode([
    'request' => 'sender data',
    'data' => [
        'host' => 'testhost',
        'key' => 'testtrap',
        'value' => 'Hello, Server!',
    ],
]);
$data = zbx_create_header(strlen($data)) . $data;

var_dump($data);

$errstr = '';
$errno = '';
// Wrap the socket with OpenSSL
$ssl_socket = stream_socket_client("ssl://$host:$port", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);

if (!$ssl_socket) {
    die("Failed to connect: $errstr ($errno)");
}


// Now you can read/write to the secure socket
fwrite($ssl_socket, $data);
$response = fread($ssl_socket, 128);
echo "Server response: $response";

// Close the socket
fclose($ssl_socket);

