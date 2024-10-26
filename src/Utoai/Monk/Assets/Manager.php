<?php

namespace Utoai\Monk\Assets;

use InvalidArgumentException;
use Utoai\Monk\Assets\Contracts\Manifest as ManifestContract;
use Utoai\Monk\Assets\Exceptions\ManifestNotFoundException;
use Utoai\Monk\Assets\Middleware\LaravelMixMiddleware;

/**
 * 管理资产清单
 *
 * @see \Illuminate\Support\Manager
 * @link https://github.com/illuminate/support/blob/8.x/Manager.php
 */
class Manager
{
    /**
     * 已解决的清单
     *
     * @var ManifestContract[]
     */
    protected $manifests;

    /**
     * 资产配置
     *
     * @var array
     */
    protected $config;

    /**
     * 清单中间件。
     *
     * @var string[]
     */
    protected $middleware = [
        LaravelMixMiddleware::class,
    ];

    /**
     * 初始化AssetManager实例。
     *
     * @param  Container  $container
     */
    public function __construct($config = [])
    {
        $this->config = $config;
    }

    /**
     * 注册给定的清单
     *
     * @param  Manifest  $manifest
     * @return static
     */
    public function register(string $name, ManifestContract $manifest): self
    {
        $this->manifests[$name] = $manifest;

        return $this;
    }

    /**
     * 获取清单
     */
    public function manifest(string $name, ?array $config = null): ManifestContract
    {
        $manifest = $this->manifests[$name] ?? $this->resolve($name, $config);

        return $this->manifests[$name] = $manifest;
    }

    /**
     * 解析给定的清单。
     *
     *
     * @throws InvalidArgumentException
     */
    protected function resolve(string $name, ?array $config): ManifestContract
    {
        $config = $config ?? $this->getConfig($name);

        if (isset($config['handler'])) {
            return new $config['handler']($config);
        }

        $config = $this->pipeline($config);

        $path = $config['path'];
        $url = $config['url'];
        $assets = isset($config['assets']) ? $this->getJsonManifest($config['assets']) : [];
        $bundles = isset($config['bundles']) ? $this->getJsonManifest($config['bundles']) : [];

        return new Manifest($path, $url, $assets, $bundles);
    }

    /**
     * 清单配置管道。
     */
    protected function pipeline(array $config): array
    {
        return array_reduce($this->middleware, function (array $config, $middleware): array {
            if (is_string($middleware) && class_exists($middleware)) {
                $middleware = new $middleware();
            }

            return is_callable($middleware) ? $middleware($config) : $middleware->handle($config);
        }, $config);
    }

    /**
     * 从本地文件系统打开SON清单文件
     *
     * @param  string  $jsonManifest  .json文件的路径
     */
    protected function getJsonManifest(string $jsonManifest): array
    {
        if (! file_exists($jsonManifest)) {
            throw new ManifestNotFoundException("资产清单 [{$jsonManifest}] 找不到.");
        }

        return json_decode(file_get_contents($jsonManifest), true) ?? [];
    }

    /**
     * 获取资产清单配置。
     */
    protected function getConfig(string $name): array
    {
        return $this->config['manifests'][$name];
    }
}
