<?php

namespace Utoai\Monk\Console;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Console\Kernel as FoundationConsoleKernel;

class Kernel extends FoundationConsoleKernel
{
    /**
     * The Console commands provided by the application.
     *
     * @var array
     */
    protected $commands = [
        \Illuminate\Cache\Console\ClearCommand::class,
        \Illuminate\Cache\Console\ForgetCommand::class,
        \Illuminate\Database\Console\DbCommand::class,
        \Illuminate\Database\Console\Seeds\SeedCommand::class,
        \Illuminate\Database\Console\Seeds\SeederMakeCommand::class,
        \Illuminate\Database\Console\TableCommand::class,
        \Illuminate\Database\Console\WipeCommand::class,
        \Illuminate\Foundation\Console\ClearCompiledCommand::class,
        \Illuminate\Foundation\Console\ComponentMakeCommand::class,
        \Illuminate\Foundation\Console\ConfigClearCommand::class,
        \Illuminate\Foundation\Console\ConsoleMakeCommand::class,
        \Illuminate\Foundation\Console\EnvironmentCommand::class,
        \Illuminate\Foundation\Console\JobMakeCommand::class,
        \Illuminate\Foundation\Console\PackageDiscoverCommand::class,
        \Illuminate\Foundation\Console\ProviderMakeCommand::class,
        \Illuminate\Foundation\Console\RouteClearCommand::class,
        \Illuminate\Foundation\Console\RouteListCommand::class,
        \Illuminate\Foundation\Console\ViewCacheCommand::class,
        \Illuminate\Foundation\Console\ViewClearCommand::class,
        \Illuminate\Queue\Console\TableCommand::class,
        \Illuminate\Queue\Console\WorkCommand::class,
        \Illuminate\Routing\Console\ControllerMakeCommand::class,
        \Illuminate\Routing\Console\MiddlewareMakeCommand::class,
        \Utoai\Monk\Console\Commands\AboutCommand::class,
        \Utoai\Monk\Console\Commands\AcornInitCommand::class,
        \Utoai\Monk\Console\Commands\AcornInstallCommand::class,
        \Utoai\Monk\Console\Commands\ComposerMakeCommand::class,
        \Utoai\Monk\Console\Commands\ConfigCacheCommand::class,
        \Utoai\Monk\Console\Commands\KeyGenerateCommand::class,
        \Utoai\Monk\Console\Commands\OptimizeClearCommand::class,
        \Utoai\Monk\Console\Commands\OptimizeCommand::class,
        \Utoai\Monk\Console\Commands\RouteCacheCommand::class,
        \Utoai\Monk\Console\Commands\SummaryCommand::class,
        \Utoai\Monk\Console\Commands\VendorPublishCommand::class,
    ];

    /**
     * The bootstrap classes for the application.
     *
     * @var string[]
     */
    protected $bootstrappers = [
        \Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class,
        \Illuminate\Foundation\Bootstrap\LoadConfiguration::class,
        \Utoai\Monk\Bootstrap\HandleExceptions::class,
        \Utoai\Monk\Bootstrap\RegisterFacades::class,
        \Illuminate\Foundation\Bootstrap\SetRequestForConsole::class,
        \Illuminate\Foundation\Bootstrap\RegisterProviders::class,
        \Illuminate\Foundation\Bootstrap\BootProviders::class,
    ];

    /**
     * Create a new console kernel instance.
     *
     * @return void
     */
    public function __construct(Application $app, Dispatcher $events)
    {
        if (! defined('ARTISAN_BINARY')) {
            define('ARTISAN_BINARY', dirname(__DIR__, 4).'/bin/acorn');
        }

        $this->app = $app;
        $this->events = $events;

        $this->app->booted(function () {
            $this->resolveConsoleSchedule();
        });
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    public function commands()
    {
        $this->load($this->app->path('Console/Commands'));
    }
}
