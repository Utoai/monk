<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | 默认缓存存储
    |--------------------------------------------------------------------------
    |
    | 该选项控制将使用的默认缓存存储
    | 框架。如果没有明确使用另一个连接，则使用此连接
    | 在应用程序内运行缓存操作时指定。
    |
    */

    'default' => 'file',

    /*
    |--------------------------------------------------------------------------
    | 缓存存储
    |--------------------------------------------------------------------------
    |
    | 在这里，您可以将应用程序的所有缓存“存储”定义为
    | 以及他们的司机。您甚至可以定义多个商店
    | 相同的缓存驱动程序可以对缓存中存储的项目类型进行分组。
    |
    | 支持的驱动程序："apc", "array", "database", "file",
    |         "memcached", "redis", "dynamodb", "octane", "null"
    |
    */

    'stores' => [

        'array' => [
            'driver' => 'array',
            'serialize' => false,
        ],

        'database' => [
            'driver' => 'database',
            'table' => env('DB_CACHE_TABLE', 'cache'),
            'connection' => env('DB_CACHE_CONNECTION'),
            'lock_connection' => env('DB_CACHE_LOCK_CONNECTION'),
        ],

        'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache/data'),
            'lock_path' => storage_path('framework/cache/data'),
        ],

        'memcached' => [
            'driver' => 'memcached',
            'persistent_id' => env('MEMCACHED_PERSISTENT_ID'),
            'sasl' => [
                env('MEMCACHED_USERNAME'),
                env('MEMCACHED_PASSWORD'),
            ],
            'options' => [
                // Memcached::OPT_CONNECT_TIMEOUT => 2000,
            ],
            'servers' => [
                [
                    'host' => env('MEMCACHED_HOST', '127.0.0.1'),
                    'port' => env('MEMCACHED_PORT', 11211),
                    'weight' => 100,
                ],
            ],
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => env('REDIS_CACHE_CONNECTION', 'cache'),
            'lock_connection' => env('REDIS_CACHE_LOCK_CONNECTION', 'default'),
        ],

        'dynamodb' => [
            'driver' => 'dynamodb',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'table' => env('DYNAMODB_CACHE_TABLE', 'cache'),
            'endpoint' => env('DYNAMODB_ENDPOINT'),
        ],

        'octane' => [
            'driver' => 'octane',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | 缓存键前缀
    |--------------------------------------------------------------------------
    |
    | 使用 APC、数据库、memcached、Redis 和 DynamoDB 缓存时
    | 存储，可能有其他应用程序使用相同的缓存。为了
    | 因此，您可以为每个缓存键添加前缀以避免冲突。
    |
    */

    'prefix' => env('CACHE_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_') . '_cache_'),

];
