<?php

namespace Utoai\Monk\Providers;

use Illuminate\Console\Events\CommandFinished;
use Illuminate\Support\ServiceProvider;
use Utoai\Monk\Filesystem\Filesystem;

class AcornServiceProvider extends ServiceProvider
{
    /**
     * 核心配置。
     *
     * @var string[]
     */
    protected $configs = ['app', 'services'];

    /**
     * Provider 配置。
     *
     * @var string[]
     */
    protected $providerConfigs = [
        \Illuminate\Auth\AuthServiceProvider::class => 'auth',
        \Illuminate\Broadcasting\BroadcastServiceProvider::class => 'broadcasting',
        \Illuminate\Cache\CacheServiceProvider::class => 'cache',
        \Illuminate\Database\DatabaseServiceProvider::class => 'database',
        \Illuminate\Filesystem\FilesystemServiceProvider::class => 'filesystems',
        \Illuminate\Hashing\HashServiceProvider::class => 'hashing',
        \Illuminate\Log\LogServiceProvider::class => 'logging',
        \Illuminate\Mail\MailServiceProvider::class => 'mail',
        \Illuminate\Queue\QueueServiceProvider::class => 'queue',
        \Illuminate\Session\SessionServiceProvider::class => 'session',
        \Illuminate\View\ViewServiceProvider::class => 'view',
        // \Utoai\Monk\Assets\AssetsServiceProvider::class => 'assets',
    ];

    /**
     * 注册服务。
     *
     * @return void
     */
    public function register()
    {
        // 无需绑定服务
    }

    /**
     * 引导服务。
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->registerPublishables();
            $this->registerPostInitEvent();
        }
    }

    /**
     * 发布应用程序文件。
     *
     * @return void
     */
    protected function registerPublishables()
    {
        $this->publishConfigs();
    }

    /**
     * 发布应用程序配置。
     *
     * @return void
     */
    protected function publishConfigs()
    {
        foreach ($this->filterPublishableConfigs() as $config) {
            $path = base_path();

            $file = file_exists($stub = "{$path}/config-stubs/{$config}.php")
                ? $stub
                : "{$path}/config/{$config}.php";

            $this->publishes([
                $file => config_path("{$config}.php"),
            ], ['monk', 'monk-configs']);
        }
    }

    /**
     * 筛选出注册的提供商配置。
     *
     * @return string[] 通过合并核心配置和有效提供者配置生成的配置数组
     */
    protected function filterPublishableConfigs()
    {
        $configs = array_filter(
            $this->providerConfigs,
            fn($provider) => class_exists($provider) && $this->app->getProviders($provider),
            ARRAY_FILTER_USE_KEY
        );

        return array_unique(array_merge($this->configs, array_values($configs)));
    }
    /**
     * 运行 acorn：init 后删除 zeroconf 存储目录。
     *
     * @return void
     */
    protected function registerPostInitEvent()
    {
        $this->app->make('events')->listen(function (CommandFinished $event) {
            if ($event->command !== 'acorn:init') {
                return;
            }

            if (! is_dir(base_path('storage'))) {
                return;
            }

            $files = new Filesystem;

            $files->deleteDirectory(__TYPECHO_ROOT_DIR__, '/usr/cache/monk');
        });
    }
}
