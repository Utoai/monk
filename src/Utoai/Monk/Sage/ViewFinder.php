<?php

namespace Utoai\Monk\Sage;

use Illuminate\Support\Collection;
use Utoai\Monk\Filesystem\Filesystem;
use Utoai\Monk\View\FileViewFinder;

class ViewFinder
{
    /**
     * The FileViewFinder instance.
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
     * Base path for theme or plugin in which views are located.
     *
     * @var string
     */
    protected $path;

    /**
     * Create new ViewFinder instance.
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
     * Locate available view files.
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
                fn ($viewPath) => collect($this->finder->getPossibleViewFilesFromPath($file))
                    ->merge([$file])
                    ->map(fn ($file) => "{$viewPath}/{$file}")
            )
            ->unique()
            ->map(fn ($file) => trim($file, '\\/'))
            ->toArray();
    }

    /**
     * Return the FileViewFinder instance.
     *
     * @return FileViewFinder
     */
    public function getFinder()
    {
        return $this->finder;
    }

    /**
     * Return the Filesystem instance.
     *
     * @return Filesystem
     */
    public function getFilesystem()
    {
        return $this->files;
    }

    /**
     * Get list of view paths relative to the base path
     *
     * @return Collection
     */
    protected function getRelativeViewPaths()
    {
        return collect($this->finder->getPaths())
            ->map(fn ($viewsPath) => $this->files->getRelativePath("{$this->path}/", $viewsPath));
    }
}
