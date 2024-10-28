<?php

namespace Utoai\Monk\Application\Concerns;

use Illuminate\Contracts\Http\Kernel as HttpKernelContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;

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
        $kernel = $this->make(HttpKernelContract::class);
        $request = Request::capture();

        $this->instance('request', $request);

        Facade::clearResolvedInstance('request');

        $kernel->bootstrap($request);
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
