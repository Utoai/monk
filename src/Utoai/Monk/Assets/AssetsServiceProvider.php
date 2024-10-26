<?php

namespace Utoai\Monk\Assets;

use Illuminate\Support\ServiceProvider;
use Utoai\Monk\Assets\View\BladeDirective;

class AssetsServiceProvider extends ServiceProvider
{
    /**
     * 注册服务。
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('assets', function () {
            return new Manager($this->app->make('config')->get('assets'));
        });

        $this->app->singleton('assets.vite', Vite::class);

        $this->app->singleton('assets.manifest', function ($app) {
            return $app['assets']->manifest($this->getDefaultManifest());
        });

        $this->app->alias('assets.manifest', \Utoai\Monk\Assets\Manifest::class);
    }

    /**
     * 引导服务。
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->bound('view')) {
            $this->app->make('view')
                ->getEngineResolver()->resolve('blade')->getCompiler()
                ->directive('asset', new BladeDirective());
        }
    }

    /**
     * 获取默认清单。
     *
     * @return string
     */
    protected function getDefaultManifest()
    {
        return $this->app['config']['assets.default'];
    }
}
