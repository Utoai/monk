<?php

namespace Utoai\Monk\View;

use Illuminate\Support\Fluent;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Utoai\Monk\View\Composers\Concerns\Extractable;

abstract class Composer
{
    use Extractable;

    /**
     * 该作曲家提供的视图列表。
     *
     * @var string[]
     */
    protected static $views;

    /**
     * 当前视图实例。
     *
     * @var \Illuminate\View\View
     */
    protected $view;

    /**
     * 当前视图数据。
     *
     * @var \Illuminate\Support\Fluent
     */
    protected $data;

    /**
     * 不应该公开的属性/方法。
     *
     * @var array
     */
    protected $except = [];

    /**
     * 不应公开的默认属性/方法。
     *
     * @var array
     */
    protected $defaultExcept = [
        'cache',
        'compose',
        'override',
        'toArray',
        'views',
        'with',
    ];

    /**
     * 该作曲家提供的视图列表。
     *
     * @return string|string[]
     */
    public static function views()
    {
        if (static::$views) {
            return static::$views;
        }

        $view = array_slice(explode('\\', static::class), 3);
        $view = array_map([Str::class, 'snake'], $view, array_fill(0, count($view), '-'));

        return implode('/', $view);
    }

    /**
     * 在渲染之前组合视图。
     *
     * @return void
     */
    public function compose(View $view)
    {
        $this->view = $view;
        $this->data = new Fluent($view->getData());

        $view->with($this->merge());
    }

    /**
     * 渲染前要传递给视图的合并数据。
     *
     * @return array
     */
    protected function merge()
    {
        [$with, $override] = [$this->with(), $this->override()];

        if (! $with && ! $override) {
            return array_merge(
                $this->extractPublicProperties(),
                $this->extractPublicMethods(),
                $this->view->getData()
            );
        }

        return array_merge(
            $with,
            $this->view->getData(),
            $override
        );
    }

    /**
     * 渲染之前传递给视图的数据。
     *
     * @return array
     */
    protected function with()
    {
        return [];
    }

    /**
     * 渲染之前传递给视图的覆盖数据。
     *
     * @return array
     */
    protected function override()
    {
        return [];
    }

    /**
     * 确定是否应忽略给定的属性/方法。
     *
     * @param  string  $name
     * @return bool
     */
    protected function shouldIgnore($name)
    {
        return str_starts_with($name, '__') ||
            in_array($name, $this->ignoredMethods());
    }

    /**
     * 获取应该被忽略的方法。
     *
     * @return array
     */
    protected function ignoredMethods()
    {
        return array_merge($this->defaultExcept, $this->except);
    }
}
