<?php

namespace Utoai\Monk\Sage;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Request;  // 导入 Request 门面
use Widget\Action;
use Typecho\Widget;

class SageServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('sage', Sage::class);
        $this->app->bind('sage.finder', ViewFinder::class);
    }

    public function boot()
    {
        $this->bindCompatFilters();
    }

    protected function bindCompatFilters()
    {
        $sage = $this->app['sage'];
        
        // 调用模板层次结构处理
        $view = $sage->resolveTemplate();

        // 将视图名称和数据绑定到容器
        $this->app->bind('sage.view', fn() => $view);
        $this->app->bind('sage.data', fn() => $sage->getViewData());
        
        // 如需要完全使用blade模板，请取消注释以下代码
        // $this->renderTemplate();
    }

    /**
     * 渲染模板
     */
    protected function renderTemplate()
    {
        if (view()->exists(app('sage.view'))) {
            echo view(app('sage.view'), app('sage.data'))->render();
            exit;
        }
    }
}
