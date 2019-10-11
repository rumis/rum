<?php

namespace Rum\Log;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Swoole\Coroutine\Channel;

/**
 * 
 */
class File implements LoggerInterface
{
    use LoggerTrait;

    var $logPath;
    var $chan;

    static function init($logPath)
    {
        return new File($logPath);
    }

    public function __construct($logPath)
    {
        if (file_exists($logPath) && !is_dir($logPath)) {
            echo "日志目录：{$logPath} 必需为一个文件夹！";
            exit();
        }
        if (!file_exists($logPath)) {
            try {
                mkdir($logPath, '0777', true);
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
     * 记录
     */
    public function log($level, $message, array $context = array())
    {
        $this->chan->push('Log.' . $level . ':' . Util::interpolate($message, $context) . PHP_EOL);
    }
}
