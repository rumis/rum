<?php

namespace Rum\Log;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

/**
 * 控制台日志输出
 */
class Console implements LoggerInterface
{
    use LoggerTrait;

    public function __construct()
    { }

    static function init()
    {
        return new Console();
    }

    /**
     * 记录
     */
    public function log($level, $message, array $context = array())
    {
        echo 'Log.' . $level . ':' . Util::interpolate($message, $context) . PHP_EOL;
    }
}
