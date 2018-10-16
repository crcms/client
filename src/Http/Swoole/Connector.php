<?php

namespace CrCms\Foundation\Client\Http\Swoole;

use CrCms\Foundation\ConnectionPool\AbstractConnector;
use CrCms\Foundation\ConnectionPool\Contracts\Connector as ConnectorContract;
use Swoole\Coroutine\Http\Client;

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
     * @return ConnectorContract
     */
    public function connect(array $config): ConnectorContract
    {
        $this->connect = new Client($config['host'], $config['port']);
        $this->connect->set($this->mergeSettings($config['settings'] ?? []));
        $this->connect->setHeaders($this->mergeHeaders([]));
        return $this;
    }

    public function close(): void
    {
        $this->connect->close();
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