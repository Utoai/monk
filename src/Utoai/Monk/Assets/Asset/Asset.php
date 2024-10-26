<?php

namespace Utoai\Monk\Assets\Asset;

use Utoai\Monk\Assets\Contracts\Asset as AssetContract;
use Utoai\Monk\Filesystem\Filesystem;
use SplFileInfo;

class Asset implements AssetContract
{
    /**
     * 资产的本地路径。
     *
     * @var string
     */
    protected $path;

    /**
     * 资产的远程 URI。
     *
     * @var string
     */
    protected $uri;

    /**
     * 资产的 MIME 内容类型。
     *
     * @var string
     */
    protected $type;

    /**
     * 资产的 base64 编码内容。
     *
     * @var string
     */
    protected $base64;

    /**
     * 资产的数据 URL。
     *
     * @var string
     */
    protected $dataUrl;

    /**
     * 从清单中获取资产。
     *
     * @param  string  $path  本地路径
     * @param  string  $uri  远程 URI
     */
    public function __construct(string $path, string $uri)
    {
        $this->path = $path;
        $this->uri = $uri;
    }

    /** {@inheritdoc} */
    public function uri(): string
    {
        return $this->uri;
    }

    /** {@inheritdoc} */
    public function path(): string
    {
        return $this->path;
    }

    /** {@inheritdoc} */
    public function exists(): bool
    {
        return file_exists($this->path());
    }

    /** {@inheritdoc} */
    public function contents(): string
    {
        if (! $this->exists()) {
            return '';
        }

        return file_get_contents($this->path());
    }

    /**
     * 获取资产的相对路径。
     *
     * @param  string  $basePath  用于相对路径的基本路径
     * @return string
     */
    public function relativePath(string $basePath): string
    {
        $basePath = rtrim($basePath, '/\\').'/';

        return (new Filesystem())->getRelativePath($basePath, $this->path());
    }

    /**
     * 获取资产的 base64 编码内容。
     *
     * @return string
     */
    public function base64()
    {
        if ($this->base64) {
            return $this->base64;
        }

        return $this->base64 = base64_encode($this->contents());
    }

    /**
     * 获取资产的数据 URL。
     *
     * @param  string  $mediatype  MIME 内容类型
     * @return string
     */
    public function dataUrl(?string $mediatype = null): string
    {
        if ($this->dataUrl) {
            return $this->dataUrl;
        }

        if (! $mediatype) {
            $mediatype = $this->contentType();
        }

        return $this->dataUrl = "data:{$mediatype};base64,{$this->base64()}";
    }

    /**
     * 获取资产的数据 URI。
     *
     * @param  string  $mediatype  MIME 内容类型
     * @return string
     */
    public function dataUri(?string $mediatype = null): string
    {
        return $this->dataUrl($mediatype);
    }

    /**
     * 获取 MIME 内容类型。
     *
     * @return string|false
     */
    public function contentType()
    {
        if ($this->type) {
            return $this->type;
        }

        return $this->type = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $this->path());
    }

    /**
     * 获取 MIME 内容类型。
     *
     * @return string|false
     */
    public function mimeType()
    {
        return $this->contentType();
    }

    /**
     * 获取资产的 SplFileInfo 实例。
     *
     * @return SplFileInfo
     */
    public function file()
    {
        return new SplFileInfo($this->path());
    }

    /** {@inheritdoc} */
    public function __toString()
    {
        return $this->uri();
    }
}