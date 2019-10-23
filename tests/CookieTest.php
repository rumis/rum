<?php

use PHPUnit\Framework\TestCase;
use Rum\Application;
use Rum\Request;
use Rum\Response;
use RumTest\ProcessManager;
use RumTest\HttpClient;
use Swoole\Coroutine\Channel;


require_once __DIR__ . '/include/functions.php';

/**
 * Cookie测试
 */
final class CookieTest extends TestCase
{
    /**
     * Cookie测试
     */
    public function testCookie()
    {
        $port = get_one_free_port();
        $c_key = '8MLP_5753_saltkey';
        $c_val = 'RSU8HYED';

        $pm = new ProcessManager(function ($pid) use ($port, $c_key, $c_val) {
            $chan = new Channel();
            HttpClient::post('127.0.0.1', $port, '/user/one', [], [], $chan);
            go(function () use ($chan, $pid, $c_key, $c_val) {
                $data = $chan->pop();
                $this->assertEquals($data['cookie'][$c_key], $c_val);
                swoole_process::kill($pid);
            });
        }, function () use ($port, $c_key, $c_val) {
            $app = new Application([]);
            $app->post('/user/one', function (Request $req, Response $res) use ($c_key, $c_val) {
                $res->cookie($c_key, $c_val);
                $res->end('');
            });
            $app->run($port);
        });
        $pm->run();
    }
}
