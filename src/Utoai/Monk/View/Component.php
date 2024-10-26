<?php

namespace Utoai\Monk\View;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component as ComponentBase;

use function Utoai\view;

abstract class Component extends ComponentBase
{
    /**
     * 获取给定视图的评估视图内容。
     *
     * @param  string|null  $view
     * @param  Arrayable|array  $data
     * @param  array  $mergeData
     * @return View|Factory
     */
    public function view($view = null, $data = [], $mergeData = [])
    {
        return view($view, $data, $mergeData);
    }
}
