<?php

namespace CrCms\Tests\HttpClient;

use CrCms\Foundation\Client\ClientManager;
use CrCms\Foundation\Client\Factory;
use CrCms\Tests\TestCase;

/**
 * Class HttpClientTest
 * @package CrCms\Tests\HttpClient
 */
class HttpClientTest extends TestCase
{
    protected function config()
    {
        config([
            'client.connections.http' => [
                'driver' => 'http',
                'host' => 'blog.crcms.cn',
                'port' => 80,
                'settings' => [
                    'timeout' => 10,
                ],
            ]
        ]);
    }

    public function testGetRemoteExceptPool()
    {
        $this->config();

        /* @var ClientManager $manager */
        $manager = $this->app->make('client.manager');

        $manager->connection('http',false);

        $connection = $manager->setMethod('get')->handle('/');

        $this->assertEquals(200,$connection->getStatusCode());

        $manager->disconnection();

        try {
            $manager->getConnection();
        } catch (\Throwable $e) {
            $this->assertInstanceOf(\TypeError::class,$e);
        }
    }

    public function testRemoteUsePool()
    {
        $this->config();

//        config([
//            'pool.connections.http' => [
//                'max_idle_number' => 1000,
//                'min_idle_number' => 100,
//                'max_connection_number' => 800,
//            ],
//        ]);

        /* @var ClientManager $manager */
        $manager = $this->app->make('client.manager');

        $manager->connection([
            [
                'name' => 'http-pool',
                'driver' => 'http',
                'host' => 'blog.crcms.cn',
                'port' => 80,
                'settings' => [
                    'timeout' => 10,
                ],
            ]
        ],true);

        $connection1 = $manager->setMethod('get')->handle('/');
        $connection2 = $manager->setMethod('get')->handle('/');

        $this->assertEquals(200,$connection1->getStatusCode());
        $this->assertEquals(200,$connection2->getStatusCode());

        $this->assertEquals(99,$manager->getPool()->getIdleQueuesCount());
        $this->assertEquals(1,$manager->getPool()->getTasksCount());
        $manager->disconnection();
        $this->assertEquals(100,$manager->getPool()->getIdleQueuesCount());
        $this->assertEquals(0,$manager->getPool()->getTasksCount());
    }


    public function testCustomExceptPool()
    {
        /* @var ClientManager $manager */
        $manager = $this->app->make('client.manager');

        $manager->connection([
            'name' => 'http2',
            'driver' => 'http',
            'host' => 'baidu.com',
            'port' => 80,
            'settings' => [
                'timeout' => 10,
            ],
        ],false);

        $connection1 = $manager->setMethod('get')->handle('/');
        $this->assertEquals(200,$connection1->getStatusCode());

        $manager->disconnection();

        try {
            $manager->getConnection();
        } catch (\Throwable $e) {
            $this->assertInstanceOf(\TypeError::class,$e);
        }
    }

}