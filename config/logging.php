<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Processor\PsrLogMessageProcessor;

return [

    /*
    |--------------------------------------------------------------------------
    | 默认日志通道
    |--------------------------------------------------------------------------
    |
    | 此选项定义用于写入
    | 消息添加到您的日志中。此处提供的值应与以下值之一匹配
    | 下面配置的 “channels” 列表中的 channels。
    |
    */

    'default' => 'stack',

    /*
    |--------------------------------------------------------------------------
    | 弃用日志频道
    |--------------------------------------------------------------------------
    |
    | 此选项控制应用于记录警告的日志通道
    | 关于已弃用的 PHP 和库功能。这使您可以获得
    | 您的应用程序已准备好迎接即将推出的依赖项的主要版本。
    |
    */

    'deprecations' => [
        'channel' => 'null',
        'trace' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Log 通道
    |--------------------------------------------------------------------------
    |
    | 在这里，您可以为您的应用程序配置日志通道。拉拉维尔
    | 利用 Monolog PHP 日志记录库，其中包括各种
    | 强大的日志处理程序和格式化程序，您可以免费使用。
    |
    | 可用驱动程序： “single”， “daily”， “slack”， “syslog”，
    |                    “errorlog”， “monolog”， “custom”， “stack”
    |
    */

    'channels' => [

        'stack' => [
            'driver' => 'stack',
            'channels' => ['single'],
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => 'debug',
            'replace_placeholders' => true,
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => 'debug',
            'days' => env('LOG_DAILY_DAYS', 14),
            'replace_placeholders' => true,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => env('LOG_SLACK_USERNAME', 'Laravel Log'),
            'emoji' => env('LOG_SLACK_EMOJI', ':boom:'),
            'level' => env('LOG_LEVEL', 'critical'),
            'replace_placeholders' => true,
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => 'debug',
            'handler' => env('LOG_PAPERTRAIL_HANDLER', SyslogUdpHandler::class),
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
                'connectionString' => 'tls://'.env('PAPERTRAIL_URL').':'.env('PAPERTRAIL_PORT'),
            ],
            'processors' => [PsrLogMessageProcessor::class],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'level' => 'debug',
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
            'processors' => [PsrLogMessageProcessor::class],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => 'debug',
            'facility' => env('LOG_SYSLOG_FACILITY', LOG_USER),
            'replace_placeholders' => true,
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => 'debug',
            'replace_placeholders' => true,
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],

    ],

];
