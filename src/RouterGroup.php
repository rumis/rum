<?php

namespace Rum;

/**
 * 路由组
 */
class RouterGroup
{

    public $handlers;
    public $basePath;
    public $app;
    /**
     * 路由组
     * @param string $path 此路由组统一根路径
     * @param $rum app对象
     * @return RouterGroup
     */
    public function __construct($path, &$rum)
    {
        $this->basePath = $path;
        $this->app = $rum;
        $this->handlers = [];
    }

    /**
     * 获取路由组的根路径
     * @return string
     */
    public function basePath()
    {
        return $this->basePath;
    }

    /**
     * 挂载插件
     * @param func[] 插件集合
     * @return void
     */
    public function use(...$middleware)
    {
        array_push($this->handlers, ...$middleware);
    }
    /**
     * 添加路由
     * @param string $method 方法
     * @param string $relativePath 相对路径
     * @param func $handle 请求响应方法
     * @return void
     */
    public function handle($method, $relativePath, $handle)
    {
        $absolutePath  = Util::path_join($this->basePath, $relativePath);
        $this->app->addRoute($method, $absolutePath, ...array_merge($this->handlers, [$handle]));
    }
    /**
     * 一次添加全部方法的路由
     * @param string $relativePath 相对路径
     * @param func $handle 请求响应方法
     * @return void
     */
    public function any($relativePath, $handle)
    {
        foreach (Method::S as $method) {
            $this->handle($method, $relativePath, $handle);
        }
    }

    /**
     * GET请求
     * @param string $relativePath 相对路径
     * @param func $handle 请求响应方法
     * @return void
     */
    public function get($relativePath, $handle)
    {
        return $this->handle(Method::GET, $relativePath, $handle);
    }
    /**
     * POST请求
     * @param string $relativePath 相对路径
     * @param func $handle 请求响应方法
     * @return void
     */
    public function post($relativePath, $handle)
    {
        return $this->handle(Method::POST, $relativePath, $handle);
    }
    /**
     * HEAD请求
     * @param string $relativePath 相对路径
     * @param func $handle 请求响应方法
     * @return void
     */
    public function head($relativePath, $handle)
    {
        return $this->handle(Method::HEAD, $relativePath, $handle);
    }
    /**
     * DELETE请求
     * @param string $relativePath 相对路径
     * @param func $handle 请求响应方法
     * @return void
     */
    public function delete($relativePath, $handle)
    {
        return $this->handle(Method::DELETE, $relativePath, $handle);
    }
    /**
     * PATCH请求
     * @param string $relativePath 相对路径
     * @param func $handle 请求响应方法
     * @return void
     */
    public function patch($relativePath, $handle)
    {
        return $this->handle(Method::PATCH, $relativePath, $handle);
    }
    /**
     * PUT请求
     * @param string $relativePath 相对路径
     * @param func $handle 请求响应方法
     * @return void
     */
    public function put($relativePath, $handle)
    {
        return $this->handle(Method::PUT, $relativePath, $handle);
    }
    /**
     * OPTIONS请求
     * @param string $relativePath 相对路径
     * @param func $handle 请求响应方法
     * @return void
     */
    public function options($relativePath, $handle)
    {
        return $this->handle(Method::OPTIONS, $relativePath, $handle);
    }

    /**
     * CONNECT请求
     * @param string $relativePath 相对路径
     * @param func $handle 请求响应方法
     * @return void
     */
    public function connect($relativePath, $handle)
    {
        return $this->handle(Method::CONNECT, $relativePath, $handle);
    }
    /**
     * TRACE请求
     * @param string $relativePath 相对路径
     * @param func $handle 请求响应方法
     * @return void
     */
    public function trace($relativePath, $handle)
    {
        return $this->handle(Method::TRACE, $relativePath, $handle);
    }
}
