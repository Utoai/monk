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
            ->reject(fn ($method) => $this->shouldIgnore($method->name) || $method->isStatic())
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
}
