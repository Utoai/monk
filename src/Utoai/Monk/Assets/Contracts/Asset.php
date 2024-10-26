<?php

namespace Utoai\Monk\Assets\Contracts;

interface Asset
{
    /**
     * 获取资源的远程 URI
     *
     * 示例: https://example.com/app/themes/sage/dist/styles/a1b2c3.min.css
     */
    public function uri(): string;

    /**
     * 获取资源的本地路径
     *
     * 示例: /srv/www/example.com/current/web/app/themes/sage/dist/styles/a1b2c3.min.css
     */
    public function path(): string;

    /**
     * 检查资源是否存在于文件系统中
     *
     * 示例:
     * ```php
     * $asset = new SomeAsset('/srv/www/example.com/current/web/app/themes/sage/dist/styles/a1b2c3.min.css');
     * if ($asset->exists()) {
     *     echo "资源存在";
     * } else {
     *     echo "资源不存在";
     * }
     * ```
     */
    public function exists(): bool;

    /**
     * 获取资源的内容
     *
     * 示例:
     * ```php
     * $asset = new SomeAsset('/srv/www/example.com/current/web/app/themes/sage/dist/styles/a1b2c3.min.css');
     * $content = $asset->contents();
     * echo $content; // 输出资源内容
     * ```
     *
     * @return mixed
     */
    public function contents();

    /**
     * 获取资源的相对路径。
     *
     * @param  string  $base_path  用于相对路径的基础路径。
     *
     * 示例:
     * ```php
     * $asset = new SomeAsset('/srv/www/example.com/current/web/app/themes/sage/dist/styles/a1b2c3.min.css');
     * $relativePath = $asset->relativePath('/srv/www/example.com/current/web/app');
     * echo $relativePath; // 输出: themes/sage/dist/styles/a1b2c3.min.css
     * ```
     */
    public function relativePath(string $base_path): string;

    /**
     * 获取资源的数据 URL。
     *
     * 示例:
     * ```php
     * $asset = new SomeAsset('/srv/www/example.com/current/web/app/themes/sage/dist/images/logo.png');
     * $dataUrl = $asset->dataUrl();
     * echo $dataUrl; // 输出: data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUA...
     * ```
     *
     * @return string
     */
    public function dataUrl();
}