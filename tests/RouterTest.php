<?php

use PHPUnit\Framework\TestCase;
use Rum\Application;
use Rum\Request;
use Rum\Response;
use RumTest\ProcessManager;
use Swoole\Coroutine\Channel;
use RumTest\HttpClient;


require_once __DIR__ . '/include/functions.php';

/**
 * 基础测试
 */
final class RouterTest extends TestCase
{
    /**
     * 测试后台直接返回JSON
     */
    public function testJSON()
    {
        $port = get_one_free_port();

        $pm = new ProcessManager(function ($pid) use ($port) {
            $chan = new Channel();
            HttpClient::post('127.0.0.1', $port, '/test/json', [], [], $chan);
            go(function () use ($chan, $pid) {
                $data = $chan->pop();
                $this->assertEquals($data['header']['content-type'], 'application/json');
                $b = json_decode($data['body'], true);
                $this->assertEquals($b['data']['name'], 'liumurong');
                swoole_process::kill($pid);
            });
        }, function () use ($port) {
            $app = new Application([]);
            $app->post('/test/json', function (Request $req, Response $res) {
                $res->json(['stat' => 1, 'msg' => 'ok', 'data' => [
                    'name' => 'liumurong'
                ]]);
            });
            $app->run($port);
        });
        $pm->run();
    }
}
