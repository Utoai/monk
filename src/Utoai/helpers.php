<?php

namespace Utoai;

use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Utoai\Monk\Assets\Contracts\Asset;
use Utoai\Monk\Bootloader;



/**
 * 从 manifest 获取资产
 */
// function asset(string $asset, ?string $manifest = null): Asset
// {
//     if (! $manifest) {
//         return \app('assets.manifest')->asset($asset);
//     }

//     return \app('assets')->manifest($manifest)->asset($asset);
// }

/**
 * 实例化引导加载程序。
 */
function bootloader(?ApplicationContract $app = null): Bootloader
{
    $bootloader = Bootloader::getInstance($app);
    return $bootloader;
}


/**
 * 将单个回调绑定到多个过滤器
 *
 * @param  iterable $filters  List of filters
 * @param  callable $callback
 * @param  integer  $priority
 * @param  integer  $args
 * @return void
 */
// function add_filters(iterable $filters, $callback, $priority = 10, $args = 2)
// {
//     $count = count($filters);
//     // var_dump($filters, $callback, $priority, $args); // 输出所有参数
//     array_map(
//         '\add_filter',
//         (array) $filters,
//         array_fill(0, $count, $callback),
//         array_fill(0, $count, $priority),
//         array_fill(0, $count, $args)
//     );
// }

/**
 * 获取给定视图或文件的评估视图内容。
 *
 * @param  string|null  $view
 * @param  \Illuminate\Contracts\Support\Arrayable|array  $data
 * @param  array  $mergeData
 * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
 *
 */
function view($view = null, $data = [], $mergeData = [])
{
    $factory = \app(ViewFactory::class);

    if (func_num_args() === 0) {
        return $factory;
    }

    return $factory->exists($view)
        ? $factory->make($view, $data, $mergeData)
        : $factory->file($view, $data, $mergeData);
}

/**
 * @deprecated
 */
function app(...$args)
{
    return \app(...$args);
}

/**
 * @deprecated
 */
function app_path(...$args)
{
    return \app_path(...$args);
}

/**
 * @deprecated
 */
function base_path(...$args)
{
    return \base_path(...$args);
}

/**
 * @deprecated
 */
function bcrypt(...$args)
{
    return \bcrypt(...$args);
}

/**
 * @deprecated
 */
function broadcast(...$args)
{
    return \broadcast(...$args);
}

/**
 * @deprecated
 */
function cache(...$args)
{
    return \cache(...$args);
}

/**
 * @deprecated
 */
function config(...$args)
{
    return \config(...$args);
}

/**
 * @deprecated
 */
function config_path(...$args)
{
    return \config_path(...$args);
}

/**
 * @deprecated
 */
function database_path(...$args)
{
    return \database_path(...$args);
}

/**
 * @deprecated
 */
function decrypt(...$args)
{
    return \decrypt(...$args);
}

/**
 * @deprecated
 */
function encrypt(...$args)
{
    return \encrypt(...$args);
}

/**
 * @deprecated
 */
function info(...$args)
{
    return \info(...$args);
}

/**
 * @deprecated
 */
function logger(...$args)
{
    return \logger(...$args);
}

/**
 * @deprecated
 */
function logs(...$args)
{
    return \logs(...$args);
}

/**
 * @deprecated
 */
function now(...$args)
{
    return \now(...$args);
}

/**
 * @deprecated
 */
function public_path(...$args)
{
    return \public_path(...$args);
}

/**
 * @deprecated
 */
function report(...$args)
{
    return \report(...$args);
}

/**
 * @deprecated
 */
function rescue(...$args)
{
    return \rescue(...$args);
}

/**
 * @deprecated
 */
function resolve(...$args)
{
    return \resolve(...$args);
}

/**
 * @deprecated
 */
function resource_path(...$args)
{
    return \resource_path(...$args);
}

/**
 * @deprecated
 */
function storage_path(...$args)
{
    return \storage_path(...$args);
}

/**
 * @deprecated
 */
function today(...$args)
{
    return \today(...$args);
}







//-------------------------------
