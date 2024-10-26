<?php

namespace Utoai\Monk\Assets\Contracts;

interface Manifest
{
    /**
     * 从清单获取资产对象
     *
     * @param  string  $key
     */
    public function asset($key): Asset;

    /**
     * 从清单获取资产包
     *
     * @param  string  $key
     */
    public function bundle($key): Bundle;
}
