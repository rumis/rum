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
 * 基础测试
 */
final class RouterTest extends TestCase
{
    /**
     * 测试后台的返回内容
     */
    public function testRouter()
    {
        $port = get_one_free_port();

        $pm = new ProcessManager(function ($pid) use ($port) {

            $wg =  new WaitGroup(); // 协调协程，用于一个方法中发送多个请求

            $chan1 = new Channel();
            HttpClient::post('127.0.0.1', $port, '/test/json', [], [], $chan1);
            $wg->add();
            go(function () use ($chan1, $wg) {
                $data = $chan1->pop();
                $this->assertEquals($data['header']['content-type'], 'application/json');
                $b = json_decode($data['body'], true);
                $this->assertEquals($b['data']['name'], 'liumurong');
                $wg->done();
            });

            $chan2 = new Channel();
            HttpClient::post('127.0.0.1', $port, '/test/xml', [], [], $chan2);
            $wg->add();
            go(function () use ($chan2, $wg) {
                $data = $chan2->pop();
                $this->assertEquals($data['header']['content-type'], 'application/xml');
                $wg->done();
            });

            // 等待杀掉服务进程
            go(function () use ($wg, $pid) {
                $wg->wait();
                swoole_process::kill($pid);
            });
        }, function () use ($port) {
            $app = new Application([]);
            $app->post('/test/json', function (Request $req, Response $res) {
                $res->json(['stat' => 1, 'msg' => 'ok', 'data' => [
                    'name' => 'liumurong'
                ]]);
            });
            $app->post('/test/xml', function (Request $req, Response $res) {
                $res->xml("<?xml version=\"1.0\" encoding=\"utf-8\"?>
                <manifest xmlns:android=\"http://schemas.android.com/apk/res/android\">  
                    <application android:label=\"@string/app_name\" android:icon=\"@drawable/osg\"> 
                    </application> 
                </manifest>");
            });
            $app->run($port);
        });
        $pm->run();
    }
}
