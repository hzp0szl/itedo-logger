# itedo-logger

### 框架版本
<a href="https://github.com/hzp0szl/itedo-logger">laravel版本</a>
` | `
<a href="https://github.com/hzp0szl/think-itedo-logger">thinkphp版本</a>

### 简介
	辅助laravel调试包，记录请求数据、数据库执行SQL语句、相应相关数据。支持file日志、mongodb、mysql（后续更新）。
	不足之处希望感兴趣的您指导加以修正。感谢！！！

### 版本 (其他版本后续新增)
```
PHP:        ^7.0
Laravel:   ^6.0
```

###镜像包
```
composer require itedo/itedo-logger -vvv
```

### config/app.php
--providers--
新增：
```
IteLog\Providers\IteLoggerProvider::class,
```

### config/新增配置itelog.php
```
<?php
return [
    //是否开启true false
    'logger' => 'true',
    //驱动 mongodb | file | mysql（后续完善）
    'driver' => 'mongodb',
    //driver是mongodb 时 需要填写表名
    'mongo_table' => 'ite_logger'
];
```

### App\Http\Kernel.php
$routeMiddleware 新增一行请求相应日志
```
'req.res.log' => \IteLog\Http\Middleware\ReqResLogger::class,
```
路由加 req.res.log中间件
```
Route::group([
    'middleware' => ['req.res.log']
]);
```

### Mongodb驱动时配置
需配置php扩展  php_mongodb

添加 MongoDB 的数据库的信息:
```
'mongodb' => [
    'driver' => 'mongodb',
    'host' => env('MONGODB_HOST', 'localhost'),
    'port' => 27017,
    'database' => env('MONGODB_DATABASE', 'itelog'),
    'username' => env('MONGODB_USERNAME', 'itelog'),
    'password' => env('MONGODB_PASSWORD', '123'),
],
```

### .env 配置新增
```
##mongodb
MONGODB_HOST=localhost
MONGODB_DATABASE=itelog
MONGODB_USERNAME=itelog
MONGODB_PASSWORD=123
```

### file驱动时配置
config/logging.php

channels 新增配置日志驱动
```
'iteLog' => [
    'driver' => 'daily',
    'path' => storage_path('logs/iteLog/laravel.log'),
    'level' => 'debug',
    'days' => 10,
],
```

### 异常记录
config/itelog.php
新增配置
```
//异常开启true false
'exception' => 'true',

```
app/Exceptions/Handler.php

render()方法内新增
```
if (config('itelog.exception')) {
    $throw = [
        'code' => $exception->getCode(),
        'message' => $exception->getMessage(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString(),
    ];
    IteLogFacades::setExceptions($throw);
    return response()->json($throw);
}
```

### Mongodb效果图
![e8efc121fd7ebfef260b2c913471f04](https://user-images.githubusercontent.com/25895643/161928179-95ca0708-3ca5-4b8c-89ca-f276f550c78b.png)
