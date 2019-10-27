<?php

declare(strict_types=1);

namespace Rum\Test;

use Rum\Application;
use Rum\Request;
use Rum\Response;
use RumTest\ProcessManager;
use Swoole\Coroutine\Channel;
use RumTest\HttpClient;
use RumTest\WaitGroup;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/include/functions.php';


/**
 * 注解测试
 */
final class AnnotationTest extends TestCase
{

    /**
     * 路由注解测试
     */
    public function testRouter()
    {
        $port = get_one_free_port();

        $pm = new ProcessManager(function ($pid) use ($port) {
            $wg =  new WaitGroup(); // 协调协程，用于一个方法中发送多个请求

            // \User\get
            $chan1 = new Channel();
            HttpClient::post('127.0.0.1', $port, '/user/get', [], [], $chan1);
            $wg->add();
            go(function () use ($chan1, $wg) {
                $data = $chan1->pop();
                $this->assertEquals($data['body'], '/user/get');
                $wg->done();
            });

            // \User\getItem
            $chan2 = new Channel();
            HttpClient::post('127.0.0.1', $port, '/user/getitem', [], [], $chan2);
            $wg->add();
            go(function () use ($chan2, $wg) {
                $data = $chan2->pop();
                $this->assertEquals($data['body'], '/user/getitem');
                $wg->done();
            });

            // \User\getParam
            $chan21 = new Channel();
            HttpClient::post('127.0.0.1', $port, '/user/getparam', [], [], $chan21);
            $wg->add();
            go(function () use ($chan21, $wg) {
                $data = $chan21->pop();
                $this->assertEquals($data['body'], 'murong');
                $wg->done();
            });

            // \Teacher\Counselor\get
            $chan3 = new Channel();
            HttpClient::post('127.0.0.1', $port, '/teacher/counselor/get', [], [], $chan3);
            $wg->add();
            go(function () use ($chan3, $wg) {
                $data = $chan3->pop();
                $this->assertEquals($data['body'], '/teacher/counselor/get');
                $wg->done();
            });

            // \Teacher\Counselor\getItem::GET
            $chan4 = new Channel();
            HttpClient::get('127.0.0.1', $port, '/teacher/counselor/get/murong', [], [], $chan4);
            $wg->add();
            go(function () use ($chan4, $wg) {
                $data = $chan4->pop();
                $this->assertEquals($data['body'], 'murong');
                $wg->done();
            });

            // \Teacher\Counselor\getItem::POST
            $chan5 = new Channel();
            HttpClient::POST('127.0.0.1', $port, '/teacher/counselor/get/liumurong', [], [], $chan5);
            $wg->add();
            go(function () use ($chan5, $wg) {
                $data = $chan5->pop();
                $this->assertEquals($data['body'], 'liumurong');
                $wg->done();
            });

            // 等待杀掉服务进程
            go(function () use ($wg, $pid) {
                $wg->wait();
                \swoole_process::kill($pid);
            });
        }, function () use ($port) {
            $dir = dirname(__FILE__);
            $app = new Application([
                'enableControllerAnnotation' => true,
                'baseNamespace' => '\Rum\Test\Controller',
                'baseControllerPath' => $dir . '/Controller',
                'ignoreAnnotations' => ['date'],
            ]);
            $app->run($port);
        });
        $pm->run();
    }
}
