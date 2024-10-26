<?php

namespace Utoai\Monk\Assets\Asset;

class TextAsset extends Asset
{
    /**
     * 字符编码
     *
     * @var string
     */
    protected $charset;

    /**
     * 获取字符编码。
     *
     * 如果无法确定字符编码，则使用提供的默认值。
     *
     * @param  string  $fallback  如果无法确定字符编码，则使用此默认值
     * @return string
     */
    public function charset($fallback = 'UTF-8'): string
    {
        if ($this->charset) {
            return $this->charset;
        }

        if (preg_match('//u', $this->contents())) {
            return $this->charset = 'UTF-8';
        }

        if (function_exists('mb_detect_encoding')) {
            return $this->charset = mb_detect_encoding($this->contents()) ?: $fallback;
        }

        return $this->charset = $fallback;
    }

    /**
     * 获取资产的数据 URL。
     *
     * @param  string  $mediatype  MIME 内容类型
     * @param  string  $charset  字符编码
     * @param  string  $urlencode  需要进行百分比编码的字符列表
     * @return string
     */
    public function dataUrl(?string $mediatype = null, ?string $charset = null, string $urlencode = '%\'"'): string
    {
        if ($this->dataUrl) {
            return $this->dataUrl;
        }

        if (! $mediatype) {
            $mediatype = $this->contentType();
        }

        if (! strstr($mediatype, 'charset')) {
            $mediatype .= ';charset='.($charset ?: $this->charset());
        }

        $percents = [];

        foreach (preg_split('//u', $urlencode, -1, PREG_SPLIT_NO_EMPTY) as $char) {
            $percents[$char] = rawurlencode($char);
        }

        $data = strtr($this->contents(), $percents);

        return $this->dataUrl = "data:{$mediatype},{$data}";
    }

    /**
     * 获取资产的数据 URI。
     *
     * @param  string  $mediatype  MIME 内容类型
     * @param  string  $charset  字符编码
     * @param  string  $urlencode  需要进行百分比编码的字符列表
     * @return string
     */
    public function dataUri(?string $mediatype = null, ?string $charset = null, string $urlencode = '%\'"'): string
    {
        return $this->dataUrl($mediatype, $charset, $urlencode);
    }
}