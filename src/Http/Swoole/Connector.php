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
     * @var Client
     */
    protected $connect;

    /**
     * @param array $config
     * @return ConnectorContract
     */
    public function connect(array $config): ConnectorContract
    {
        $this->connect = new Client($config['host'], $config['port']);
        $this->connect->set($this->mergeSettings($config['settings'] ?? []));
        return $this;
    }

    /**
     * @return void
     */
    public function close(): void
    {
        $this->connect->close();
    }
}