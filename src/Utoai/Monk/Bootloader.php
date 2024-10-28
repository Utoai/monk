<?php

namespace Utoai\Monk;

use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Str;
use Utoai\Monk\Filesystem\Filesystem;

class Bootloader
{
    /**
     * 引导加载程序实例。
     */
    protected static $instance;

    /**
     *应用程序实例。
     */
    protected ?ApplicationContract $app;

    /**
     *文件系统实例。
     */
    protected Filesystem $files;

    /**
     *应用程序的基本路径。
     */
    protected string $basePath = '';

    /**
     *规范化期间使用的绝对缓存路径的前缀。
     */
    protected array $absoluteApplicationPathPrefixes = ['/', '\\'];

    /**
     *创建一个新的引导加载程序实例。
     */
    public function __construct(?ApplicationContract $app = null, ?Filesystem $files = null)
    {
        $this->app = $app;
        $this->files = $files ?? new Filesystem;

        static::$instance ??= $this;
    }

    /**
     *启动应用程序。
     */
    public function __invoke(): void
    {
        $this->boot();
    }

    /**
     *设置Bootloader实例。
     */
    public static function setInstance(?self $bootloader): void
    {
        static::$instance = $bootloader;
    }

    /**
     *获取Bootloader实例。
     */
    public static function getInstance(?ApplicationContract $app = null): static
    {
        return static::$instance ??= new static($app);
    }

    /**
     *启动应用程序。
     */
    public function boot(?callable $callback = null): void
    {
        if (! defined('LARAVEL_START')) {
            define('LARAVEL_START', microtime(true));
        }

        $this->getApplication();

        if ($callback) {
            $callback($this->app);
        }

        if ($this->app->hasBeenBootstrapped()) {
            return;
        }

        $this->bootHttp();
    }

    /**
     *启动 HTTP 请求的应用程序。
     */
    protected function bootHttp(): void
    {
        $kernel = $this->app->make(\Illuminate\Contracts\Http\Kernel::class);
        $request = \Illuminate\Http\Request::capture();

        $this->app->instance('request', $request);

        Facade::clearResolvedInstance('request');

        $kernel->bootstrap($request);
    }

    /**
     *初始化并检索应用程序实例。
     */
    public function getApplication(): ApplicationContract
    {
        $this->app ??= new Application($this->basePath(), $this->usePaths());

        $this->app->singleton(
            \Illuminate\Contracts\Http\Kernel::class,
            \Utoai\Monk\Http\Kernel::class
        );

        $this->app->singleton(
            \Illuminate\Contracts\Debug\ExceptionHandler::class,
            \Utoai\Monk\Exceptions\Handler::class
        );

        return $this->app;
    }

    /**
     * 获取应用程序的基本路径。
     */
    protected function basePath(): string
    {
        if ($this->basePath) {
            return $this->basePath;
        }

        if (isset($_ENV['APP_BASE_PATH'])) {
            return $this->basePath = $_ENV['APP_BASE_PATH'];
        }

        if (defined('ACORN_BASEPATH')) {
            return $this->basePath = constant('ACORN_BASEPATH');
        }

        if (is_file($composerPath = get_theme_file_path('composer.json'))) {
            return $this->basePath = dirname($composerPath);
        }

        if (is_dir($appPath = get_theme_file_path('app'))) {
            return $this->basePath = dirname($appPath);
        }

        if (is_file($vendorPath = $this->files->closest(dirname(__DIR__, 4), 'composer.json'))) {
            return $this->basePath = dirname($vendorPath);
        }

        return $this->basePath = dirname(__DIR__, 3);
    }

    /**
     *获取环境文件路径。
     */
    protected function environmentPath(): string
    {
        $envPath = $this->files->closest($this->basePath(), '.env') ?? '';

        if (is_file($envPath)) {
            return dirname($envPath);
        } else {
            return $this->basePath();
        }
    }

    /**
     * 使用开发者可配置的路径
     */
    protected function usePaths(): array
    {
        $paths = [];

        foreach (['app', 'config', 'storage', 'resources', 'public'] as $path) {
            $normalizedPath = $this->normalizeApplicationPath($path, null);
            $paths[$path] = $normalizedPath;
        }

        if (isset($paths['storage'])) {
            $paths['bootstrap'] = $this->normalizeApplicationPath('bootstrap', "{$paths['storage']}/framework");
        }

        return $paths;
    }

    /**
     * 标准化应用程序目录的相对或绝对路径
     */
    protected function normalizeApplicationPath(string $path, ?string $default = null): string
    {
        $key = strtoupper($path);

        $env = Env::get("ACORN_{$key}_PATH");
        if (is_null($env)) {
            if (defined("ACORN_{$key}_PATH")) {
                return constant("ACORN_{$key}_PATH");
            } elseif ($default) {
                return $default;
            } else {
                return $this->findPath($path);
            }
        }

        $isAbsolutePath = Str::startsWith($env, $this->absoluteApplicationPathPrefixes);
        if ($isAbsolutePath) {
            return $env;
        } else {
            return $this->basePath($env);
        }
    }

    /**
     * 将新前缀添加到绝对路径前缀列表中
     */
    public function addAbsoluteApplicationPathPrefix(string $prefix): self
    {
        $this->absoluteApplicationPathPrefixes[] = $prefix;

        return $this;
    }

    /**
     * 找到开发者可配置的路径
     */
    protected function findPath(string $path): string
    {
        $path = trim($path, '\\/');

        $searchPaths = [
            $this->basePath() . DIRECTORY_SEPARATOR . $path,
            get_theme_file_path($path),
        ];

        $result = collect($searchPaths)
            ->map(fn($path) => (is_string($path) && is_dir($path)) ? $path : null)
            ->filter()
            ->whenEmpty(fn($paths) => $paths->add($this->fallbackPath($path)))
            ->unique()
            ->first();

        return $result;
    }

    /**
     * 路径类型的后备
     */
    protected function fallbackPath(string $path): string
    {
        $fallback = match ($path) {
            'storage' => $this->fallbackStoragePath(),
            'app' => "{$this->basePath()}/app",
            'public' => "{$this->basePath()}/public",
            default => dirname(__DIR__, 3) . "/{$path}",
        };

        return $fallback;
    }

    /**
     * 确保所有存储目录都存在
     */
    protected function fallbackStoragePath(): string
    {
        $path = Str::finish(__TYPECHO_ROOT_DIR__, '/usr/cache/monk');

        $directories = [
            'framework/cache/data',
            'framework/views',
            'framework/sessions',
            'logs',
        ];

        foreach ($directories as $directory) {
            $fullDirectoryPath = "{$path}/{$directory}";
            $this->files->ensureDirectoryExists($fullDirectoryPath, 0755, true);
        }

        return $path;
    }
}