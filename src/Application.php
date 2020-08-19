<?php

namespace Rum;

use Rum\Annotation\GroupItem;
use Rum\Annotation\RouterParser;
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
     * Rum
     * @param boolean  $opts.handleMethodNotAllowed 是否允许405
     * @param func  $opts.noRoute 405响应方法
     * @param func  $opts.noMethod 404响应方法
     * @param boolean  $opts.enableControllerAnnotation 是否允许注解路由
     * @param string  $opts.baseNamespace 注解路由的根命名空间
     * @param string  $opts.baseControllerPath 注解路由的根路径
     * @param string[]  $opts.ignoreAnnotations 忽略的注解
     * @return
     */
    public function __construct($opts)
    {
        parent::__construct('/', $this);
        // 默认初始化控制台日志器
        Logger::setLogger(Console::init());  // 

        // 404
        $this->handleMethodNotAllowed = !empty($opts['handleMethodNotAllowed']) ? $opts['handleMethodNotAllowed'] : false;
        $this->noRoute = !empty($opts['noRoute']) ? $opts['noRoute'] : function (Request $req, Response $res) {
            return $this->default404Method($req, $res);
        };
        // 405
        $this->noMethod = !empty($opts['noMethod']) ? $opts['noMethod'] : function (Request $req, Response $res) {
            return $this->default405Method($req, $res);
        };

        // 添加默认的第一个组件
        $this->use(BodyParser());
        // 简单的日志组件
        $this->use(Logger::middle());

        // 解析注解路由
        if (!empty($opts['enableControllerAnnotation'])) {
            if (empty($opts['baseNamespace'])) {
                Logger::fatal('启用路由注解时必须指定控制器根命名空间');
                return;
            }
            if (empty($opts['baseControllerPath'])) {
                Logger::fatal('启用路由注解时必须指定控制器根路径');
                return;
            }
            $p = new RouterParser($opts['baseNamespace'], $opts['baseControllerPath'], $opts['ignoreAnnotations']);
            $groups = $p->handle();
            $this->handleRouter($groups);
        }
    }

    /**
     * 将解析控制器路由得到的参数转化为真正的路由
     * @param {array} $groupItem 解析控制器得到的路由组
     * @author: liumurong  <liumurong1@100tal.com>
     * @Date: 2019-10-16 09:24:35
     */
    private function handleRouter($groupItems)
    {
        foreach ($groupItems as $item) {
            $group = $this->group($item->getPrefix(), ...$item->getMiddlewares());
            foreach ($item->getRouters() as $router) {
                $methods = explode(',', $router['methods']);
                foreach ($methods as $method) {
                    $group->handle($method, $router['path'], $router['handle']);
                }
            }
        }
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
     * 创建新的路由组
     * @param string $path 路径
     * @param func[] $middleware 中间件集合
     * @return RouterGroup 
     */
    public function group($path, ...$middleware)
    {
        $g = new RouterGroup($path, $this);
        $g->use(...$this->handlers, ...$middleware);
        return $g;
    }

    /**
     * 添加路由
     * @param string $method 方法名
     * @param string $path 路径
     * @param func[] $handles 中间件集合
     * @return 
     */
    public function addRoute($method, $path, ...$handles)
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
        $this->trees[$method]->addRoute($path, $handles);
        Logger::info('{method}:{router}', [
            'router' => $path,
            'method' => $method,
        ]);
    }



    /**
     * 静态路径
     */
    public function static($relativePath, $root)
    {
    }

    /**
     * 处理HTTP请求
     * @param Request $req 请求对象
     * @param Response $res 响应对象
     * @return
     */
    public function handleHTTPRequest(Request &$req, Response &$res)
    {
        try {
            $method = $req->method();
            $path = $req->path();
            // 路由存在
            if (!empty($this->trees[$method])) {
                $handle = $this->trees[$method]->getValue($path);
                if (!empty($handle['handles'])) {
                    $req->setParams($handle['params']); // 记录URL中的参数
                    foreach ($handle['handles'] as $fn) {
                        if (!$res->aborted()) {
                            $fn($req, $res);
                        }
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
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            $res->status(500);
            $res->end($th->getMessage());
        }
    }

    /**
     * 设置404处理方法
     * @param func $handle 
     */
    public function resetNoRoute($handle)
    {
        $this->noRoute = $handle;
    }
    /**
     * 设置405处理方法
     * @param func $handle 
     */
    public function resetNoMethod($handle)
    {
        $this->noMethod = $handle;
    }

    /**
     * 默认的404方法
     * @param Request $req 请求对象
     * @param Response $res 响应对象
     * @return
     */
    private function default404Method(Request $req, Response $res)
    {
        $res->header('Content-type', Mime::PLAIN);
        $res->end('404 Not Found');
    }
    /**
     * 默认的405方法
     * @param Request $req 请求对象
     * @param Response $res 响应对象
     * @return
     */
    private function default405Method(Request $req, Response $res)
    {
        $res->header('Content-type', Mime::PLAIN);
        $res->end('405 Method Not Allowed');
    }
}
