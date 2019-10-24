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
 * Request对象的一些测试
 */
final class RequestTest extends TestCase
{
    /**
     * 测试request对象的一些内容
     */
    public function testRequest()
    {
        $port = get_one_free_port();

        $pm = new ProcessManager(function ($pid) use ($port) {
            $wg =  new WaitGroup(); // 协调协程，用于一个方法中发送多个请求

            // method方法
            $chan1 = new Channel();
            HttpClient::post('127.0.0.1', $port, '/test/method', [], [], $chan1);
            $wg->add();
            go(function () use ($chan1, $wg) {
                $data = $chan1->pop();
                $this->assertEquals($data['body'], 'POST');
                $wg->done();
            });

            // path方法
            $chan2 = new Channel();
            HttpClient::get('127.0.0.1', $port, '/test/path', [], [], $chan2);
            $wg->add();
            go(function () use ($chan2, $wg) {
                $data = $chan2->pop();
                $this->assertEquals($data['body'], '/test/path');
                $wg->done();
            });

            // Restful URL
            $chan3 = new Channel();
            HttpClient::post('127.0.0.1', $port, '/test/ext/param/catchall/xt', [], [], $chan3);
            $wg->add();
            go(function () use ($chan3, $wg) {
                $data = $chan3->pop();
                $res = json_decode($data['body'], true);
                $this->assertEquals($res['p'], 'param');
                $this->assertEquals($res['a'], 'catchall/xt');
                $wg->done();
            });

            // Query Params
            $chan4 = new Channel();
            HttpClient::get('127.0.0.1', $port, '/test/queryparams', [], ['name' => 'liumurong', 'sex' => 'man'], $chan4);
            $wg->add();
            go(function () use ($chan4, $wg) {
                $data = $chan4->pop();
                $this->assertEquals($data['body'], 'liumurong');
                $wg->done();
            });

            // Query Params
            $chan5 = new Channel();
            HttpClient::post('127.0.0.1', $port, '/test/body/form', ['Content-Type' => 'application/x-www-form-urlencoded'], ['name' => 'liumurong', 'sex' => 'man'], $chan5);
            $wg->add();
            go(function () use ($chan5, $wg) {
                $data = $chan5->pop();
                $this->assertEquals($data['body'], 'liumurong');
                $wg->done();
            });

            $chan6 = new Channel();
            HttpClient::post('127.0.0.1', $port, '/test/body/json', ['Content-Type' => 'application/json'], json_encode(['name' => 'liumurong', 'sex' => 'man']), $chan6);
            $wg->add();
            go(function () use ($chan6, $wg) {
                $data = $chan6->pop();
                $this->assertEquals($data['body'], 'man');
                $wg->done();
            });

            $chan7 = new Channel();
            HttpClient::post('127.0.0.1', $port, '/test/contenttype', ['Content-Type' => 'application/json'], json_encode(['name' => 'liumurong', 'sex' => 'man']), $chan7);
            $wg->add();
            go(function () use ($chan7, $wg) {
                $data = $chan7->pop();
                $this->assertEquals($data['body'], 'application/json');
                $wg->done();
            });

            $chan8 = new Channel();
            HttpClient::post('127.0.0.1', $port, '/test/ip', [], [], $chan8);
            $wg->add();
            go(function () use ($chan8, $wg) {
                $data = $chan8->pop();
                $this->assertEquals($data['body'], '127.0.0.1');
                $wg->done();
            });

            // 等待杀掉服务进程
            go(function () use ($wg, $pid) {
                $wg->wait();
                swoole_process::kill($pid);
            });
        }, function () use ($port) {
            $app = new Application([]);
            $app->post('/test/method', function (Request $req, Response $res) {
                $res->string($req->method());
            });
            $app->get('/test/path', function (Request $req, Response $res) {
                $res->string($req->path());
            });
            $app->post('/test/ext/:p/*a', function (Request $req, Response $res) {
                $res->json(['p' => $req->params('p'), 'a' => $req->params('a')]);
            });
            $app->get('/test/queryparams', function (Request $req, Response $res) {
                $res->string($req->query('name'));
            });
            $app->post('/test/body/form', function (Request $req, Response $res) {
                $res->string($req->body('name'));
            });
            $app->post('/test/body/json', function (Request $req, Response $res) {
                $res->string($req->body('sex'));
            });
            $app->post('/test/contenttype', function (Request $req, Response $res) {
                $res->string($req->contentType());
            });
            $app->post('/test/ip', function (Request $req, Response $res) {
                $res->string($req->ip());
            });
            $app->run($port);
        });
        $pm->run();
    }
}
