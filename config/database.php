<?php

use Illuminate\Support\Str;
use Typecho\Db;

// 获取 Typecho 数据库配置信息
$typechoDb = Db::get();
$dbConfigRead = $typechoDb->getConfig(Db::READ);
$dbname = $typechoDb->getAdapterName();  // 获取适配器名称
$adapterName = strtolower(str_replace('Pdo_', '', $dbname));  // 将 "Pdo_Mysql" 转换为 "mysql"
$dbPrefix = $typechoDb->getPrefix();  // 获取表前缀


return [

    /*
    |--------------------------------------------------------------------------
    | 默认数据库连接名称
    |--------------------------------------------------------------------------
    |
    | 您可以在此处指定您想要的以下数据库连接
    | 用作数据库操作的默认连接。这是
    | 除非有另一个连接，否则将使用的连接
    | 当您执行查询/语句时显式指定。
    |
    */

    'default' => $dbname,

    /*
    |--------------------------------------------------------------------------
    | 数据库连接
    |--------------------------------------------------------------------------
    |
    | 以下是为您的应用程序定义的所有数据库连接。
    | 为每个数据库系统提供了一个示例配置
    | 由 Laravel 支持。您可以自由添加/删除连接。
    |
    */

    'connections' => [
        // MySQL 原生适配器配置
        'Mysqli' => [
            'driver' => $adapterName,  // 这里自动转换 driver
            'host' => $dbConfigRead->host,
            'port' => $dbConfigRead->port,
            'database' => $dbConfigRead->database,
            'username' => $dbConfigRead->user,
            'password' => $dbConfigRead->password,
            'charset' => $dbConfigRead->charset,
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => $dbPrefix,
            'strict' => true,
            'engine' => $dbConfigRead->engine,
        ],

        // PDO MySQL 配置
        'Pdo_Mysql' => [
            'driver' => 'mysql',  // PDO MySQL 使用 'mysql'
            'host' => $dbConfigRead->host,
            'port' => $dbConfigRead->port,
            'database' => $dbConfigRead->database,
            'username' => $dbConfigRead->user,
            'password' => $dbConfigRead->password,
            'charset' => $dbConfigRead->charset,
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => $dbPrefix,
            'strict' => true,
            'engine' => $dbConfigRead->engine,
        ],

        // PDO SQLite 配置
        'Pdo_SQLite' => [
            'driver' => $adapterName,  // 这里自动转换 driver
            'database' => $dbConfigRead->file,  // SQLite 使用文件路径
            'prefix' => $dbPrefix,
            'foreign_key_constraints' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 迁移存储库表
    |--------------------------------------------------------------------------
    |
    | 该表跟踪所有已经运行的迁移
    | 您的申请。使用此信息，我们可以确定哪些
    | 磁盘上的迁移实际上并未在数据库上运行。
    |
    */

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Redis 数据库
    |--------------------------------------------------------------------------
    |
    | Redis 是一个开源、快速且先进的键值存储，还
    | 提供比典型键值系统更丰富的命令
    | 例如 Memcached。您可以在此处定义连接设置。
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_') . '_database_'),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],

    ],

];
