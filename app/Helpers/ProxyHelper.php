<?php

namespace App\Helpers;

class ProxyHelper
{
    /**
     * Преобразует строковое представление типа прокси в cURL-константу.
     */
    public static function getProxyType(mixed $type): int
    {
        $map = [
            'http'                      => CURLPROXY_HTTP,
            'http_1_0'                  => CURLPROXY_HTTP_1_0,
            'socks4'                    => CURLPROXY_SOCKS4,
            'socks4a'                   => CURLPROXY_SOCKS4A,
            'socks5'                    => CURLPROXY_SOCKS5,
            'socks5_hostname'           => CURLPROXY_SOCKS5_HOSTNAME,
        ];

        $key = strtolower((string) $type);

        return $map[$key] ?? CURLPROXY_SOCKS5_HOSTNAME;
    }
}
