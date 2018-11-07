<?php

/**
 * @author simon <crcms@crcms.cn>
 * @datetime 2018/6/26 6:13
 * @link http://crcms.cn/
 * @copyright Copyright &copy; 2018 Rights Reserved CRCMS
 */

namespace CrCms\Foundation\Client;

use CrCms\Foundation\ConnectionPool\AbstractConnectionFactory;
use CrCms\Foundation\ConnectionPool\Contracts\ConnectionPool as ConnectionPoolContract;
use CrCms\Foundation\ConnectionPool\Contracts\ConnectionFactory as ConnectionFactoryContract;
use CrCms\Foundation\ConnectionPool\Contracts\Connection as ConnectionContract;
use CrCms\Foundation\ConnectionPool\Contracts\Connector as ConnectorContract;
use CrCms\Foundation\Client\Http\Guzzle\Connection as GuzzleConnection;
use CrCms\Foundation\Client\Http\Guzzle\Connector as GuzzleConnector;
use CrCms\Foundation\Client\Http\Swoole\Connection as SwooleConnection;
use CrCms\Foundation\Client\Http\Swoole\Connector as SwooleConnector;
use CrCms\Foundation\Client\Yar\Connection as YarConnector;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

/**
 * Class Factory
 * @package CrCms\Foundation\Rpc\Client
 */
class Factory implements ConnectionFactoryContract
{
    /**
     * @var Container
     */
    protected $app;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var string
     */
    protected $name;

    /**
     * Factory constructor.
     * @param Container $app
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    /**
     * @param $name
     * @param array $config
     * @return Factory
     */
    public function config($name, array $config = []): self
    {
        $this->name = $name;
        $this->config = $config;
        return $this;
    }

    /**
     * @return ConnectionContract
     */
    public function make(): ConnectionContract
    {
        return $this->createConnection();
    }

    /**
     * @param array $config
     * @return ConnectionContract
     */
    protected function createConnection(): ConnectionContract
    {
        $config = $this->configure($this->name);

        switch ($config['driver']) {
            case 'http':
                return new GuzzleConnection($config);
            case 'swoole_http':
                return new SwooleConnection($config);
            case 'yar':
                return new YarConnector($config);
        }

        throw new InvalidArgumentException("Unsupported driver [{$config['driver']}]");
    }

    /**
     * @param string $name
     * @return array
     */
    protected function configure(string $name): array
    {
        return $this->app->make('config')->get("client.connections.{$name}", $this->config);
    }
}