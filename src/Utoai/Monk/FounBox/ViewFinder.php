<?php

namespace Utoai\Monk\FounBox;

use Illuminate\Support\Collection;
use Utoai\Monk\Filesystem\Filesystem;
use Utoai\Monk\View\FileViewFinder;

class ViewFinder
{
    /**
     * FileViewFinder 实例。
     *
     * @var FileViewFinder
     */
    protected $finder;

    /**
     * The Filesystem instance.
     *
     * @var Filesystem
     */
    protected $files;

    /**
     * 视图所在的主题或插件的基本路径。
     *
     * @var string
     */
    protected $path;

    /**
     * 创建新的 ViewFinder 实例。
     *
     * @param  string  $path
     * @return void
     */
    public function __construct(FileViewFinder $finder, Filesystem $files, $path = '')
    {
        $this->finder = $finder;
        $this->files = $files;
        $this->path = realpath($path ?: get_theme_file_path());
    }

    /**
     * 找到可用的视图文件。
     *
     * @param  mixed  $file
     * @return array
     */
    public function locate($file)
    {
        if (is_array($file)) {
            return array_merge(...array_map([$this, 'locate'], $file));
        }

        return $this->getRelativeViewPaths()
            ->flatMap(
                fn($viewPath) => collect($this->finder->getPossibleViewFilesFromPath($file))
                    ->merge([$file])
                    ->map(fn($file) => "{$viewPath}/{$file}")
            )
            ->unique()
            ->map(fn($file) => trim($file, '\\/'))
            ->toArray();
    }

    /**
     * 返回 FileViewFinder 实例。
     *
     * @return FileViewFinder
     */
    public function getFinder()
    {
        return $this->finder;
    }

    /**
     * 返回 Filesystem 实例。
     *
     * @return Filesystem
     */
    public function getFilesystem()
    {
        return $this->files;
    }

    /**
     * 获取相对于基本路径的视图路径列表
     *
     * @return Collection
     */
    protected function getRelativeViewPaths()
    {
        return collect($this->finder->getPaths())
            ->map(fn($viewsPath) => $this->files->getRelativePath("{$this->path}/", $viewsPath));
    }
}
