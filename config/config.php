<?php

/**
 * @author simon <crcms@crcms.cn>
 * @datetime 2018/6/26 6:19
 * @link http://crcms.cn/
 * @copyright Copyright &copy; 2018 Rights Reserved CRCMS
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Default Client name
    |--------------------------------------------------------------------------
    |
    */
    
    'default' => 'http',

    /*
    |--------------------------------------------------------------------------
    | Client Connections
    |--------------------------------------------------------------------------
    |
    | Set configuration options for multiple connections
    |
    */

    'connections' => [
        'http' => [
            'driver' => 'http',
            'host' => 'crcms.cn',
            'port' => 80,
            'settings' => [
                'timeout' => 1,
            ],
        ],
        'swoole_http' => [
            'driver' => 'swoole_http',
            'host' => '192.168.1.106',
            'port' => 2222,
            'settings' => [
                'timeout' => 1,
            ],
        ],
    ],
];