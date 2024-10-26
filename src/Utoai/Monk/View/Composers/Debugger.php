<?php

namespace Utoai\Monk\View\Composers;

use Illuminate\View\View;
use Utoai\Monk\Application;

class Debugger
{
    public $debugLevel; // 添加这一行声明
    /**
     * 创建一个新的调试器实例。
     */
    public function __construct(Application $app)
    {
        $this->debugLevel = $app['config']['view.debug'];
    }

    /**
     * 在渲染之前组合视图。
     *
     * @param  View  $view
     * @return void
     */
    public function compose($view)
    {
        $name = $view->getName();

        if ($this->debugLevel === 'view') {
            dump($name);

            return;
        }

        $data = array_map(function ($value) {
            if (is_object($value)) {
                return get_class($value);
            }

            return $value;
        }, $view->getData());

        if ($this->debugLevel === 'data') {
            dump($data);

            return;
        }

        dump(['view' => $name, 'data' => $data]);
    }
}
