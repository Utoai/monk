<?php

namespace Utoai\Monk\Application\Concerns;

use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use Illuminate\Contracts\Http\Kernel as HttpKernelContract;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Throwable;

trait Bootable
{
    /**
     * 引导应用程序的服务提供商。
     *
     * @return $this
     */
    public function bootAcorn()
    {

        if ($this->isBooted()) {
            return $this;
        }

        if (! defined('LARAVEL_START')) {
            define('LARAVEL_START', microtime(true));
        }

        $this->bootHttp();

        return $this;
    }


    /**
     * 引导应用程序以接收 HTTP 请求。
     */
    protected function bootHttp(): void
    {
        // 初始化 HTTP 内核
        $kernel = $this->make(HttpKernelContract::class);
        // 捕获当前请求
        $request = Request::capture();
        // 将请求实例传递给 Laravel 容器
        $this->instance('request', $request);

        // 清除已解析的实例，确保新的请求实例被正确处理
        Facade::clearResolvedInstance('request');
        // 引导 HTTP 内核
        $kernel->bootstrap($request);

        // try {
        //     $route = $this->make('router')->getRoutes()->match($request);

        //     $this->registerRequestHandler($request, $route);
        // } catch (Throwable) {
        //     //
        // }
    }

    /**
     * 注册请求处理程序。
     * 如果当前请求没有匹配的路由，则执行此操作。
     * 
     * @需要移植到typecho的路由
     */
    protected function registerRequestHandler(
        \Illuminate\Http\Request $request,
        ?\Illuminate\Routing\Route $route
    ): void {
        // 构建完整的请求路径
        $path = Str::finish($request->getBaseUrl(), $request->getPathInfo());

        // 定义不需要处理的路径，如管理后台和登录页面等
        $except = collect([
            '/admin',
        ])->map(fn($url) => parse_url($url, PHP_URL_PATH))->unique()->filter();


        // 如果请求路径以不需要处理的路径开头或以.php结尾，则不执行后续操作
        if (
            Str::startsWith($path, $except->all()) ||
            Str::endsWith($path, '.php')
        ) {
            return;
        }



        // 开始输出缓冲，以控制输出
        ob_start();
    }

    /**
     * 处理请求。
     */
    public function handleRequest(\Illuminate\Http\Request $request): void
    {
        $kernel = $this->make(HttpKernelContract::class);

        $response = $kernel->handle($request);

        $response->send();

        $kernel->terminate($request, $response);

        exit((int) $response->isServerError());
    }
}
