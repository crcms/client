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
    protected $method = 'POST';

    /**
     * @var array
     */
    protected $headers = [
        'Content-Type' => 'application/json',
    ];

    /**
     * @param string $method
     * @return $this
     */
    public function setMethod(string $method)
    {
        $this->method = strtoupper($method);

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
        return $this->connector->statusCode;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->connector->body;
    }

    /**
     * @param string $uri
     * @param array $data
     * @return ConnectionContract
     */
    public function send(string $uri, array $data = []): AbstractConnection
    {
        $this->connector->setMethod($this->method);
        if ($data) {
            if (isset($this->headers['Content-Type']) && stripos($this->headers['Content-Type'], 'json')) {
                $data = json_encode($data);
            } else {
                $data = http_build_query($data);
            }
            $this->headers['Content-Length'] = strlen($data);
            $this->connector->setData($data);
        }
        $this->connector->setHeaders($this->headers);
        $this->connector->execute('/' . ltrim($uri, '/'));

        //加入异常连接
        if ($this->isAbnormalConnection()) {
            throw new ConnectionException($this);
        }

        if ($this->connector->statusCode >= 400 && $this->connector->statusCode < 500) {
            throw new ConnectionPoolRequestException($this);
        }

        return $this;
    }

    /**
     * @param bool $isDead
     * @return bool
     */
    protected function isAbnormalConnection(): bool
    {
        return in_array($this->connector->statusCode, [-1, -2, -3], true) || $this->connector->errCode !== 0;
    }
}