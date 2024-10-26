<?php

namespace Utoai\Monk\View\Composers\Concerns;

use Closure;
use Illuminate\View\InvokableComponentVariable;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

/**
 * 从 Composer 中提取公共属性以在视图中可用。
 *
 * @copyright Taylor Otwell
 *
 * @link https://github.com/illuminate/view/blob/v10.35.0/Component.php#L220-L342
 */
trait Extractable
{
    /**
     * 公共属性名称的缓存，以类为键。
     *
     * @var array
     */
    protected static $propertyCache = [];

    /**
     * 公共方法名称的缓存，以类为键。
     *
     * @var array
     */
    protected static $methodCache = [];

    /**
     * 提取类的公共属性。
     *
     * @return array
     */
    protected function extractPublicProperties()
    {
        $class = get_class($this);

        if (! isset(static::$propertyCache[$class])) {
            $reflection = new ReflectionClass($this);

            static::$propertyCache[$class] = collect($reflection->getProperties(ReflectionProperty::IS_PUBLIC))
                ->reject(fn (ReflectionProperty $property) => $property->isStatic() || $this->shouldIgnore($property->getName()))
                ->map(fn (ReflectionProperty $property) => $property->getName())
                ->all();
        }

        $values = [];

        foreach (static::$propertyCache[$class] as $property) {
            $values[$property] = $this->{$property};
        }

        return $values;
    }

    /**
     * 提取类的公共方法。
     *
     * @return array
     */
    protected function extractPublicMethods()
    {
        $class = get_class($this);

        if (! isset(static::$methodCache[$class])) {
            $reflection = new ReflectionClass($this);

            static::$methodCache[$class] = collect($reflection->getMethods(ReflectionMethod::IS_PUBLIC))
                ->reject(fn (ReflectionMethod $method) => $this->shouldIgnore($method->getName()))
                ->map(fn (ReflectionMethod $method) => $method->getName());
        }

        $values = [];

        foreach (static::$methodCache[$class] as $method) {
            $values[$method] = $this->createVariableFromMethod(new ReflectionMethod($this, $method));
        }

        return $values;
    }

    /**
     * 从给定方法创建一个可调用变量。
     *
     * @return mixed
     */
    protected function createVariableFromMethod(ReflectionMethod $method)
    {
        return $method->getNumberOfParameters() === 0
            ? $this->createInvokableVariable($method->getName())
            : Closure::fromCallable([$this, $method->getName()]);
    }

    /**
     * 为给定的类方法创建一个可调用的 toStringable 变量。
     *
     * @return \Illuminate\View\InvokableComponentVariable
     */
    protected function createInvokableVariable(string $method)
    {
        return new InvokableComponentVariable(fn () => $this->{$method}());
    }
}
