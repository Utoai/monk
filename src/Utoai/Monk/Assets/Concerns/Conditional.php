<?php

namespace Utoai\Monk\Assets\Concerns;

trait Conditional
{
    /**
     * 有条件地加载资产。
     *
     * @var bool
     */
    protected $conditional = true;

    /**
     * 设置条件加载。
     *
     * @param  bool|callable  $conditional
     * @return $this
     */
    public function when($conditional, ...$args)
    {
        $this->conditional = is_callable($conditional)
            ? call_user_func($conditional, $args)
            : $conditional;

        return $this;
    }
}
