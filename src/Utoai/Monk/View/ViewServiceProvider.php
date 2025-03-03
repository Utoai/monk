<?php

namespace Utoai\Monk\View;

use Illuminate\Contracts\View\Engine;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\View;
use Illuminate\View\ViewServiceProvider as ViewServiceProviderBase;
use ReflectionClass;
use Utoai\Monk\View\Composers\Debugger;
use Symfony\Component\Finder\Finder;

class ViewServiceProvider extends ViewServiceProviderBase
{
    /**
     * 注册任何应用程序服务。
     *
     * @return void
     */
    public function register()
    {
        parent::register();
        $this->registerMacros();
    }

    /**
     * 引导任何应用程序服务。
     *
     * @return void
     */
    public function boot()
    {
        $this->attachDirectives();
        $this->attachComponents();
        $this->attachComposers();

        if ($this->app['config']['view.debug']) {
            $this->attachDebugger();
        }
    }

    /**
     * 返回一个View的实例。
     *
     * @return View
     */
    protected function view()
    {
        return $this->app['view'];
    }

    /**
     * 注册取景器
     *
     * @return void
     */
    public function registerViewFinder()
    {
        $this->app->bind('view.finder', function ($app) {
            $finder = new FileViewFinder($app['files'], array_unique($app['config']['view.paths']));

            foreach ($app['config']['view.namespaces'] as $namespace => $hints) {
                $hints = array_merge(
                    array_map(fn($path) => "{$path}/vendor/{$namespace}", $finder->getPaths()),
                    (array) $hints
                );

                $finder->addNamespace($namespace, $hints);
            }

            return $finder;
        });

        $this->app->alias('view.finder', FileViewFinder::class);
    }


    /**
     * 注册视图宏
     *
     * @return void
     */
    public function registerMacros()
    {
        $app = $this->app;

        /**
         * 获取视图的编译路径
         *
         * @return string
         */
        View::macro('getCompiled', function () {
            /** @var string $file path to file */
            $file = $this->getPath();

            /** @var Engine $engine */
            $engine = $this->getEngine();

            return ($engine instanceof CompilerEngine)
                ? $engine->getCompiler()->getCompiledPath($file)
                : $file;
        });

        /**
         * 为稍后调用的视图创建一个加载器
         *
         * @return string
         */
        View::macro('makeLoader', function () use ($app) {
            $view = $this->getName();
            $path = $this->getPath();
            $id = md5($this->getCompiled());
            $compiledPath = $app['config']['view.compiled'];
            $compiledExtension = $app['config']->get('view.compiled_extension', 'php');

            $content = "<?= \\Utoai\\view('{$view}', \$data ?? get_defined_vars())->render(); ?>"
                . "\n<?php /**PATH {$path} ENDPATH**/ ?>";

            if (! file_exists($loader = "{$compiledPath}/{$id}-loader.{$compiledExtension}")) {
                file_put_contents($loader, $content);
            }

            return $loader;
        });
    }


    /**
     * Attach 视图指令
     *
     * @return void
     */
    public function attachDirectives()
    {
        $blade = $this->view()->getEngineResolver()->resolve('blade')->getCompiler();
        $directives = $this->app['config']['view.directives'];

        foreach ($directives as $name => $handler) {
            if (! is_callable($handler)) {
                $handler = $this->app->make($handler);
            }

            $blade->directive($name, $handler);
        }
    }
    /**
     * Attach View 组件
     *
     * @return void
     */
    public function attachComponents()
    {
        $components = $this->app->config['view.components'];

        if (is_array($components) && Arr::isAssoc($components)) {
            $blade = $this->view()->getEngineResolver()->resolve('blade')->getCompiler();

            foreach ($components as $alias => $view) {
                $blade->component($view, $alias);
            }
        }
    }


    /**
     * Attach View 作曲家
     *
     * @return void
     */
    public function attachComposers()
    {
        $composers = $this->app->config['view.composers'];

        if (is_array($composers) && Arr::isAssoc($composers)) {
            foreach ($composers as $composer) {
                $this->view()->composer($composer::views(), $composer);
            }
        }

        if (! is_dir($path = $this->app->path('View/Composers'))) {
            return;
        }

        $namespace = $this->app->getNamespace();


        foreach ((new Finder())->in($path)->files() as $composer) {
            $composer = $namespace . str_replace(
                ['/', '.php'],
                ['\\', ''],
                Str::after($composer->getPathname(), $this->app->path() . DIRECTORY_SEPARATOR)
            );

            if (
                is_subclass_of($composer, Composer::class) &&
                ! (new ReflectionClass($composer))->isAbstract()
            ) {
                $this->view()->composer($composer::views(), $composer);
            }
        }
    }



    /**
     * 连接视图调试器
     *
     * @return void
     */
    public function attachDebugger()
    {
        $this->view()->composer('*', Debugger::class);
    }
}
