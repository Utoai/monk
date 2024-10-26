<?php

namespace Utoai\Monk\Assets\Asset;

use Illuminate\Contracts\Filesystem\FileNotFoundException;

class PhpAsset extends Asset
{
    /**
     * 获取资产的返回值
     *
     * @return mixed
     */
    public function requireOnce()
    {
        $this->assertExists();

        return require_once $this->path();
    }

    /**
     * 获取资产的返回值
     *
     * @return mixed
     */
    public function require()
    {
        $this->assertExists();

        return require $this->path();
    }

    /**
     * 获取资产的返回值
     *
     * @return mixed
     */
    public function includeOnce()
    {
        $this->assertExists();

        return include_once $this->path();
    }

    /**
     * 获取资产的返回值
     *
     * @return mixed
     */
    public function include()
    {
        $this->assertExists();

        return include $this->path();
    }

    /**
     * 断言资产存在。
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function assertExists()
    {
        if (! $this->exists()) {
            throw new FileNotFoundException("资产 [{$this->path()}] 找不到.");
        }
    }
}