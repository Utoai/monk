<?php

namespace Utoai\Monk;

use Illuminate\Support\ServiceProvider as ServiceProviderBase;

final class ServiceProvider extends ServiceProviderBase
{
    /**
     * 获取 Monk 应用程序的默认提供程序。
     *
     * @return \Utoai\Monk\DefaultProviders
     */
    public static function defaultProviders()
    {
        return new DefaultProviders;
    }
}
