<?php

namespace Utoai\Monk\Sage\Concerns;

trait FiltersTemplates
{
    /**
     * 通过 Typecho 的归档类型确定视图名称
     *
     * @return string
     */
    public function filterTemplateInclude()
    {
        // 获取 Typecho 归档类型
        $archive = \Typecho\Widget::widget('\Widget\Archive');
        $type = $archive->parameter->type ?? 'index';
        

        // 返回视图名称
        return $this->getViewForType($type);
    }

    /**
     * 根据页面类型返回视图名称
     */
    protected function getViewForType($type)
    {
        return match ($type) {
            'index' => 'index',           // 视图名称映射到 resources/views/home/index.blade.php
            'post' => 'content.post',           // 视图名称映射到 resources/views/content/post.blade.php
            'page' => 'content.page',           // 视图名称映射到 resources/views/content/page.blade.php
            'category' => 'content.category',   // 视图名称映射到 resources/views/content/category.blade.php
            'tag' => 'content.tag',             // 视图名称映射到 resources/views/content/tag.blade.php
            'search' => 'content.search',       // 视图名称映射到 resources/views/content/search.blade.php
            'author' => 'content.author',       // 视图名称映射到 resources/views/content/author.blade.php
            'archive' => 'content.archive',     // 视图名称映射到 resources/views/content/archive.blade.php
            default => null,            // 默认视图
        };
    }
}
