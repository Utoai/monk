<?php

namespace Utoai\Monk\Filesystem;

use Illuminate\Filesystem\Filesystem as FilesystemBase;

class Filesystem extends FilesystemBase
{
    /**
     * 规范化文件路径分隔符
     *
     * @param  mixed  $path
     * @param  string  $separator
     * @return mixed
     */
    public function normalizePath($path, $separator = '/')
    {
        return preg_replace('#/+#', $separator, strtr($path, '\\', '/'));
    }

    /**
     * 在目录树中找到最近的文件。
     *
     * @param  string  $path
     * @param  string  $file
     * @return string|null
     */
    public function closest($path, $file)
    {
        $currentDirectory = $path;

        while ($this->isReadable($currentDirectory)) {
            if ($this->isFile($filePath = $currentDirectory . DIRECTORY_SEPARATOR . $file)) {
                return $filePath;
            }

            $parentDirectory = $this->dirname($currentDirectory);

            if (empty($parentDirectory) || $parentDirectory === $currentDirectory) {
                break;
            }

            $currentDirectory = $parentDirectory;
        }

        return null;
    }

    /**
     * 从指定基获取目标的相对路径
     *
     * @param  string  $basePath
     * @param  string  $targetPath
     * @return string
     *
     * @copyright Fabien Potencier
     * @license   MIT
     *
     * @link      https://github.com/symfony/routing/blob/v4.1.1/Generator/UrlGenerator.php#L280-L329
     */
    public function getRelativePath($basePath, $targetPath)
    {
        $basePath = $this->normalizePath($basePath);
        $targetPath = $this->normalizePath($targetPath);

        if ($basePath === $targetPath) {
            return '';
        }

        $sourceDirs = explode('/', ltrim($basePath, '/'));
        $targetDirs = explode('/', ltrim($targetPath, '/'));

        array_pop($sourceDirs);

        $targetFile = array_pop($targetDirs);

        foreach ($sourceDirs as $i => $dir) {
            if (isset($targetDirs[$i]) && $dir === $targetDirs[$i]) {
                unset($sourceDirs[$i], $targetDirs[$i]);
            } else {
                break;
            }
        }

        $targetDirs[] = $targetFile;
        $path = str_repeat('../', count($sourceDirs)) . implode('/', $targetDirs);

        return $path === '' || $path[0] === '/'
            || ($colonPos = strpos($path, ':')) !== false && ($colonPos < ($slashPos = strpos($path, '/'))
                || $slashPos === false)
            ? "./$path" : $path;
    }
}
