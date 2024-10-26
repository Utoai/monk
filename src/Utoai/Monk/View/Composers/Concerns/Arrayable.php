<?php

namespace Utoai\Monk\View\Composers\Concerns;

use Illuminate\Support\Fluent;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;
use Throwable;

trait Arrayable
{
    /**
     * 将公共类方法映射到数组。
     *
     * @return array
     */
    public function toArray()
    {
        return collect((new ReflectionClass(static::class))->getMethods(ReflectionMethod::IS_PUBLIC))
            ->reject(fn($method) => $this->shouldIgnore($method->name) || $method->isStatic())
            ->mapWithKeys(function ($method) {
                try {
                    $data = $this->{$method->name}();

                    return [Str::snake($method->name) => is_array($data) ? new Fluent($data) : $data];
                } catch (Throwable) {
                    return [];
                }
            })
            ->filter()
            ->all();
    }

    /**
     * 判断是否应忽略某个方法。
     *
     * @param  string  $method
     * @return bool
     */
    protected function shouldIgnore($method)
    {
        return in_array($method, ['__construct', '__destruct', '__call', '__callStatic', '__get', '__set', '__isset', '__unset', '__sleep', '__wakeup', '__toString', '__invoke', '__set_state', '__clone', '__debugInfo']);
    }
}
