<?php

/**
 * @author simon <crcms@crcms.cn>
 * @datetime 2018/6/28 20:42
 * @link http://crcms.cn/
 * @copyright Copyright &copy; 2018 Rights Reserved CRCMS
 */

namespace CrCms\Foundation\Client;

use CrCms\Foundation\ConnectionPool\PoolServiceProvider;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application;

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
        if ($this->isLumen()) {
        } else {
            $this->publishes([
                $this->packagePath . 'config' => config_path(),
            ]);
        }
    }

    /**
     * @return void
     */
    public function register(): void
    {
        if ($this->isLumen()) {
            $this->app->configure($this->namespaceName);
        }

        //merge config
        $configFile = $this->packagePath . "config/config.php";
        if (file_exists($configFile)) $this->mergeConfigFrom($configFile, $this->namespaceName);

        $this->registerAlias();

        $this->registerServices();

        $this->app->register(PoolServiceProvider::class);
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
     * @return bool
     */
    protected function isLumen(): bool
    {
        return class_exists(Application::class) && $this->app instanceof Application;
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