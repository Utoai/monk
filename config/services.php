<?php

return [

    /*
    |--------------------------------------------------------------------------
    | 第三方服务
    |--------------------------------------------------------------------------
    |
    | 此文件用于存储第三方服务的凭证，例如
    | 作为 Mailgun、Postmark、AWS 等。本文件提供了事实上的
    | location，允许软件包具有
    | 用于查找各种服务凭证的 Conventional 文件。
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
