<?php

/**
 * @author simon <crcms@crcms.cn>
 * @datetime 2018/6/28 20:42
 * @link http://crcms.cn/
 * @copyright Copyright &copy; 2018 Rights Reserved CRCMS
 */

namespace CrCms\Foundation\Client;

use Illuminate\Support\ServiceProvider;

/**
 * Class ClientServiceProvider
 * @package CrCms\Foundation\Client
 */
class ClientServiceProvider extends ServiceProvider
{
    /**
     * @var bool
     */
    protected $defer = true;


    /**
     * @var string
     */
    protected $namespaceName = 'client';

    /**
     * @var string
     */
    protected $packagePath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;

    /**
     * @return void
     */
    public function boot()
    {
        //move config path
        $this->publishes([
            $this->packagePath . 'config' => config_path(),
        ]);
    }

    /**
     * @return void
     */
    public function register(): void
    {
        //merge config
        $configFile = $this->packagePath . "config/config.php";
        if (file_exists($configFile)) $this->mergeConfigFrom($configFile, $this->namespaceName);

        $this->registerAlias();

        $this->registerServices();
    }

    /**
     * @return void
     */
    protected function registerServices(): void
    {
        $this->app->singleton('client.factory', function ($app) {
            return new Factory($app);
        });

        $this->app->singleton('client.manager', function ($app) {
            return new Manager($app, $app->make('client.factory'), $app->make('pool.manager'));
        });
    }

    /**
     * @return void
     */
    protected function registerAlias(): void
    {
        $this->app->alias('client.factory', Factory::class);
        $this->app->alias('client.manager', Manager::class);
    }

    /**
     * @return array
     */
    public function provides(): array
    {
        return [
            'client.manager',
            'client.factory',
        ];
    }
}