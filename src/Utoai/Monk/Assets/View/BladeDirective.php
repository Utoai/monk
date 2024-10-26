<?php

namespace Utoai\Monk\Assets\View;

class BladeDirective
{
    /**
     * 调用 @asset 指令。
     *
     * @param  string  $expression
     * @return string
     */
    public function __invoke($expression)
    {
        return sprintf('<?= %s(%s); ?>', '\Utoai\asset', $expression);
    }
}
