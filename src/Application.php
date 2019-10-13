<?php

namespace Rum;

use Rum\Log\Console;
use Rum\Log\Logger;

require_once('BodyParser.php');


/**
 * Rum Application 
 */
class Application extends RouterGroup
{
    private $httpServe;
    private $trees;
    private $handleMethodNotAllowed;
    private $noRoute;
    private $noMethod;

    /**
     * 构造方法
     */
    public function __construct($opts)
    {

        parent::__construct('/', $this);

        // 404
        $this->handleMethodNotAllowed = !empty($opts['handleMethodNotAllowed']) ? $opts['handleMethodNotAllowed'] : false;
        $this->noRoute = !empty($opts['noRoute']) ? $opts['noRoute'] : function (Request $req, Response $res) {
            return $this->default404Method($req, $res);
        };
        // 405
        $this->noMethod = !empty($opts['noMethod']) ? $opts['noMethod'] : function (Request $req, Response $res) {
            return $this->default405Method($req, $res);
        };

        Logger::setLogger(Console::init());  // 默认初始化控制台日志器

        $this->use(BodyParser());   // 添加默认的第一个组件
        $this->use(Logger::middle()); // 简单的日志组件

    }
    /**
     * 启动
     */
    public function run($port = 9501, $started = null)
    {
        $this->httpServe = new \Swoole\Http\Server("0.0.0.0", $port, SWOOLE_BASE);
        $this->httpServe->on('request', function ($request, $response) {
            $req = new Request($request);
            $res = new Response($response);
            go(function () use ($req, $res) {
                // 处理http请求
                $this->handleHTTPRequest($req, $res);
            });
        });
        if (!empty($started)) {
            $this->httpServe->on('start', function () use ($started, $port) {
                $started();
                go(function () use ($port) {
                    Logger::info('server start on 0.0.0.0:{port}', ['port' => $port]);
                });
            });
        }

        $this->httpServe->start();
    }

    /**
     * 启动2
     * 另一种协程启动方式
     */
    public function runc($port = 9501, $started = null)
    {
        go(function () use ($port, $started) {
            $this->httpServe = new \Co\Http\Server("0.0.0.0", $port, false);
            $this->httpServe->handle('/', function ($request, $response) {
                $req = new Request($request);
                $res = new Response($response);
                $this->handleHTTPRequest($req, $res);
            });

            if (!empty($started)) {
                $this->httpServe->on('start', function () use ($started, $port) {
                    $started();
                    Logger::info('server start on 0.0.0.0:{port}', ['port' => $port]);
                });
            }

            $this->httpServe->start();
        });
    }

    /**
     * 添加路由
     */
    public function addRoute($method, $path, $handle)
    {
        if (!in_array($method, Method::S)) {
            Logger::fatal('不支持的请求方法');
            return;
        }
        if ($path[0] != '/') {
            Logger::fatal('路由路径必须以/开头');
            return;
        }
        if (empty($this->trees[$method])) {
            $this->trees[$method] = new Node();
        }
        $this->trees[$method]->addRoute($path, $handle);
    }

    /**
     * 创建新的路由组
     */
    public function group($path, ...$middleware)
    {
        $g = new RouterGroup($path, $this);
        $g->use(...$this->handlers, ...$middleware);
        return $g;
    }

    /**
     * 静态路径
     */
    public function static($relativePath, $root)
    { }

    /**
     * 处理HTTP请求
     */
    public function handleHTTPRequest(Request &$req, Response &$res)
    {
        $method = $req->method();
        $path = $req->path();
        // 路由存在
        if (!empty($this->trees[$method])) {
            $handle = $this->trees[$method]->getValue($path);
            if (!empty($handle['handles'])) {
                $req->setParams($handle['params']); // 记录URL中的参数
                foreach ($handle['handles'] as $fn) {
                    $fn($req, $res);
                }
                return;
            }
        }
        // 检测是否包含同PATH但是METHOD不同的路由，提示405错误
        if ($this->handleMethodNotAllowed) {
            foreach ($this->trees as $m => $tree) {
                if ($m != $method) {
                    $handle = $tree->getValue($path);
                    if (!empty($handle) && !empty($handle['handles'])) {
                        $noMethod = $this->noMethod;
                        $noMethod($req, $res);
                        return;
                    }
                }
            }
        }
        // 未包含路由，提示404错误。
        $noRoute = $this->noRoute;
        $noRoute($req, $res);
    }

    /**
     * 设置404处理方法
     */
    public function resetNoRoute($handle)
    {
        $this->noRoute = $handle;
    }
    /**
     * 设置405处理方法
     */
    public function resetNoMethod($handle)
    {
        $this->noMethod = $handle;
    }

    /**
     * 默认的404方法
     */
    private function default404Method(Request $req, Response $res)
    {
        $res->header('Content-type', Mime::PLAIN);
        $res->end('404 Not Found');
    }
    /**
     * 默认的405方法
     */
    private function default405Method(Request $req, Response $res)
    {
        $res->header('Content-type', Mime::PLAIN);
        $res->end('405 Method Not Allowed');
    }
}
