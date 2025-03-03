<?php

namespace  Utoai\Monk\Console\Commands;

use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use  Utoai\Monk\Console\Concerns\GetsFreshApplication;

class RouteCacheCommand extends \Illuminate\Foundation\Console\RouteCacheCommand
{
    use GetsFreshApplication {
        getFreshApplication as protected parentGetFreshApplication;
    }

    /**
     * Get a fresh application instance.
     *
     * @return \Illuminate\Contracts\Foundation\Application
     */
    protected function getFreshApplication()
    {
        return tap($this->parentGetFreshApplication(), function ($app) {
            $app->make(ConsoleKernelContract::class)->bootstrap();
        });
    }
}
