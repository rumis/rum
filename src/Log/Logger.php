<?php

namespace Rum\Log;

use Psr\Log\LoggerInterface;
use Rum\Request;
use Rum\Response;

/**
 * Logger
 */
class Logger
{
    static $logger = null;

    /**
     * Sets a logger instance on the object.
     *
     * @param LoggerInterface $logger
     * @return void
     */
    public static function setLogger(LoggerInterface $logger)
    {
        self::$logger = $logger;
    }

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function emergency($message, array $context = array())
    {
        if (empty(self::$logger)) {
            return;
        }
        self::$logger->emergency($message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function alert($message, array $context = array())
    {
        if (empty(self::$logger)) {
            return;
        }
        self::$logger->alert($message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function critical($message, array $context = array())
    {
        if (empty(self::$logger)) {
            return;
        }
        self::$logger->critical($message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function error($message, array $context = array())
    {
        if (empty(self::$logger)) {
            return;
        }
        self::$logger->error($message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function warning($message, array $context = array())
    {
        if (empty(self::$logger)) {
            return;
        }
        self::$logger->warning($message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function notice($message, array $context = array())
    {
        if (empty(self::$logger)) {
            return;
        }
        self::$logger->notice($message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function info($message, array $context = array())
    {
        if (empty(self::$logger)) {
            return;
        }
        self::$logger->info($message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function debug($message, array $context = array())
    {
        if (empty(self::$logger)) {
            return;
        }
        self::$logger->debug($message, $context);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function log($level, $message, array $context = array())
    {
        if (empty(self::$logger)) {
            return;
        }
        self::$logger->log($message, $context);
    }

    /**
     * 日志中间件
     */
    public static function middle()
    {
        return function (Request $req, Response $res) {
            $msg = 'rum.{time}.{path}';
            Logger::info($msg, array(
                'time' => date('Y-m-d:H-i-s'),
                'path' => $req->path(),
            ));
        };
    }
}
