<?php

namespace Utoai\Monk\Bootstrap;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Bootstrap\HandleExceptions as FoundationHandleExceptionsBootstrapper;
use Throwable;
use Typecho\Common;  // 引入 Typecho 的 Common 类


class HandleExceptions extends FoundationHandleExceptionsBootstrapper
{
    /**
     * 启动错误和异常处理程序。
     *
     * @param  \Utoai\Monk\Application  $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        self::$reservedMemory = str_repeat('x', 32768);

        static::$app = $app;

        // 使用 Typecho 调试模式检查
        if (!$this->isDebug() || $this->hasHandler()) {
            return;
        }

        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);
    }

    /**
     * 检查是否有 Typecho 或其他自定义的致命错误处理程序。
     *
     * @return bool
     */
    protected function hasHandler()
    {
        // 检查是否已设置异常处理器或错误处理器
        return set_exception_handler(null) !== null || set_error_handler(null) !== null;
    }


    /**
     * 自定义的异常处理方法，用于接管 Typecho 的错误页面显示。
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function handleException(Throwable $exception)
    {
        // 检查是否为 Typecho 的数据库异常
        if ($exception instanceof \Typecho\Db\Exception) {
            Common::error($exception);  // 使用 Typecho 的 Common::error 处理数据库异常
        } else {
            // 使用 FoundationHandleExceptionsBootstrapper 的 renderHttpResponse 渲染其他异常
            $this->renderHttpResponse($exception);
        }
    }


    /**
     * 处理 PHP 错误：将错误转为 ErrorException 实例。
     *
     * @param  int  $level
     * @param  string  $message
     * @param  string  $file
     * @param  int  $line
     * @param  array  $context
     * @return void|false
     *
     * @throws \ErrorException
     */
    public function handleError($level, $message, $file = '', $line = 0, $context = [])
    {
        try {
            parent::handleError($level, $message, $file, $line, $context);
        } catch (Throwable $e) {
            throw $e;
        }
    }



    /**
     * 确定是否启用了应用程序调试。
     *
     * @return bool
     */
    protected function isDebug()
    {
        return static::$app->config->get('app.debug');
    }


    /**
     * 渲染异常为 HTTP 响应并发送。
     *
     * @return void
     */
    protected function renderHttpResponse(Throwable $e)
    {
        for ($i = 0; $i < ob_get_level(); $i++) {
            ob_end_clean();
        }

        parent::renderHttpResponse($e);
    }
}
