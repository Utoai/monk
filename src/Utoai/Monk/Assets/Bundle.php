<?php

namespace Utoai\Monk\Assets;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Utoai\Monk\Assets\Concerns\Conditional;
use Utoai\Monk\Assets\Concerns\Enqueuable;
use Utoai\Monk\Assets\Contracts\Bundle as BundleContract;

class Bundle implements BundleContract
{
    use Conditional, Enqueuable;

    /**
     * 捆绑包ID。
     *
     * @var string
     */
    protected $id;

    /**
     * 捆绑路径。
     *
     * @var string
     */
    protected $path;

    /**
     * 捆绑包URL。
     *
     * @var string
     */
    protected $uri;

    /**
     * 捆绑包运行时。
     *
     * @var string|null
     */
    protected $runtime;

    /**
     * 捆绑内容。
     *
     * @var array
     */
    protected $bundle;

    /**
     * 捆绑包运行时。
     *
     * @var array
     */
    protected static $runtimes = [];

    /**
     * 创建一个新捆绑包。
     */
    public function __construct(string $id, array $bundle, string $path, string $uri = '/')
    {
        $this->id = $id;
        $this->path = $path;
        $this->uri = $uri;
        $this->bundle = $bundle + ['js' => [], 'mjs' => [], 'css' => []];
        $this->setRuntime();
    }

    /**
     * 获取捆绑包中的CSS文件。
     *
     * （可选）传递一个函数以在每个CSS文件上执行。
     *
     * @return Collection|$this
     */
    public function css(?callable $callable = null)
    {
        $styles = $this->conditional ? $this->bundle['css'] : [];

        if (! $callable) {
            return collect($styles);
        }

        collect($styles)
            ->each(function ($src, $handle) use ($callable) {
                $callable("{$this->id}/{$handle}", $this->getUrl($src));
            });

        return $this;
    }

    /**
     * 获取捆绑包中的JS文件。
     *
     * 可选地传递一个函数以在每个JS文件上执行。
     *
     * @return Collection|$this
     */
    public function js(?callable $callable = null)
    {
        $scripts = $this->conditional ? array_merge($this->bundle['js'], $this->bundle['mjs']) : [];

        if (! $callable) {
            return collect($scripts);
        }

        collect($scripts)
            ->reject('runtime')
            ->each(function ($src, $handle) use ($callable) {
                $callable("{$this->id}/{$handle}", $this->getUrl($src), $this->dependencies());
            });

        return $this;
    }

    /**
     * 获取捆绑包依赖项。
     *
     * @return array
     */
    public function dependencies()
    {
        return $this->bundle['dependencies'] ?? [];
    }

    /**
     * 获取捆绑包运行时。
     *
     * @return string|null
     */
    public function runtime()
    {
        return $this->runtime;
    }

    /**
     * 获取捆绑包运行时内容。
     *
     * @return string|null
     */
    public function runtimeSource()
    {
        if (($runtime = $this->runtime()) === null) {
            return null;
        }

        if ($sauce = self::$runtimes[$runtime] ?? null) {
            return $sauce;
        }

        return self::$runtimes[$runtime] = file_get_contents("{$this->path}/{$runtime}");
    }

    /**
     * 获取捆绑包URL。
     *
     * @return string
     */
    protected function getUrl(string $path)
    {
        if (parse_url($path, PHP_URL_HOST)) {
            return $path;
        }

        $path = ltrim($path, '/');
        $uri = rtrim($this->uri, '/');

        return "{$uri}/{$path}";
    }

    /**
     * 设置捆绑包运行时。
     *
     * @return void
     */
    protected function setRuntime()
    {
        if (Arr::isAssoc($this->bundle['js'])) {
            $this->runtime = $this->bundle['js']['runtime']
                ?? $this->bundle['js']["runtime~{$this->id}"]
                ?? null;

            unset($this->bundle['js']['runtime'], $this->bundle['js']["runtime~{$this->id}"]);

            return;
        }

        $this->runtime = $this->getBundleRuntime() ?? $this->getBundleRuntime('mjs');
    }

    /**
     * 在捆绑包中卸载运行时。
     *
     * @return string|null
     */
    protected function getBundleRuntime(string $type = 'js')
    {
        if (! $this->bundle[$type]) {
            return null;
        }

        foreach ($this->bundle[$type] as $key => $value) {
            if (! str_contains($value, 'runtime')) {
                continue;
            }

            unset($this->bundle[$type][$key]);

            return $value;
        }

        return null;
    }
}
