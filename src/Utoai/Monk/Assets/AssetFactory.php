<?php

namespace Utoai\Monk\Assets;

use Utoai\Monk\Assets\Asset\Asset;
use Utoai\Monk\Assets\Asset\JsonAsset;
use Utoai\Monk\Assets\Asset\PhpAsset;
use Utoai\Monk\Assets\Asset\SvgAsset;
use Utoai\Monk\Assets\Contracts\Asset as AssetContract;

class AssetFactory
{
    /**
     * 创建资产实例。
     *
     * @param  string  $path  Local path
     * @param  string  $uri  Remote URI
     * @param  string  $type  Asset type
     */
    public static function create(string $path, string $uri, ?string $type = null): AssetContract
    {
        if (! $type) {
            $type = pathinfo($path, PATHINFO_EXTENSION);
        }

        if (method_exists(self::class, $method = 'create'.ucfirst(strtolower($type)).'Asset')) {
            return self::{$method}($path, $uri);
        }

        return self::createAsset($path, $uri);
    }

    /**
     * 将资产转换为另一种资产类型。
     */
    public static function convert(AssetContract $asset, string $type): AssetContract
    {
        return self::create($asset->path(), $asset->uri(), $type);
    }

    /**
     * 创建资产实例。
     */
    protected static function createAsset(string $path, string $uri): Asset
    {
        return new Asset($path, $uri);
    }

    /**
     * 创建JsonAsset实例。
     */
    protected static function createJsonAsset(string $path, string $uri): JsonAsset
    {
        return new JsonAsset($path, $uri);
    }

    /**
     * 创建PhpAsset实例。
     */
    protected static function createPhpAsset(string $path, string $uri): PhpAsset
    {
        return new PhpAsset($path, $uri);
    }

    /**
     * 创建SvgAsset实例。
     */
    protected static function createSvgAsset(string $path, string $uri): SvgAsset
    {
        return new SvgAsset($path, $uri);
    }
}
