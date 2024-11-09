<?php

namespace Utoai\Monk\FounBox;

use Illuminate\Contracts\Container\Container as ContainerContract;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Support\Str;
use Utoai\Monk\Filesystem\Filesystem;
use Utoai\Monk\View\FileViewFinder;

class FounBox
{
    use Concerns\FiltersTemplates;

    /**
     * The application implementation.
     *
     * @var ContainerContract
     */
    protected $app;

    /**
     * The ViewFinder instance.
     *
     * @var ViewFinder
     */
    protected $sageFinder;

    /**
     * The FileViewFinder instance.
     *
     * @var FileViewFinder
     */
    protected $fileFinder;

    /**
     * The View Factory instance.
     *
     * @var ViewFactory
     */
    protected $view;

    /**
     * The Filesystem instance.
     */
    protected Filesystem $files;

    /**
     * 创建一个新的founbox实例。
     */
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
        echo  '新的founbox实例<br /><br />';
    }

    /**
     * 获取要传递到 typecho 的过滤器
     *
     * @return array
     */
    public function filter($filter)
    {
        echo '获取要传递到 typecho 的过滤器';
        return method_exists($this, $filter) ?
            [$this, $filter] :
            [$this, 'filter' . Str::studly($filter)];
    }
}
