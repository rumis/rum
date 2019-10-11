<?php

namespace Rum\Log;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

/**
 * 
 */
class File implements LoggerInterface
{
    use LoggerTrait;

    static function init($logPath)
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
