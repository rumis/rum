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

    /**
     * 控制台日志输出
     */
    public function __construct()
    { }

    /**
     * 初始化
     * @return Console 控制台日志对象
     */
    static function init()
    {
        return new Console();
    }

    /**
     * 记录
     * @param number $level 日志级别
     * @param string $message 消息
     * @param array 参数
     * @return void
     */
    public function log($level, $message, array $context = array())
    {
        echo 'Log.' . $level . ':' . Util::interpolate($message, $context) . PHP_EOL;
    }
}
