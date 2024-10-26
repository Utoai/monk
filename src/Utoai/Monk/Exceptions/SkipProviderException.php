<?php

namespace Utoai\Monk\Exceptions;

use InvalidArgumentException;
use Throwable;

class SkipProviderException extends InvalidArgumentException
{
    /**
     * 创建新的例外。
     *
     * @return void
     */
    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null, string $package = '')
    {
        parent::__construct($message, $code, $previous);

        $this->package = $package;
    }

    /**
     * 提供商的包的名称。
     *
     * @var string
     */
    protected $package;

    /**
     * 设置提供程序包的名称。
     *
     * @return void
     */
    public function setPackage(string $package)
    {
        $this->package = $package;
    }

    /**
     * 获取提供程序的软件包。
     *
     * @return string
     */
    public function package()
    {
        return $this->package;
    }

    /**
     * 报告异常。
     *
     * @return array
     */
    public function context()
    {
        return [
            'package' => $this->package(),
        ];
    }
}
