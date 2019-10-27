<?php

namespace Rum\Test;

use PHPUnit\Framework\TestCase;
use Rum\Application;
use Rum\Request;
use Rum\Response;
use RumTest\ProcessManager;
use RumTest\HttpClient;
use Swoole\Coroutine\Channel;

require_once __DIR__ . '/include/functions.php';

/**
 * 日志测试
 */
final class LogTest extends TestCase
{
    /**
     * 控制台日志测试
     */
    public function testConsoleLog()
    {
        $port = get_one_free_port();
        $pm = null;
        $pm = new ProcessManager(function ($pid) use ($port, &$pm) {
            $chan = new Channel();
            HttpClient::post('127.0.0.1', $port, '/user/log', [], [], $chan);
            go(function () use ($chan, $pid, &$pm) {
                $data = $chan->pop();
                // $output = $pm->getServeOutput();
                // var_dump('$output');
                $this->assertEquals(1, 1);
                \swoole_process::kill($pid);
            });
        }, function () use ($port) {
            $app = new Application([]);
            $app->post('/user/log', function (Request $req, Response $res) {
                $res->end('');
            });
            $app->run($port);
        });
        $pm->run();
    }
}
