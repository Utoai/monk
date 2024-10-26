<?php

namespace Utoai\Monk\FounBox;

use Illuminate\Support\ServiceProvider;

class BoxServiceProvider extends ServiceProvider
{
    /**
     * 注册应用程序服务。
     *
     * @return void
     */
    public function register()
    {
        // 注册默认服务，比如日志、数据库、缓存等 
        echo "子包服务已注册!\n <br /><br />";
    }

    /**
     * 启动应用程序服务。
     *
     * @return void
     */
    public function boot()
    {
        // 启动服务，比如视图、事件监听
        echo "子包服务已启动!\n<br /><br />";
    }
}
