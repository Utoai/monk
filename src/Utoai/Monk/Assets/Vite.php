<?php

namespace Utoai\Monk\Assets;

use Illuminate\Foundation\Vite as FoundationVite;

class Vite extends FoundationVite
{
    /**
     * 为应用程序生成资产路径。
     *
     * @param  string  $path
     * @param  bool|null  $secure
     * @return string
     */
    protected function assetPath($path, $secure = null)
    {
        return \Utoai\asset($path)->uri();
    }
}
