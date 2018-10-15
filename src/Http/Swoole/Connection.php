<?php

namespace CrCms\Foundation\Client\Http\Swoole;

use CrCms\Foundation\Client\Http\Contracts\ResponseContract;
use CrCms\Foundation\ConnectionPool\AbstractConnection;
use CrCms\Foundation\ConnectionPool\Exceptions\ConnectionException;
use CrCms\Foundation\ConnectionPool\Contracts\Connection as ConnectionContract;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use CrCms\Foundation\ConnectionPool\Exceptions\RequestException as ConnectionPoolRequestException;

/**
 * Class Connection
 * @package CrCms\Foundation\Client\Http\Swoole
 */
class Connection extends AbstractConnection implements ConnectionContract, ResponseContract
{
    /**
     * @var string
     */
    protected $method = 'post';

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * @param string $method
     * @return $this
     */
    public function setMethod(string $method)
    {
        $this->method = strtolower($method);

        return $this;
    }

    /**
     * @param array $headers
     * @return $this
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * @param string $path
     * @param array $data
     */
    protected function resolve(array $data): array
    {
        if (!empty($data['method'])) {
            $this->setMethod($data['method']);
            unset($data['method']);
        }

        if (!empty($data['headers'])) {
            $this->setHeaders($data['headers']);
            unset($data['headers']);
        }

        return $data['payload'] ?? [];
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->response ? $this->response->getStatusCode() : -1;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->response ? $this->response->getBody()->getContents() : null;
    }

    /**
     * @param string $uri
     * @param array $data
     * @return ConnectionContract
     */
    public function send(string $uri, array $data = []): AbstractConnection
    {
        if ($this->method === 'post') {
            $execResult = call_user_func_array([$this->connector, $this->method], [$uri, json_encode($data)]);
        } else if ($this->method === 'get') {
            $execResult = call_user_func_array([$this->connector, $this->method], [$uri]);
        } else {
            /* 这里需要详细测试，暂时此功能不可用 */
            $this->connector->setMethod($this->method);
            $this->connector->setData(json_encode($data));
            $execResult = call_user_func_array([$this->connector, 'execute'], [$uri]);
        }

        //加入异常连接
        if ($this->isAbnormalConnection(!$execResult)) {
            throw new ConnectionException($this);
        }

        return $this;
    }

    /**
     * @param bool $isDead
     * @return bool
     */
    protected function isAbnormalConnection(bool $isDead = false): bool
    {
        if (in_array($this->connector->statusCode, [-1, -2], true) || $this->connector->errCode !== 0 || $isDead === true) {
            return true;
        }

        return false;
    }
}