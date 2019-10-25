<?php

use PHPUnit\Framework\TestCase;
use Rum\Application;
use Rum\Request;
use Rum\Response;
use RumTest\ProcessManager;
use Swoole\Coroutine\Channel;
use RumTest\HttpClient;
use RumTest\WaitGroup;

require_once __DIR__ . '/include/functions.php';

/**
 * 中间件测试
 */
final class MiddlewareTest extends TestCase
{
    /**
     *  路由组，中间件
     */
    public function testMiddleware()
    {
        $port = get_one_free_port();

        $pm = new ProcessManager(function ($pid) use ($port) {

            $wg =  new WaitGroup(); // 协调协程，用于一个方法中发送多个请求

            $chan1 = new Channel();
            HttpClient::post('127.0.0.1', $port, '/g/param/x1', [], [], $chan1);
            $wg->add();
            go(function () use ($chan1, $wg) {
                $data = $chan1->pop();
                $this->assertEquals($data['header']['content-type'], 'application/json');
                $b = json_decode($data['body'], true);
                $this->assertEquals($b['group_name'], 'liumurong');
                $this->assertEquals($b['comp_name'], 'xes');
                $wg->done();
            });

            $chan2 = new Channel();
            HttpClient::post('127.0.0.1', $port, '/abort/drop', [], [], $chan2);
            $wg->add();
            go(function () use ($chan2, $wg) {
                $data = $chan2->pop();
                $this->assertEquals($data['code'], 401);
                $this->assertEquals($data['body'], 'Unauthorized');
                $wg->done();
            });

            $chan3 = new Channel();
            HttpClient::post('127.0.0.1', $port, '/abort/drop', ['Authorization' => 'liumurong'], [], $chan3);
            $wg->add();
            go(function () use ($chan3, $wg) {
                $data = $chan3->pop();
                $this->assertEquals($data['body'], 'Aborted');
                $wg->done();
            });

            // 等待杀掉服务进程
            go(function () use ($wg, $pid) {
                $wg->wait();
                swoole_process::kill($pid);
            });
        }, function () use ($port) {
            $app = new Application([]);
            $app->use(function (Request $req, Response $res) {
                $req->set('comp', 'xes');
            });
            $tgroup = $app->group('/g', function (Request $req, Response $res) {
                $req->set('tkey', 'liumurong');
            });
            $tgroup->post('param/x1', function (Request $req, Response $res) {
                $res->json(['group_name' => $req->get('tkey'), 'comp_name' => $req->get('comp')]);
            });
            $abortGroup = $app->group('/abort', function (Request $req, Response $res) {
                $auth = $req->header('authorization');
                if ($auth != 'liumurong') {
                    $res->abort();
                    $res->string('Unauthorized', 401);
                }
            });
            $abortGroup->post('drop', function (Request $req, Response $res) {
                $res->string('Aborted');
            });
            $app->run($port);
        });
        $pm->run();
    }
}
