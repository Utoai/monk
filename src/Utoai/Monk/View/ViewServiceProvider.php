<?php

namespace Utoai\Monk\View;

use Illuminate\Support\Arr;
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
