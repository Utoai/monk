<?php

namespace Utoai\Monk\Filesystem;

use Illuminate\Filesystem\FilesystemServiceProvider as FilesystemServiceProviderBase;

class FilesystemServiceProvider extends FilesystemServiceProviderBase
{
    /**
     * Register the Filesystem natively inside of the provider.
     *
     * @return void
     */
    protected function registerNativeFilesystem()
    {
        $this->app->singleton('files', fn () => new Filesystem);

        $this->app->alias('files', \Utoai\Monk\Filesystem\Filesystem::class);
    }
}
