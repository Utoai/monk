<?php

namespace Utoai\Monk\Configuration;

use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Bootstrap\RegisterProviders;
use Illuminate\Foundation\Configuration\ApplicationBuilder as FoundationApplicationBuilder;
use Utoai\Monk\Configuration\Concerns\Paths;

class ApplicationBuilder extends FoundationApplicationBuilder
{
    use Paths;

    /**
     *  为应用程序注册标准内核类。
     *
     * @return $this
     */
    public function withKernels()
    {
        $this->app->singleton(
            \Illuminate\Contracts\Http\Kernel::class,
            \Utoai\Monk\Http\Kernel::class
        );

        $this->app->singleton(
            \Illuminate\Contracts\Console\Kernel::class,
        );

        return $this;
    }

    /**
     * 注册并配置应用程序的异常处理程序。
     *
     * @return $this
     */
    public function withExceptions(?callable $using = null)
    {
        $this->app->singleton(
            \Illuminate\Contracts\Debug\ExceptionHandler::class,
            \Utoai\Monk\Exceptions\Handler::class,
        );

        $using ??= fn () => true;

        $this->app->afterResolving(
            \Utoai\Monk\Exceptions\Handler::class,
            fn ($handler) => $using(new Exceptions($handler)),
        );

        return $this;
    }

    /**
     * 为应用程序注册全局中间件、中间件组和中间件别名。
     *
     * @return $this
     */
    public function withMiddleware(?callable $callback = null)
    {
        $this->app->afterResolving(HttpKernel::class, function ($kernel) use ($callback) {
            $middleware = new Middleware;

            if (! is_null($callback)) {
                $callback($middleware);
            }

            $this->pageMiddleware = $middleware->getPageMiddleware();

            $kernel->setGlobalMiddleware($middleware->getGlobalMiddleware());
            $kernel->setMiddlewareGroups($middleware->getMiddlewareGroups());
            $kernel->setMiddlewareAliases($middleware->getMiddlewareAliases());

            if ($priorities = $middleware->getMiddlewarePriority()) {
                $kernel->setMiddlewarePriority($priorities);
            }
        });

        return $this;
    }

    /**
     * 注册其他服务提供商。
     *
     * @return $this
     */
    public function withProviders(array $providers = [], bool $withBootstrapProviders = true)
    {
        RegisterProviders::merge(
            $providers,
            $withBootstrapProviders
                ? $this->app->getBootstrapProvidersPath()
                : null
        );

        return $this;
    }

    /**
     * 获取应用程序实例。
     *
     * @return \Utoai\Monk\Application
     */
    public function create()
    {
        return $this->app;
    }

    /**
     * 引导应用程序。
     *
     * @return \Utoai\Monk\Application
     */
    public function boot()
    {
        return $this->app->bootAcorn();
    }
}
