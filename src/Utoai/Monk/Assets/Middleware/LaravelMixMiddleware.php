<?php

namespace Utoai\Monk\Assets\Middleware;

use Illuminate\Support\Str;

class LaravelMixMiddleware
{
    /**
     * 处理清单配置。
     *
     * @param  array  $config
     * @return array
     */
    public function handle($config)
    {
        if ($url = $this->getMixHotUri($config['path'])) {
            $config['url'] = $url;
        }

        return $config;
    }

    /**
     * 获取 Mix 热模块替换服务器的 URI。
     *
     * @link https://laravel-mix.com/docs/hot-module-replacement
     */
    protected function getMixHotUri(string $path): ?string
    {
        if (! file_exists($hot = "{$path}/hot")) {
            return null;
        }

        $url = rtrim(rtrim(file_get_contents($hot)), '/');

        return Str::after($url, ':');
    }
}