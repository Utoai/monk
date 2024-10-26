<?php

namespace Utoai\Monk\Assets\Concerns;

use Illuminate\Support\Str;
use Utoai\Monk\Filesystem\Filesystem;

trait Enqueuable
{
    /**
     * 已解析的内联源。
     *
     * @var array
     */
    protected static $inlined = [];

    /**
     * 获取捆绑包中的 JS 文件。
     *
     * 可选地传递一个函数来对每个 JS 文件执行操作。
     *
     * @return Collection|$this
     */
    abstract public function js(?callable $callable = null);

    /**
     * 获取捆绑包中的 CSS 文件。
     *
     * 可选地传递一个函数来对每个 CSS 文件执行操作。
     *
     * @return Collection|$this
     */
    abstract public function css(?callable $callable = null);

    abstract public function runtime();

    abstract public function runtimeSource();

    /**
     * 在 WordPress 中将 CSS 文件排入队列。
     *
     * @return $this
     */
    public function enqueueCss(string $media = 'all', array $dependencies = [])
    {
        // $this->css(function ($handle, $src) use (&$dependencies, $media) {
        //     wp_enqueue_style($handle, $src, $dependencies, null, $media);
        //     $this->mergeDependencies($dependencies, [$handle]);
        // });

        return "在 WordPress 中将 CSS 文件排入队列。";
    }

    /**
     * 在 WordPress 中将 JS 文件排入队列。
     *
     * @return $this
     */
    public function enqueueJs(bool|array $args = true, array $dependencies = [])
    {
        $this->js(function ($handle, $src, $bundleDependencies) use (&$dependencies, $args) {
            $this->mergeDependencies($dependencies, $bundleDependencies);

            // wp_enqueue_script($handle, $src, $dependencies, null, $args);

            $this->inlineRuntime();

            $this->mergeDependencies($dependencies, [$handle]);
        });

        return $this;
    }

    /**
     * 在 WordPress 中将 JS 和 CSS 文件排入队列。
     *
     * @return $this
     */
    public function enqueue()
    {
        return $this->enqueueCss()->enqueueJs();
    }

    /**
     * 在 WordPress 中添加 CSS 文件作为编辑器样式。
     *
     * @return $this
     */
    public function editorStyles()
    {
        $relativePath = (new Filesystem)->getRelativePath(
            Str::finish(get_theme_file_path(), '/'),
            $this->path
        );

        $this->css(function ($handle, $src) use ($relativePath) {
            if (! Str::startsWith($src, $this->uri)) {
                // return add_editor_style($src);
            }

            $style = Str::of($src)
                ->after($this->uri)
                ->ltrim('/')
                ->start("{$relativePath}/")
                ->toString();

            // add_editor_style($style);
        });

        return $this;
    }

    /**
     * 在 WordPress 中取消排队 CSS 文件。
     *
     * @return $this
     */
    public function dequeueCss()
    {
        $this->css(function ($handle) {
            // wp_dequeue_style($handle);
        });

        return $this;
    }

    /**
     * 在 WordPress 中取消排队 JS 文件。
     *
     * @return $this
     */
    public function dequeueJs()
    {
        $this->js(function ($handle) {
            // wp_dequeue_script($handle);
        });

        return $this;
    }

    /**
     * 在 WordPress 中取消排队 JS 和 CSS 文件。
     *
     * @return $this
     */
    public function dequeue()
    {
        return $this->dequeueCss()->dequeueJs();
    }

    /**
     * 在 WordPress 中内联运行时脚本。
     *
     * @return $this
     */
    public function inlineRuntime()
    {
        if (! $runtime = $this->runtime()) {
            return $this;
        }

        if (isset(self::$inlined[$runtime])) {
            return $this;
        }

        if ($contents = $this->runtimeSource()) {
            $this->inline($contents, 'before');
        }

        self::$inlined[$runtime] = $contents;

        return $this;
    }

    /**
     * 在捆绑包加载前或后添加内联脚本。
     *
     * @param  string  $contents
     * @param  string  $position
     * @return $this
     */
    public function inline($contents, $position = 'after')
    {
        if (! $handles = array_keys($this->js()->keys()->toArray())) {
            return $this;
        }

        $handle = "{$this->id}/".(
            $position === 'after'
                ? array_pop($handles)
                : array_shift($handles)
        );

        // wp_add_inline_script($handle, $contents, $position);

        return $this;
    }

    /**
     * 添加捆绑包要使用的本地化数据。
     *
     * @param  string  $name
     * @param  array  $object
     * @return $this
     */
    public function localize($name, $object)
    {
        if (! $handles = $this->js()->keys()->toArray()) {
            return $this;
        }

        $handle = "{$this->id}/{$handles[0]}";
        // wp_localize_script($handle, $name, $object);

        return $this;
    }

    /**
     * 添加捆绑包要使用的脚本翻译。
     *
     * @param  string  $domain
     * @param  string  $path
     * @return $this
     */
    public function translate($domain = null, $path = null)
    {
        // $domain ??= wp_get_theme()->get('TextDomain');
        // $path ??= lang_path();

        // $this->js()->keys()->each(function ($handle) use ($domain, $path) {
        //     wp_set_script_translations("{$this->id}/{$handle}", $domain, $path);
        // });

        return "添加捆绑包要使用的脚本翻译";
    }

    /**
     * 合并两个或多个数组。
     *
     * @return void
     */
    protected function mergeDependencies(array &$dependencies, array ...$moreDependencies)
    {
        $dependencies = array_unique(array_merge($dependencies, ...$moreDependencies));
    }

    /**
     * 重置已内联的源。
     *
     * @internal
     *
     * @return void
     */
    public static function resetInlinedSources()
    {
        self::$inlined = [];
    }
}