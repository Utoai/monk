<?php

use Illuminate\Support\Facades\Facade;
use Utoai\Monk\ServiceProvider;

return [
    /*
    |--------------------------------------------------------------------------
    | 应用名称
    |--------------------------------------------------------------------------
    |
    | 该值是您的应用程序的名称。当该值被使用时
    | 框架需要将应用程序的名称放在通知中或
    | 应用程序或其包所需的任何其他位置。
    |
    */
    'name' => env('APP_NAME', 'Monk'),

    /*
    |--------------------------------------------------------------------------
    | 应用环境
    |--------------------------------------------------------------------------
    |
    | 该值决定了您的应用程序当前所处的“环境”
    | 运行中。这可能决定您更喜欢如何配置各种
    | 应用程序使用的服务。在“.env”文件中设置它。
    |   development |   testing      |   production
    */
    'env' => 'development',

    /*
    |--------------------------------------------------------------------------
    | 应用程序调试模式
    |--------------------------------------------------------------------------
    |
    | 当您的应用程序处于调试模式时，详细的错误消息
    | 堆栈跟踪将显示在您的程序中发生的每个错误上
    | 应用。如果禁用，则会显示一个简单的通用错误页面。
    |   false true
    */
    'debug' => false,

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | 控制台使用此 URL 在使用时正确生成 URL
    | Artisan 命令行工具。您应该将其设置为根
    | 您的应用程序，以便在运行 Artisan 任务时使用它。
    |
    */

    
    'url' => env('APP_URL', 'http://localhost'),    //  应该用不到,待排查
    
    'frontend_url' => env('FRONTEND_URL', 'http://localhost:3000'),    //  应该用不到,待排查
    
    'asset_url' => env('ASSET_URL', 'https://q.w.com/usr/themes/classic-22/public'),

    /*
    |--------------------------------------------------------------------------
    | 应用程序时区
    |--------------------------------------------------------------------------
    |
    | 您可以在此处指定应用程序的默认时区，
    | 将由 PHP 日期和日期时间函数使用。我们已经走了
    | 并将其设置为开箱即用的合理默认值。
    |
    */
    'timezone' => 'UTC',


    /*
    |--------------------------------------------------------------------------
    | 应用程序区域设置配置
    |--------------------------------------------------------------------------
    |
    | 应用程序区域设置确定将使用的默认区域设置
    | 由翻译服务提供商提供。您可以自由设置该值
    | 应用程序支持的任何区域设置。
    |
    */
    'locale' => 'zh_CN',

    /*
    |--------------------------------------------------------------------------
    | 应用程序回退区域设置
    |--------------------------------------------------------------------------
    |
    | 后备区域设置确定当前区域设置时要使用的区域设置
    | 不可用。您可以更改该值以对应于任何
    | 通过您的应用程序提供的语言文件夹。
    |
    */
    'fallback_locale' => 'cn',

    /*
    |--------------------------------------------------------------------------
    | Faker 区域设置
    |--------------------------------------------------------------------------
    |
    | Faker PHP 库在生成 fake 时将使用此语言环境
    | 数据库种子的数据。例如，这将用于获取
    | 本地化电话号码、街道地址信息等。
    |
    */
    'faker_locale' => 'zh_CN',

    /*
    |--------------------------------------------------------------------------
    | 加密密钥
    |--------------------------------------------------------------------------
    |
    | 该密钥由 Laravel 的加密服务使用，应设置
    | 为随机的 32 个字符的字符串，以确保所有加密值
    | 是安全的。您应该在部署应用程序之前执行此操作。
    |
    */

    'cipher' => 'AES-256-CBC',

    'key' => 'base64:SUZBzKGnP/DZ/Zl9JRxBLZs2sF0kD7ivczkHuyVTl1c=',


    'previous_keys' => [
        ...array_filter(
            explode(',', env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | 维护模式驱动程序
    |--------------------------------------------------------------------------
    |
    | 这些配置选项决定了用于确定和
    | 管理 Laravel 的“维护模式”状态。 “cache”驱动程序将
    | 允许跨多台机器控制维护模式。
    |
    | 支持的驱动程序：“file”、“cache”
    |
    */
    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

    /*
    |--------------------------------------------------------------------------
    | 自动加载服务提供商
    |--------------------------------------------------------------------------
    |
    | 此处列出的服务提供商将自动加载到任何
    | 向您的应用程序提出请求。您可以将自己的服务添加到
    | 下面的数组为本应用程序提供附加功能。
    |
    */

    'providers' => ServiceProvider::defaultProviders()->merge([

        // App\Providers\ThemeServiceProvider::class,
        // 套餐服务提供商...
    ])->merge([
        // 应用服务提供商...
        // App\Providers\ThemeServiceProvider::class,
    ])->merge([
        // 添加的服务提供商（不要删除此行）...
    ])->toArray(),


    /*
    |--------------------------------------------------------------------------
    | 类别名
    |--------------------------------------------------------------------------
    |
    | 当此应用程序
    | 已启动。您可以添加任何其他类别名，这些别名应该
    | 加载到数组中。为了提高速度，所有别名都是延迟加载的。
    |
    */
    'aliases' => Facade::defaultAliases()->merge([
        // 'Example' => App\Facades\Example::class,
    ])->toArray(),
];
