<?php

namespace Utoai\Monk\Providers;

use Illuminate\Queue\Worker;
use Illuminate\Support\ServiceProvider;

class QueueServiceProvider extends ServiceProvider
{
    /**
     * 注册服务。
     *
     * @return void
     */
    public function register()
    {
        $this->app->alias('queue.worker', Worker::class);
    }

    /**
     * 引导服务。
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
