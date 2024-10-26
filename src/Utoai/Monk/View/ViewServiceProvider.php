<?php

namespace Utoai\Monk\View;

use Illuminate\Contracts\View\Engine;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\View;
use Illuminate\View\ViewServiceProvider as ViewServiceProviderBase;
use Utoai\Monk\View\Composers\Debugger;

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
         * 创建一个加载器供稍后调用的视图
         *
         * @return string
         */
        View::macro('makeLoader', function () use ($app) {
            $view = $this->getName();
            $path = $this->getPath();
            $id = md5($this->getCompiled());
            $compiledPath = $app['config']['view.compiled'];
            $compiledExtension = $app['config']->get('view.compiled_extension', 'php');

            $content = "<?= \\Roots\\view('{$view}', \$data ?? get_defined_vars())->render(); ?>"
                . "\n<?php /**PATH {$path} ENDPATH**/ ?>";

            if (! file_exists($loader = "{$compiledPath}/{$id}-loader.{$compiledExtension}")) {
                file_put_contents($loader, $content);
            }

            return $loader;
        });
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
