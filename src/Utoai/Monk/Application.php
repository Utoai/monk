<?php

namespace Utoai\Monk;

use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Application as FoundationApplication;
use Illuminate\Foundation\PackageManifest as FoundationPackageManifest;
use Illuminate\Foundation\ProviderRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Utoai\Monk\Exceptions\SkipProviderException;
use Utoai\Monk\Filesystem\Filesystem;
use RuntimeException;
use Throwable;


class Application extends FoundationApplication
{
    
    public const VERSION = '2.3.0';
    // use Bootable;
    /**
     * 开发人员定义的自定义资源路径。
     *
     * @var string
     */
    protected $resourcePath;

    /**
     * 创建新的 Application 实例。
     *
     * @param  string|null  $basePath
     * @return void
     */
    public function __construct($basePath = null, $paths = null)
    {
        if ($basePath) {
            $this->basePath = rtrim($basePath, '\/');
        }

        if ($paths) {
            $this->usePaths((array) $paths);
        }

        $this->registerGlobalHelpers();

        parent::__construct($basePath);
    }

    /**
     * 加载全局帮助程序函数。
     *
     * @return void
     */
    protected function registerGlobalHelpers()
    {
        require_once dirname(__DIR__, 2) . '/Illuminate/Foundation/helpers.php';
    }

    /**
     * 设置开发人员可配置的路径。
     *
     * @param  array  $path
     * @return $this
     */
    public function usePaths(array $paths)
    {
        $supportedPaths = [
            'app' => 'appPath',
            'lang' => 'langPath',
            'config' => 'configPath',
            'public' => 'publicPath',
            'storage' => 'storagePath',
            'database' => 'databasePath',
            'resources' => 'resourcePath',
            'bootstrap' => 'bootstrapPath',
        ];

        foreach ($paths as $pathType => $path) {
            $path = rtrim($path, '\\/');

            if (! isset($supportedPaths[$pathType])) {
                throw new Exception("这 {$pathType} 不支持路径类型。");
            }

            $this->{$supportedPaths[$pathType]} = $path;
        }

        $this->bindPathsInContainer();

        return $this;
    }

    /**
     * 绑定容器中所有应用程序路径。
     *
     * @返回无效
     */
    protected function bindPathsInContainer()
    {
        $this->instance('path', $this->path());
        $this->instance('path.base', $this->basePath());
        $this->instance('path.config', $this->configPath());
        $this->instance('path.database', $this->databasePath());
        $this->instance('path.public', $this->publicPath());
        $this->instance('path.resources', $this->resourcePath());
        $this->instance('path.storage', $this->storagePath());
        $this->instance('path.bootstrap', $this->bootstrapPath());

        $this->useLangPath(value(function () {
            return is_dir($directory = $this->resourcePath('lang'))
                ? $directory
                : $this->basePath('lang');
        }));
    }

    /**
     * 获取引导目录的路径。
     *
     * @param  string  $path  Optionally, a path to append to the bootstrap path
     * @return string
     */
    public function bootstrapPath($path = '')
    {
        // echo '-----引导目录'. $path .'路径<br /><br />';
        return $this->joinPaths($this->bootstrapPath ?: $this->storagePath('framework'), $path);
    }

    /**
     * 获取资源目录的路径。
     *
     * @param  string  $path
     * @return string
     */
    public function resourcePath($path = '')
    {
        // echo '-----资源目录'. $path .'路径<br /><br />';
        return $this->joinPaths($this->resourcePath ?: $this->basePath('resources'), $path);
    }

    /**
     * 设置资源目录。
     *
     * @param  string  $path
     * @return $this
     */
    public function useResourcePath($path)
    {
        $this->resourcePath = $path;

        $this->instance('path.resources', $path);

        return $this;
    }

    /**
     * 将基本绑定注册到容器中。
     *
     * @return void
     */
    protected function registerBaseBindings()
    {
        parent::registerBaseBindings();
        $this->registerPackageManifest();
    }


    /**
     * 注册软件包清单。
     *
     * @return void
     */
    protected function registerPackageManifest()
    {
        $this->alias(FoundationPackageManifest::class, PackageManifest::class);
    }

    /**
     * 确定应用程序当前是否因维护而关闭。
     *
     * @return bool
     */
    public function isDownForMaintenance()
    {
        return is_file($this->storagePath() . '/framework/down') || (defined('ABSPATH') && is_file(constant('ABSPATH') . '/.maintenance'));
    }

    /**
     * 在容器中注册核心类别名。
     *
     * @return void
     */
    public function registerCoreContainerAliases()
    {
        parent::registerCoreContainerAliases();

        $this->alias('app', self::class);
    }

    /**
     * 引导给定的服务提供商。
     *
     * @return void
     */
    protected function bootProvider(ServiceProvider $provider)
    {
        try {
            parent::bootProvider($provider);
        } catch (Throwable $e) {
            $this->skipProvider($provider, $e);
        }
    }

    /**
     * 注册所有已配置的提供程序。
     *
     * @return void
     */
    public function registerConfiguredProviders()
    {
        $providers = Collection::make($this->make('config')->get('app.providers'))
            ->filter(fn($provider) => class_exists($provider))
            ->partition(fn($provider) => str_starts_with($provider, 'Illuminate\\') || str_starts_with($provider, 'Roots\\'));

        $providers->splice(1, 0, [$this->make(PackageManifest::class)->providers()]);

        (new ProviderRepository($this, new Filesystem, $this->getCachedServicesPath()))
            ->load($providers->collapse()->toArray());
    }

    /**
     * 向应用程序注册服务提供商。
     *
     * @param  \Illuminate\Support\ServiceProvider|string  $provider
     * @param  bool  $force
     * @return \Illuminate\Support\ServiceProvider
     */
    public function register($provider, $force = false)
    {
        // echo "<pre>";
        // var_export($provider);
        // echo "</pre>";

        try {
            if (is_string($provider) && ! class_exists($provider)) {
                throw new SkipProviderException("跳过提供商 [{$provider}] 因为它不存在。");
            }

            return parent::register($provider, $force);
        } catch (Throwable $e) {
            return $this->skipProvider($provider, $e);
        }
    }

    /**
     * 跳过引导服务提供商和日志错误。
     *
     * @param  \Illuminate\Support\ServiceProvider|string  $provider
     */
    protected function skipProvider($provider, Throwable $e): ServiceProvider
    {
        $providerName = is_object($provider) ? get_class($provider) : $provider;

        if (! $e instanceof SkipProviderException) {
            $error = get_class($e);
            $message = [
                BindingResolutionException::class => "跳过提供商 [{$providerName}] 因为它需要一个无法找到的依赖项。",
            ][$error] ?? "跳过提供商 [{$providerName}]因为遇到错误[{$error}].";

            $e = new SkipProviderException($message, 0, $e);
        }

        if (method_exists($packages = $this->make(PackageManifest::class), 'getPackage')) {
            $e->setPackage($packages->getPackage($providerName));
        }

        report($e);

        if ($this->environment('development', 'testing', 'local') && ! $this->runningInConsole()) {
            $this->booted(fn() => throw $e);
        }

        return is_object($provider) ? $provider : new class($this) extends ServiceProvider
        {
            //
        };
    }

    /**
     * 获取应用程序命名空间。
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    public function getNamespace()
    {
        if (! is_null($this->namespace)) {
            return $this->namespace;
        }

        $composer = json_decode(file_get_contents($composerPath = $this->getAppComposer()), true);

        foreach ((array) data_get($composer, 'autoload.psr-4') as $namespace => $path) {
            foreach ((array) $path as $pathChoice) {
                if (realpath($this->path()) === realpath(dirname($composerPath) . DIRECTORY_SEPARATOR . $pathChoice)) {
                    return $this->namespace = $namespace;
                }
            }
        }

        throw new RuntimeException('无法检测应用程序命名空间。');
    }

    /**
     * 获取应用程序使用的 composer.json 文件。
     *
     * 此功能将从 app path 开始，并向上走
     * 目录结构，直到找到 composer.json 文件。
     *
     * 如果未找到，则它将假定存在一个
     * composer.json 文件。
     */
    protected function getAppComposer(): string
    {
        return (new Filesystem)->closest($this->path(), 'composer.json') ?? $this->basePath('composer.json');
    }

    /**
     * 设置应用程序命名空间。
     *
     * @param  string  $namespace
     * @return $this
     */
    public function useNamespace($namespace)
    {
        $this->namespace = trim($namespace, '\\') . '\\';

        return $this;
    }

    /**
     * 获取应用程序的版本号。
     *
     * @return string
     */
    public function version()
    {
        return 'Monk ' . static::VERSION . ' (Laravel ' . parent::VERSION . ')';
    }
}
