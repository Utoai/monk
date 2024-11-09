<?php

namespace Utoai\Monk\Sage;

use Illuminate\Contracts\Container\Container as ContainerContract;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Support\Str;
use Utoai\Monk\Filesystem\Filesystem;
use Utoai\Monk\View\FileViewFinder;

class Sage
{
    use Concerns\FiltersTemplates;

    protected $app;
    protected $sageFinder;
    protected $fileFinder;
    protected $view;
    protected Filesystem $files;

    public function __construct(
        Filesystem $files,
        ViewFinder $sageFinder,
        FileViewFinder $fileFinder,
        ViewFactory $view,
        ContainerContract $app
    ) {
        $this->app = $app;
        $this->files = $files;
        $this->fileFinder = $fileFinder;
        $this->sageFinder = $sageFinder;
        $this->view = $view;
    }

    /**
     * 获取当前模板视图
     */
    public function resolveTemplate()
    {
        return $this->filterTemplateInclude();
    }

    /**
     * 获取要传递到视图的数据
     */
    public function getViewData()
    {
        $archive = \Typecho\Widget::widget('\Widget\Archive');
        return [
            'siteName' => config('app.name', 'My Site'),
            'archive' => $archive,
        ];
    }
}
