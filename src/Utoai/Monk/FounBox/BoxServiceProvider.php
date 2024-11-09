<?php

namespace Utoai\Monk\FounBox;

use Typecho\Plugin;
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
        $this->app->singleton('sage', FounBox::class);
        // echo "子包服务已注册!\n <br /><br />";
    }

    /**
     * 启动应用程序服务。
     *
     * @return void
     */
    public function boot()
    {
        $this->bindCompatFilters();
        // echo "子包服务已启动!\n<br /><br />";
    }

    /**
     * 兼容性过滤器
     *
     * 这些是  功能正常运行所需的过滤器
     *
     * @return void
     */
    protected function bindCompatFilters()
    {
        $sage = $this->app['sage'];  // 获取 Sage 实例，假设你依旧需要它的某些功能

        // $archive = \Typecho\Widget::widget('\Widget\Archive');
        // $archiveType = $archive->getArchiveType(); // 获取归档类型  
        // echo $archiveType;
        // var_dump($archive->is('page'));
        // var_dump($archive->is('post'));
        // var_dump($archive->is('category'));
        // var_dump($archive->is('tag'));
        // var_dump($archive->is('search'));
        // var_dump($archive->is('author'));
        // var_dump($archive->is('page'));
        $sage->filter('comments_template');
    }
}
