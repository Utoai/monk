<?php

return [

    /*
    |--------------------------------------------------------------------------
    | 默认文件系统磁盘
    |--------------------------------------------------------------------------
    |
    | 您可以在此处指定应使用的默认文件系统磁盘
    | 通过框架。 “本地”磁盘，以及各种云
    | 基于磁盘的应用程序可用于文件存储。
    |
    */

    'default' => 'local',

    /*
    |--------------------------------------------------------------------------
    | 文件
    |--------------------------------------------------------------------------
    |
    | 在下面，您可以根据需要配置尽可能多的文件系统磁盘，并且您
    | 甚至可以为同一驱动程序配置多个磁盘。的实例
    | 大多数支持的存储驱动程序都在此处配置以供参考。
    |
    | 支持的驱动程序：“本地”、“ftp”、“sftp”、“s3”
    | 支持的驱动程序: "local", "ftp", "sftp", "s3"
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | 符号链接
    |--------------------------------------------------------------------------
    |
    | 在这里，您可以配置将在
    | 执行 'storage：link' Acorn 命令。数组键应为
    | 链接的位置和值应该是它们的目标。
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
        public_path('build') => base_path('usr/themes/FounBox/public/build'),
    ],

];
