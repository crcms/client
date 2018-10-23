<?php

/**
 * @author simon <crcms@crcms.cn>
 * @datetime 2018/7/2 6:14
 * @link http://crcms.cn/
 * @copyright Copyright &copy; 2018 Rights Reserved CRCMS
 */

namespace CrCms\Foundation\Client;

use CrCms\Foundation\ConnectionPool\Contracts\ConnectionPool;
use CrCms\Foundation\ConnectionPool\PoolManager;
use Illuminate\Container\Container;
use CrCms\Foundation\ConnectionPool\ConnectionManager;
use CrCms\Foundation\ConnectionPool\Contracts\ConnectionFactory;
use InvalidArgumentException;
use BadMethodCallException;
use Crcms\Foundation\ConnectionPool\Contracts\Connection;

/**
 * Class ClientManager
 * @package CrCms\Foundation\Client
 */
class ClientManager
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @var ConnectionManager
     */
    protected $poolManager;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var ConnectionFactory
     */
    protected $factory;

    /**
     * @var bool
     */
    protected $usePool;

    /**
     * @var ConnectionPool
     */
    protected $pool;

    /**
     * @var string
     */
    protected $name;

    /**
     * Manager constructor.
     * @param Container $app
     * @param ConnectionFactory $factory
     * @param PoolManager|null $poolManager
     */
    public function __construct(Container $app, ConnectionFactory $factory, ?PoolManager $poolManager = null)
    {
        $this->app = $app;
        $this->factory = $factory;
        $this->poolManager = $poolManager;
    }

    /**
     * @param null $name
     * @param bool $usePool
     * @return $this
     */
    public function connection($name = null, $usePool = true)
    {
        $resolve = $this->resolveConfig($name);
        /* @var string $name */
        /* @var array $configure */
        list($name, $configure) = array_values($resolve);

        $this->usePool = $usePool;
        $this->name = $name;

        $this->factory->config($name, $configure);

        if (is_null($this->connection)) {
            $this->connection = $this->usePool ?
                $this->poolManager->connection($this->factory, $name) :
                $this->factory->make();

            if ($this->usePool) {
                $this->pool = $this->poolManager->getPool($name);
            }
        }

        return $this;
    }

    /**
     * @return ConnectionPool
     */
    public function getPool(): ConnectionPool
    {
        return $this->pool;
    }

    /**
     * @return void
     */
    public function disconnection(): void
    {
        if ($this->poolManager && $this->usePool) {
            $this->poolManager->disconnection($this->connection, $this->name);
        }

        $this->name = null;
        $this->connection = null;
    }


    /**
     * @return Connection
     */
    public function getConnection(): Connection
    {
        return $this->connection;
    }

    /**
     * @param string $name
     * @return array
     */
    protected function configuration($name): array
    {
        $connections = $this->app->make('config')->get('client.connections');

        if (!isset($connections[$name])) {
            throw new InvalidArgumentException("Client config[{$name}] not found");
        }

        return $connections[$name];
    }

    /**
     * @return string
     */
    protected function defaultDriver(): string
    {
        return $this->app->make('config')->get('client.default');
    }

    /**
     * @param null $name
     * @return array
     */
    protected function resolveConfig($name = null): array
    {
        if (is_array($name)) {
            list($name, $configure) = [$name['name'] ?? $this->defaultDriver(), $name];
        } else {
            $name = $name ? $name : $this->defaultDriver();
            $configure = $this->configuration($name);
        }

        return compact('name', 'configure');
    }

    /**
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $method, array $arguments)
    {
        if (method_exists($this->connection, $method)) {
            return $this->connection->{$method}(...$arguments);
        }

        throw new BadMethodCallException("The method[{$method}] is not exists");
    }
}