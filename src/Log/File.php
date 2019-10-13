<?php

namespace Rum\Log;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Swoole\Coroutine\Channel;

/**
 * 本地文件日志
 * 日志内容通过Channel发送，日志记录协程dump
 * 此对象的初始化一定要放在Server EventLoop创建之后
 */
class File implements LoggerInterface
{
    use LoggerTrait;

    var $logPath;
    var $chan;

    /**
     * 初始化，职责类似构造
     */
    static function init($logPath)
    {
        return new File($logPath);
    }
    /**
     * 本地文件日志
     */
    public function __construct($logPath)
    {
        if (file_exists($logPath) && !is_dir($logPath)) {
            echo "log path：{$logPath} must be a directory！";
            exit();
        }
        if (!file_exists($logPath)) {
            try {
                mkdir($logPath, 0777, true);
            } catch (\Throwable $th) {
                echo $th->getMessage();
                exit();
            }
        }
        $this->logPath = $logPath . '/' . posix_getpid() . '.log';
        $this->chan = new Channel(1);
        go(function () {
            while (true) {
                $msg = $this->chan->pop();
                file_put_contents($this->logPath, $msg, FILE_APPEND);
            }
        });
    }

    /**
     * log
     */
    public function log($level, $message, array $context = array())
    {
        $this->chan->push('Log.' . $level . ':' . Util::interpolate($message, $context) . PHP_EOL);
    }
}
