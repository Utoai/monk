<?php

namespace Utoai\Monk\View;

use Illuminate\View\FileViewFinder as FileViewFinderBase;

class FileViewFinder extends FileViewFinderBase
{
    /**
     *获取视图文件的可能相对位置
     *
     * @param  string  $path  可能的视图文件的绝对或相对路径
     * @return string[]
     */
    public function getPossibleViewFilesFromPath($path)
    {
        $path = $this->getPossibleViewNameFromPath($path);

        return $this->getPossibleViewFiles($path);
    }

    /**
     * 根据路径获取可能的视图名称
     *
     * @param  string  $path  可能的视图文件的绝对或相对路径
     * @return string
     */
    public function getPossibleViewNameFromPath($file)
    {
        $view = $this->normalizePath($file);
        $paths = $this->normalizePath($this->paths);
        $hints = array_map([$this, 'normalizePath'], $this->hints);

        $view = $this->stripExtensions($view);
        $view = str_replace($paths, '', $view);

        foreach ($hints as $hintNamespace => $hintPaths) {
            $maybeView = str_replace($hintPaths, '', $view);

            if ($view === $maybeView) {
                continue;
            }

            $namespace = $hintNamespace;
            $view = $maybeView;

            break;
        }

        $view = ltrim($view, '/\\');
        $namespace = $namespace ?? null;

        if ($namespace) {
            $view = "{$namespace}::$view";
        }

        return $view;
    }

    /**
     * 从路径中删除已识别的扩展名
     *
     * @param  string  $file  查看文件的相对路径
     * @return string view name
     */
    protected function stripExtensions($path)
    {
        $extensions = implode('|', array_map('preg_quote', $this->getExtensions()));

        return preg_replace("/\.({$extensions})$/", '', $path);
    }

    /**
     * 标准化路径
     *
     * @param  string|string[]  $path
     * @param  string  $separator
     * @return string|string[]
     */
    protected function normalizePath($path, $separator = '/')
    {
        return preg_replace('#[\\/]+#', $separator, $path);
    }
}
