<?php

namespace Utoai\Monk;

use Illuminate\Support\Collection;
use Illuminate\Support\DefaultProviders as DefaultProvidersBase;
use Illuminate\Support\Str;

class DefaultProviders extends DefaultProvidersBase
{
    /**
     * Monk 提供商。
     */
    protected array $acornProviders = [
        \Utoai\Monk\Assets\AssetsServiceProvider::class,
        \Utoai\Monk\Filesystem\FilesystemServiceProvider::class,
        \Utoai\Monk\Providers\AcornServiceProvider::class,
        \Utoai\Monk\Providers\QueueServiceProvider::class,
        \Utoai\Monk\View\ViewServiceProvider::class,
    ];

    /**
     * 其他框架提供程序。
     */
    protected array $additionalProviders = [
        \Illuminate\Foundation\Providers\ComposerServiceProvider::class,
        \Illuminate\Database\MigrationServiceProvider::class,
    ];

    /**
     * 不允许的提供商。
     */
    protected array $disallowedProviders = [
        \Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        \Illuminate\View\ViewServiceProvider::class,
    ];

    /**
     * 创建新的默认提供程序集合。
     *
     * @return void
     */
    public function __construct(?array $providers = null)
    {
        parent::__construct($providers);

        $this->providers = array_unique($this->providers);

        if ($providers) {
            return;
        }

        $this->providers = Collection::make($this->providers)
            ->merge($this->acornProviders)
            ->filter(fn ($provider) => ! Str::contains($provider, $this->disallowedProviders))
            ->merge($this->additionalProviders)
            ->unique()
            ->all();
    }
}
