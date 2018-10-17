# 远程调用的统一客户端

## 使用方法

### 加载引入

> 如果您的Larvel版本>5.5可忽略此设置

在`config/app.php`中增加
```
'providers' => [
    CrCms\Foundation\ConnectionPool\PoolServiceProvider::class,
    CrCms\Foundation\Client\ClientServiceProvider::class,
]
```

### 增加配置

在`config/client.php`的`connections`中增加如下测试配置
```
'http' => [
    'driver' => 'http',
    'host' => 'blog.crcms.cn',
    'port' => 80,
    //资源连接器的配置，请参考guzzlehttp
    'settings' => [
        'timeout' => 1,
    ],
],
```

### 调用方法
```
//实例化并设置连接
$client = $this->app->make('client.manager')->connection('http');
//发送请求
$client = $client->request('/',[]);
//获取当前连接
dump(get_class($client->getConnection()));
//获取连接池管理器
dump(get_class($client->getConnectionPoolManager()));
//获取当前的连接资源的响应
dump(get_class($client->getResponse()));
//获取资源响应内容
dd($client->getContent());
```

### 动态化配置
```
$client = $this->app->make('client.manager')->connection([
    'name' => 'http',
    'driver' => 'http',
    'host' => '192.168.1.12',
    'port' => 8500,
    'settings' => [
        'timeout' => 1,
    ],
]);
```

### 使用连接池

在`config/pool.php`的`connections`中增加配置(可选增加)
```
'client' => [
    'max_idle_number' => 50,//最大空闲数
    'min_idle_number' => 15,//最小空闲数
    'max_connection_number' => 20,//最大连接数
    'max_connection_time' => 3,//最大连接时间(s)
],
```

> **使用连接池时，以当前连接名称做为连接池名称**

## 支持的类型
- Http

## 后期增加
- Tcp
- WebSocket
