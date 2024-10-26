<?php

namespace Utoai\Monk\Bootstrap;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\Facade;
use Utoai\Monk\Application;
use Utoai\Monk\PackageManifest;

class RegisterFacades
{
    /**
     * 引导给定的应用程序。
     *
     * @return void
     */
    public function bootstrap(Application $app)
    {
        Facade::clearResolvedInstances();

        Facade::setFacadeApplication($app);

        AliasLoader::getInstance(array_merge(
            $app->make('config')->get('app.aliases', []),
            $app->make(PackageManifest::class)->aliases()
        ))->register();
    }
}
