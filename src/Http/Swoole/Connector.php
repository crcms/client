<?php

namespace CrCms\Foundation\Client\Http\Swoole;

use CrCms\Foundation\ConnectionPool\AbstractConnector;
use CrCms\Foundation\ConnectionPool\Contracts\Connector as ConnectorContract;

/**
 * Class Connector
 * @package CrCms\Foundation\Client\Http\Swoole
 */
class Connector extends AbstractConnector implements ConnectorContract
{
    /**
     * @var array
     */
    protected $defaultHeaders = [
        'Content-Type' => 'application/json',
    ];

    /**
     * @param array $config
     * @return Connector
     */
    public function connect(array $config): Connector
    {
        $this->connect = new Client($config['host'], $config['port']);
        $this->connect->set($this->mergeSettings($config['settings'] ?? []));
        $this->connect->setHeaders($this->mergeHeaders([]));
        return $this;
    }

    /**
     * @param $headers
     * @return array
     */
    protected function mergeHeaders($headers): array
    {
        return array_merge($this->defaultHeaders, $headers);
    }
}