<?php

namespace CrCms\Foundation\Client\Yar;

use CrCms\Foundation\ConnectionPool\AbstractConnection;
use Yar_Client;

/**
 * Class Connection
 * @package CrCms\Foundation\Client\Yar
 */
class Connection extends AbstractConnection
{
    /**
     * @return Yar_Client
     */
    public function connect(): Yar_Client
    {
        $client = new Yar_Client("http://{$this->config['host']}");
        $timeout = $this->config['settings']['timeout'] ? intval($this->config['settings']['timeout']) : 0.5;
        $client->SetOpt(YAR_OPT_CONNECT_TIMEOUT, $timeout * 1000);
        return $client;
    }

    /**
     * @param mixed ...$params
     * @return mixed
     */
    public function handle(...$params)
    {
        $method = array_shift($params);
        return $this->connector->call($method, $params);
    }
}