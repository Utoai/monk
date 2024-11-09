<?php

namespace Utoai\Monk\Configuration\Concerns;

use Illuminate\Foundation\Application;
use Illuminate\Support\Env;
use Illuminate\Support\Str;
use Utoai\Monk\Filesystem\Filesystem;

trait Paths
{
    protected Application $app;

    /**
     * 从环境中推断应用程序的基目录。
     *
     * @return string
     */
    public static function inferBasePath()
    {
        return match (true) {
            isset($_ENV['APP_BASE_PATH']) => $_ENV['APP_BASE_PATH'],

            defined('ACORN_BASEPATH') => constant('ACORN_BASEPATH'),

            is_file($composerPath = get_theme_file_path('composer.json')) => dirname($composerPath),

            is_dir($appPath = get_theme_file_path('app')) => dirname($appPath),

            optional($vendorPath = (new Filesystem)->closest(dirname(__DIR__, 6), 'composer.json'), 'is_file') => dirname($vendorPath),

            default => dirname(__DIR__, 5),
        };
    }

    /**
     * 注册并配置应用程序的路径。
     *
     * @return $this
     */
    public function withPaths(
        ?string $app = null,
        ?string $config = null,
        ?string $storage = null,
        ?string $resources = null,
        ?string $public = null,
        ?string $bootstrap = null,
        ?string $lang = null,
        ?string $database = null
    ) {
        $this->app->usePaths(
            array_filter(compact(
                'app',
                'config',
                'storage',
                'resources',
                'public',
                'bootstrap',
                'lang',
                'database'
            )) + $this->defaultPaths()
        );

        return $this;
    }

    /**
     * 使用配置的默认路径。
     */
    public function defaultPaths(): array
    {
        $paths = [];

        foreach (['app', 'config', 'storage', 'resources', 'public', 'lang', 'database'] as $path) {
            $paths[$path] = $this->normalizeApplicationPath($path);
        }

        $paths['bootstrap'] = $this->normalizeApplicationPath($path, "{$paths['storage']}/framework");

        return $paths;
    }

    /**
     * 规范化应用程序目录的相对或绝对路径。
     */
    protected function normalizeApplicationPath(string $path, ?string $default = null): string
    {
        $key = strtoupper($path);

        if (is_null($env = Env::get("ACORN_{$key}_PATH"))) {
            return $default
                ?? (defined("ACORN_{$key}_PATH") ? constant("ACORN_{$key}_PATH") : $this->findPath($path));
        }

        return Str::startsWith($env, $this->app->absoluteCachePathPrefixes)
            ? $env
            : $this->app->basePath($env);
    }

    /**
     * 查找开发人员可配置的路径。
     */
    protected function findPath(string $path): string
    {
        $path = trim($path, '\\/');

        $method = $path === 'app' ? 'path' : "{$path}Path";

        $searchPaths = [
            method_exists($this->app, $method) ? $this->app->{$method}() : null,
            $this->app->basePath($path),
            get_theme_file_path($path),
        ];

        return collect($searchPaths)
            ->filter(fn($path) => (is_string($path) && is_dir($path)))
            ->whenEmpty(fn($paths) => $paths->add($this->fallbackPath($path)))
            ->unique()
            ->first();
    }

    /**
     * 路径类型的回退。
     */
    protected function fallbackPath(string $path): string
    {
        return $path === 'storage'
            ? $this->fallbackStoragePath()
            : $this->app->basePath($path);
    }

    /**
     * 确保所有存储目录都存在。
     */
    protected function fallbackStoragePath(): string
    {
        $files = new Filesystem;
        $path = Str::finish(__TYPECHO_ROOT_DIR__, '/usr/cache/monk');

        foreach (
            [
                'framework/cache/data',
                'framework/views',
                'framework/sessions',
                'logs',
            ] as $directory
        ) {
            $files->ensureDirectoryExists("{$path}/{$directory}", 0755, true);
        }

        return $path;
    }
}
