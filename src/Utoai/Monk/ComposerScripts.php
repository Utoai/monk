<?php

namespace Utoai\Monk;

use Illuminate\Foundation\ComposerScripts as FoundationComposerScripts;
use Utoai\Monk\Console\Console;
use Utoai\Monk\Filesystem\Filesystem;

class ComposerScripts extends FoundationComposerScripts
{
    /**
     * 清除缓存的 Laravel 引导文件。
     *
     * @return void
     */
    protected static function clearCompiled()
    {
        $console = new Console(new Filesystem, getcwd());

        $console->configClear();
        $console->clearCompiled();
    }
}
