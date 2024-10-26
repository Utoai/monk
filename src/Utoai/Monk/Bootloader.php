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
            echo '基本路径已设置：' . $this->basePath . '<br>';
            return $this->basePath;
        }

        // 检查环境变量 APP_BASE_PATH
        if (isset($_ENV['APP_BASE_PATH'])) {
            echo '使用环境变量 APP_BASE_PATH：' . $_ENV['APP_BASE_PATH'] . '<br>';
            return $this->basePath = $_ENV['APP_BASE_PATH'];
        }

        // 检查常量 ACORN_BASEPATH
        if (defined('ACORN_BASEPATH')) {
            echo '使用常量 ACORN_BASEPATH：' . constant('ACORN_BASEPATH') . '<br>';
            return $this->basePath = constant('ACORN_BASEPATH');
        }

        // 检查主题文件路径中的 composer.json 文件
        var_dump(get_theme_file_path('composer.json') . '<br>');
        if (is_file($composerPath = get_theme_file_path('composer.json'))) {
            echo '在主题路径中找到 composer.json 文件：' . $composerPath . '<br>';
            return $this->basePath = dirname($composerPath);
        }

        // 检查主题文件路径中的 app 目录
        if (is_dir($appPath = get_theme_file_path('app'))) {
            echo '在主题路径中找到 app 目录：' . $appPath . '<br>';
            return $this->basePath = dirname($appPath);
        }

        // 检查 vendor 文件路径中的 composer.json 文件
        if (is_file($vendorPath = $this->files->closest(dirname(__DIR__, 4), 'composer.json'))) {
            echo '在 vendor 路径中找到 composer.json 文件：' . $vendorPath . '<br>';
            return $this->basePath = dirname($vendorPath);
        }

        // 默认路径
        echo '使用默认路径：' . dirname(__DIR__, 3) . '<br>';
        return $this->basePath = dirname(__DIR__, 3);
    }


    /**
     *获取环境文件路径。
     */
    protected function environmentPath(): string
    {
        // 调试基路径的获取
        echo '获取基本路径：' . $this->basePath() . '<br>';

        // 获取 .env 文件的路径
        $envPath = $this->files->closest($this->basePath(), '.env') ?? '';

        // 输出 .env 文件查找结果
        if ($envPath) {
            echo '找到 .env 文件路径：' . $envPath . '<br>';
        } else {
            echo '.env 文件未找到<br>';
        }

        // 判断 .env 文件是否存在并返回相应路径
        if (is_file($envPath)) {
            echo '.env 文件存在，返回路径：' . dirname($envPath) . '<br>';
            return dirname($envPath);
        } else {
            echo '.env 文件不存在，使用基本路径：' . $this->basePath() . '<br>';
            return $this->basePath();
        }
    }


    /**
     * 使用开发者可配置的路径
     */
    protected function usePaths(): array
    {
        $paths = [];
        echo '<br><br>开始配置应用路径：<br><br>';

        // 遍历每个需要配置的路径
        foreach (['app', 'config', 'storage', 'resources', 'public'] as $path) {
            // 调用 normalizeApplicationPath 规范化路径
            $normalizedPath = $this->normalizeApplicationPath($path, null);
            echo "路径 '{$path}' 的标准化结果：{$normalizedPath}<br><br>";

            // 将规范化后的路径加入到路径数组中
            $paths[$path] = $normalizedPath;
        }

        // 处理 'bootstrap' 路径，设置默认存储路径下的 'framework' 目录
        if (isset($paths['storage'])) {
            $paths['bootstrap'] = $this->normalizeApplicationPath('bootstrap', "{$paths['storage']}/framework");
            echo "路径 'bootstrap' 的标准化结果：{$paths['bootstrap']}<br>";
        } else {
            echo "警告：'storage' 路径未设置，无法生成 'bootstrap' 路径。<br>";
        }

        echo '配置完成的路径数组：<br><br><br>';
        var_dump($paths);

        return $paths;
    }


    /**
     * 标准化应用程序目录的相对或绝对路径
     */
    protected function normalizeApplicationPath(string $path, ?string $default = null): string
    {
        $key = strtoupper($path);
        echo "正在标准化路径：{$path}（键名：{$key}）<br>";

        // 获取环境变量中的路径
        $env = Env::get("ACORN_{$key}_PATH");
        if (is_null($env)) {
            echo "环境变量 ACORN_{$key}_PATH 未设置<br>";

            // 如果环境变量未设置，使用默认路径或常量路径
            if (defined("ACORN_{$key}_PATH")) {
                $constantPath = constant("ACORN_{$key}_PATH");
                echo "使用常量 ACORN_{$key}_PATH：{$constantPath}<br>";
                return $constantPath;
            } elseif ($default) {
                echo "使用默认路径：{$default}<br>";
                return $default;
            } else {
                $foundPath = $this->findPath($path);
                echo "通过 findPath 方法找到的路径：{$foundPath}<br>";
                return $foundPath;
            }
        } else {
            echo "环境变量 ACORN_{$key}_PATH 设置为：{$env}<br>";
        }

        // 检查环境变量路径是否为绝对路径
        $isAbsolutePath = Str::startsWith($env, $this->absoluteApplicationPathPrefixes);
        if ($isAbsolutePath) {
            echo "路径 {$env} 是绝对路径，直接返回<br>";
            return $env;
        } else {
            $basePathEnv = $this->basePath($env);
            echo "路径 {$env} 不是绝对路径，基于 basePath 计算后的路径：{$basePathEnv}<br>";
            return $basePathEnv;
        }
    }


    /**
     * 将新前缀添加到绝对路径前缀列表中
     */
    public function addAbsoluteApplicationPathPrefix(string $prefix): self
    {
        $this->absoluteApplicationPathPrefixes[] = $prefix;
        echo "添加了绝对路径前缀：{$prefix}<br>";
        echo "当前绝对路径前缀列表：" . implode(', ', $this->absoluteApplicationPathPrefixes) . "<br>";

        return $this;
    }


    /**
     * 找到开发者可配置的路径
     */
    protected function findPath(string $path): string
    {
        $path = trim($path, '\\/');
        echo "<br><br>标准化后的路径：{$path}<br>";

        $searchPaths = [
            $this->basePath() . DIRECTORY_SEPARATOR . $path,
            get_theme_file_path($path),
        ];

        // 输出要搜索的路径数组
        echo "待搜索路径列表：<br>";
        foreach ($searchPaths as $searchPath) {
            echo "- {$searchPath}<br>";
        }

        // 使用 collect 进行路径筛选
        $result = collect($searchPaths)
            ->map(fn($path) => (is_string($path) && is_dir($path)) ? $path : null)
            ->filter()
            ->whenEmpty(fn($paths) => $paths->add($this->fallbackPath($path)))
            ->unique()
            ->first();

        echo "找到的路径：" . ($result ?: '无匹配路径，使用后备路径') . "<br><br><br>";
        return $result;
    }


    /**
     * 路径类型的后备
     */
    protected function fallbackPath(string $path): string
    {
        echo "正在处理后备路径，路径类型：{$path}<br>";

        $fallback = match ($path) {
            'storage' => $this->fallbackStoragePath(),
            'app' => "{$this->basePath()}/app",
            'public' => "{$this->basePath()}/public",
            default => dirname(__DIR__, 3) . "/{$path}",
        };

        echo "计算的后备路径为：{$fallback}<br>";
        return $fallback;
    }

    /**
     * 确保所有存储目录都存在
     */
    protected function fallbackStoragePath(): string
    {
        // 确定存储路径的基础路径
        $path = Str::finish(__TYPECHO_ROOT_DIR__, '/usr/cache/monk');
        echo "生成的存储基础路径：{$path}<br>";

        // 定义所需的子目录
        $directories = [
            'framework/cache/data',
            'framework/views',
            'framework/sessions',
            'logs',
        ];

        // 遍历并确保每个目录存在
        foreach ($directories as $directory) {
            $fullDirectoryPath = "{$path}/{$directory}";

            // 调用 ensureDirectoryExists 方法
            echo "确保目录存在：{$fullDirectoryPath}<br>";
            $this->files->ensureDirectoryExists($fullDirectoryPath, 0755, true);

            // 检查目录创建结果
            if (is_dir($fullDirectoryPath)) {
                echo "目录已存在或已成功创建：{$fullDirectoryPath}<br>";
            } else {
                echo "警告：无法创建目录：{$fullDirectoryPath}<br>";
            }
        }

        return $path;
    }
}
