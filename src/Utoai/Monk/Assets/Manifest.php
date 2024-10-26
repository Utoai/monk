<?php

namespace Utoai\Monk\Assets;

use Illuminate\Support\Str;
use Utoai\Monk\Assets\Contracts\Asset as AssetContract;
use Utoai\Monk\Assets\Contracts\Bundle as BundleContract;
use Utoai\Monk\Assets\Contracts\Manifest as ManifestContract;
use Utoai\Monk\Assets\Exceptions\BundleNotFoundException;

class Manifest implements ManifestContract
{
    /**
     * 清单资产。
     *
     * @var array
     */
    protected $assets;

    /**
     * 清单捆绑包。
     *
     * @var array
     */
    protected $bundles;

    /**
     * 显式路径。
     *
     * @var string
     */
    protected $path;

    /**
     * 清单URI。
     *
     * @var string
     */
    protected $uri;

    /**
     * 创建新的清单实例。
     */
    public function __construct(string $path, string $uri, array $assets = [], ?array $bundles = null)
    {
        $this->path = $path;
        $this->uri = $uri;
        $this->bundles = $bundles;

        foreach ($assets as $original => $revved) {
            $this->assets[$this->normalizeRelativePath($original)] = $this->normalizeRelativePath($revved);
        }
    }

    /**
     * 获取指定资产。
     *
     * @param  string  $key
     */
    public function asset($key): AssetContract
    {
        $key = $this->normalizeRelativePath($key);
        $relativePath = $this->assets[$key] ?? $key;

        $path = Str::before("{$this->path}/{$relativePath}", '?');
        $uri = "{$this->uri}/{$relativePath}";

        return AssetFactory::create($path, $uri);
    }

    /**
     * 获取指定的捆绑包。
     *
     * @param  string  $key
     *
     * @throws \Utoai\Monk\Assets\Exceptions\BundleNotFoundException
     */
    public function bundle($key): BundleContract
    {
        if (! isset($this->bundles[$key])) {
            throw new BundleNotFoundException("Bundle [{$key}] not found in manifest.");
        }

        return new Bundle($key, $this->bundles[$key], $this->path, $this->uri);
    }

    /**
     * 规范化为正斜线并删除前斜线。
     */
    protected function normalizeRelativePath(string $path): string
    {
        $path = str_replace('\\', '/', $path);
        $path = preg_replace('%//+%', '/', $path);

        return ltrim($path, './');
    }
}
