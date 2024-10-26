<?php

namespace Utoai\Monk\Exceptions;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Foundation\Exceptions\Handler as FoundationHandler;
use Throwable;

class Handler extends FoundationHandler
{
    /**
     * 将异常呈现到 HTTP 响应中。
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $e)
    {
        if ($e instanceof Responsable) {
            return $e->toResponse($request);
        }

        $e = $this->prepareException($this->mapException($e));

        return $this->prepareResponse($request, $e);
    }

    /**
     * 获取用于日志记录的默认上下文变量。
     *
     * @return array
     */
    protected function context()
    {
        try {
            return array_filter([
                'userId' => "获取当前用户 id",
            ]);
        } catch (Throwable $e) {
            return [];
        }
    }
}
