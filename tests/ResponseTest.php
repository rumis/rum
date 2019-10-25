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
 * 测试Response对象
 */
final class ResponseTest extends TestCase
{
    /**
     * 测试Response对象
     * 由于协程的使用，测试写在一个方法内
     */
    public function testResponse()
    {
        $port = get_one_free_port();

        $pm = new ProcessManager(function ($pid) use ($port) {

            $wg =  new WaitGroup(); // 协调协程，用于一个方法中发送多个请求

            $chan1 = new Channel();
            HttpClient::post('127.0.0.1', $port, '/test/write', [], [], $chan1);
            $wg->add();
            go(function () use ($chan1, $wg) {
                $data = $chan1->pop();
                $this->assertEquals($data['body'], 'test_write');
                $wg->done();
            });

            $chan2 = new Channel();
            HttpClient::post('127.0.0.1', $port, '/test/end', [], [], $chan2);
            $wg->add();
            go(function () use ($chan2, $wg) {
                $data = $chan2->pop();
                $this->assertEquals($data['body'], 'test_end');
                $wg->done();
            });

            $chan3 = new Channel();
            HttpClient::post('127.0.0.1', $port, '/test/statuscode', [], [], $chan3);
            $wg->add();
            go(function () use ($chan3, $wg) {
                $data = $chan3->pop();
                var_dump($data);
                $this->assertEquals($data['code'], 402);
                $wg->done();
            });

            $chan4 = new Channel();
            HttpClient::post('127.0.0.1', $port, '/test/content/json', [], [], $chan4);
            $wg->add();
            go(function () use ($chan4, $wg) {
                $data = $chan4->pop();
                $this->assertEquals($data['header']['content-type'], 'application/json');
                $b = json_decode($data['body'], true);
                $this->assertEquals($b['data']['name'], 'liumurong');
                $wg->done();
            });

            $chan5 = new Channel();
            HttpClient::post('127.0.0.1', $port, '/test/content/xml', [], [], $chan5);
            $wg->add();
            go(function () use ($chan5, $wg) {
                $data = $chan5->pop();
                $this->assertEquals($data['header']['content-type'], 'application/xml');
                $wg->done();
            });

            $chan6 = new Channel();
            HttpClient::post('127.0.0.1', $port, '/test/content/html', [], [], $chan6);
            $wg->add();
            go(function () use ($chan6, $wg) {
                $data = $chan6->pop();
                $this->assertEquals($data['header']['content-type'], 'text/html');
                $wg->done();
            });

            $chan7 = new Channel();
            HttpClient::post('127.0.0.1', $port, '/test/content/string', [], [], $chan7);
            $wg->add();
            go(function () use ($chan7, $wg) {
                $data = $chan7->pop();
                $this->assertEquals($data['header']['content-type'], 'text/plain');
                $this->assertEquals($data['body'], '刘木荣');
                $wg->done();
            });

            // 等待杀掉服务进程
            go(function () use ($wg, $pid) {
                $wg->wait();
                swoole_process::kill($pid);
            });
        }, function () use ($port) {
            $app = new Application([]);
            $app->post('/test/write', function (Request $req, Response $res) {
                $res->write('test_write');
                $res->end();
            });
            $app->post('/test/end', function (Request $req, Response $res) {
                $res->end('test_end');
            });
            $app->post('/test/statuscode', function (Request $req, Response $res) {
                $res->status(402);
                $res->end();
            });
            $app->post('/test/content/json', function (Request $req, Response $res) {
                $res->json(['stat' => 1, 'msg' => 'ok', 'data' => [
                    'name' => 'liumurong'
                ]]);
            });
            $app->post('/test/content/xml', function (Request $req, Response $res) {
                $res->xml("<?xml version=\"1.0\" encoding=\"utf-8\"?>
                <manifest xmlns:android=\"http://schemas.android.com/apk/res/android\">  
                    <application android:label=\"@string/app_name\" android:icon=\"@drawable/osg\"> 
                    </application> 
                </manifest>");
            });
            $app->post('/test/content/html', function (Request $req, Response $res) {
                $res->html("
                <html lang=\"en\">
                <head>
                    <meta charset=\"UTF-8\">
                    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
                    <meta http-equiv=\"X-UA-Compatible\" content=\"ie=edge\">
                    <title>Html Test</title>
                </head>
                <body>
                    HTML 测试
                </body>
                </html>");
            });
            $app->post('/test/content/string', function (Request $req, Response $res) {
                $res->string('刘木荣');
            });

            $app->run($port);
        });
        $pm->run();
    }
}
