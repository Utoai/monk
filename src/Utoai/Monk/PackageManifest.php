<?php

namespace Utoai\Monk;

use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\PackageManifest as FoundationPackageManifest;

class PackageManifest extends FoundationPackageManifest
{
    /**
     * composer.json 路径。
     *
     * @var string[]
     */
    public $composerPaths;

    /**
     * 创建一个新的包清单实例。
     *
     * @param  string[]  $composerPaths
     * @param  string  $manifestPath
     * @return void
     */
    public function __construct(Filesystem $files, array $composerPaths, $manifestPath)
    {
        $this->files = $files;
        $this->composerPaths = $composerPaths;
        $this->manifestPath = $manifestPath;
    }

    /**
     * 根据其提供者获取包名称
     *
     * @param  string  $providerName
     * @return string
     *
     * @throws FileNotFoundException
     * @throws Exception
     */
    public function getPackage($providerName)
    {
        foreach ($this->getManifest() as $package => $configuration) {
            foreach ($configuration['providers'] ?? [] as $provider) {
                if ($provider !== $providerName) {
                    continue;
                }

                return $package;
            }
        }

        return '';
    }

    /**
     * 构建清单并将其写入磁盘。
     *
     * @return void
     */
    public function build()
    {
        $packages = array_reduce($this->composerPaths, function ($all, $composerPath) {
            $packages = [];

            $path = "{$composerPath}/vendor/composer/installed.json";

            if ($this->files->exists($path)) {
                $installed = json_decode($this->files->get($path), true);

                $packages = $installed['packages'] ?? $installed;
            }

            $packages[] = json_decode($this->files->get("{$composerPath}/composer.json"), true);

            $ignoreAll = in_array('*', $ignore = $this->packagesToIgnore());

            return collect($packages)->mapWithKeys(fn ($package) => [
                $this->format($package['name'] ?? basename($composerPath), dirname($path, 2)) => $package['extra']['monk'] ?? $package['extra']['laravel'] ?? [],
            ])
                ->each(function ($configuration) use (&$ignore) {
                    $ignore = array_merge($ignore, $configuration['dont-discover'] ?? []);
                })
                ->reject(fn ($configuration, $package) => $ignoreAll || in_array($package, $ignore))
                ->filter()
                ->merge($all)
                ->all();
        }, []);

        $this->write($packages);
    }


    /**
     * 格式化给定的包名称。
     *
     * @param  string  $package
     * @param  string  $vendorPath
     * @return string
     */
    protected function format($package, $vendorPath = null)
    {
        return str_replace($vendorPath . '/', '', $package);
    }

    /**
     * 获取所有应该被忽略的包名称。
     *
     * @return array
     */
    protected function packagesToIgnore()
    {
        return array_reduce($this->composerPaths, function ($ignore, $composerPath) {
            $path = "{$composerPath}/composer.json";

            if (! $this->files->exists($path)) {
                return $ignore;
            }

            $package = json_decode($this->files->get($path), true);

            return array_merge(
                $ignore,
                $package['extra']['laravel']['dont-discover'] ?? [],
                $package['extra']['monk']['dont-discover'] ?? []
            );
        }, []);
    }
}
