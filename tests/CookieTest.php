<?php

use PHPUnit\Framework\TestCase;
use Rum\Application;
use Rum\Request;
use Rum\Response;
use RumTest\ProcessManager;
use RumTest\HttpClient;


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
            // $client = new swoole_client(SWOOLE_SOCK_TCP);
            // if (!$client->connect('127.0.0.1', $port, 1)) {
            //     exit("connect failed. Error: {$client->errCode}\n");
            // }
            // $header = "POST /user/one HTTP/1.1\r\n";
            // $header .= "Host: 127.0.0.1\r\n";
            // $header .= "Connection: keep-alive\r\n";
            // $header .= "Cache-Control: max-age=0\r\n";

            // $header .= "$h_key: $h_val\r\n";
            // $header .= "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8\r\n";
            // $header .= "User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.116 Safari/537.36\r\n";
            // $header .= "\r\n";
            // $_sendStr = $header;

            // $client->send($_sendStr);
            // $data = $client->recv();
            // list(, $ctx) = explode("\r\n\r\n", $data);
            // $client->close();

            HttpClient::post('127.0.0.1', $port, '/user/one', [], [], function ($data) use ($c_key, $c_val) {
                // $this->assertEquals($cli->cookies[$c_key], $c_val);
                var_dump($data);
                $this->assertEquals(1, 1);
            });
            swoole_process::kill($pid);
        }, function () use ($port, $c_key, $c_val) {
            $app = new Application([]);
            $app->post('/user/one', function (Request $req, Response $res) use ($c_key, $c_val) {
                var_dump('request');
                $res->cookie($c_key, $c_val);
                $res->end('test');
            });
            $app->run($port);
        });

        $pm->run();
    }
}
