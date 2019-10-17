<?php

namespace Rum\Annotation;


/**
 * 路由注解解析
 */
class GroupItem
{
    /**
     * 路由
     */
    private $routers;
    /**
     * 路径前缀
     */
    private $prefix;
    /**
     * 此路由组包含的中间件
     */
    private $middlewares;

    /**
     * 路由解析参数
     * @param string $prefix 路由前缀
     * @param array $routers 路由集合
     * @param array $middlewares 中间件集合
     * @return void
     */
    public function __construct($prefix = '', $routers = [], $middlewares = [])
    {
        $this->routers = $routers;
        $this->prefix = $prefix;
        $this->middlewares = $middlewares;
    }

    /**
     * 添加路由
     * @param array $router 路由参数
     * @return void
     */
    public function addRouter($router)
    {
        array_push($this->routers, $router);
    }
    /**
     * 添加中间件
     * @param array $middleware 中间件
     * @return void
     */
    public function addMiddleware($middleware)
    {
        array_push($this->middlewares, $middleware);
    }
    /**
     * 设置路径前缀
     * @param array $prefix 前缀
     * @return void
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * 获取路由集合
     * @return array 路由集合
     */
    public function getRouters()
    {
        return $this->routers;
    }
    /**
     * 获取中间件集合
     * @return array 中间件集合
     */
    public function getMiddlewares()
    {
        return $this->middlewares;
    }
    /**
     * 获取路径前缀
     * @return string 前缀
     */
    public function getPrefix()
    {
        return $this->prefix;
    }
}
