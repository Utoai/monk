<?php

return [

    /*
    |--------------------------------------------------------------------------
    | 默认邮件程序
    |--------------------------------------------------------------------------
    |
    | 此选项控制用于发送所有电子邮件的默认邮件程序
    | 消息，除非在发送时明确指定了其他邮件程序
    | 消息。所有其他邮件程序都可以在
    | “mailers” 数组。提供了每种类型的邮件程序的示例。
    |
    */

    'default' => env('MAIL_MAILER', 'smtp'),

    /*
    |--------------------------------------------------------------------------
    | 邮件程序配置
    |--------------------------------------------------------------------------
    |
    | 在这里，您可以配置您的应用程序使用的所有邮件程序以及
    | 它们各自的设置。已为
    | 您可以根据自己的应用程序需要自由添加自己的应用程序。
    |
    | Laravel 支持各种可以使用的邮件“传输”驱动程序
    | 在发送电子邮件时。您可以指定要用于哪个
    | 你的邮件在下面。如果需要，您还可以添加其他邮件。
    |
    | 支持：“smtp”、“sendmail”、“mailgun”、“ses”、“ses-v2”、
    |            “邮戳”、“日志”、“数组”、“故障转移”、“循环”
    |
    */

    'mailers' => [

        'smtp' => [
            'transport' => 'smtp',
            'url' => env('MAIL_URL'),
            'host' => env('MAIL_HOST', '127.0.0.1'),
            'port' => env('MAIL_PORT', 2525),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN'),
        ],

        'ses' => [
            'transport' => 'ses',
        ],

        'postmark' => [
            'transport' => 'postmark',
            // 'message_stream_id' => null,
            // 'client' => [
            //     'timeout' => 5,
            // ],
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers' => [
                'smtp',
                'log',
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    |
    | You may wish for all emails sent by your application to be sent from
    | the same address. Here you may specify a name and address that is
    | used globally for all emails that are sent by your application.
    |
    */

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => env('MAIL_FROM_NAME', 'Example'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Markdown 邮件设置
    |--------------------------------------------------------------------------
    |
    | 如果您使用基于 Markdown 的电子邮件渲染，您可以配置您的
    | 此处的主题和组件路径，允许您自定义设计
    | 的电子邮件。或者，您可以简单地坚持使用 Laravel 默认值！
    |
    */

    'markdown' => [
        'theme' => env('MAIL_MARKDOWN_THEME', 'default'),

        'paths' => [
            resource_path('views/vendor/mail'),
        ],
    ],

];
