<?php

namespace Rum\Annotation;


/**
 * 路由注解解析
 */
class GroupItem
{
    /**
     * 子路由组
     */
    private $groups;
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

    public function __construct($prefix = '', $groups = [], $routers = [], $middlewares = [])
    {
        $this->groups = $groups;
        $this->routers = $routers;
        $this->prefix = $prefix;
        $this->middlewares = $middlewares;
    }

    /**
     * 添加路由组
     */
    public function addGroup($gourp)
    {
        array_push($this->groups, $gourp);
    }
    /**
     * 添加路由
     */
    public function addRouter($router)
    {
        array_push($this->routers, $router);
    }
    /**
     * 添加中间件
     */
    public function addMiddleware($middleware)
    {
        array_push($this->middlewares, $middleware);
    }
    /**
     * 设置路径前缀
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * 获取路由组集合
     */
    public function getGroups()
    {
        return $this->groups;
    }
    /**
     * 获取路由集合
     */
    public function getRouters()
    {
        return $this->routers;
    }
    /**
     * 获取中间件集合
     */
    public function getMiddlewares()
    {
        return $this->middlewares;
    }
    /**
     * 获取路径前缀
     */
    public function getPrefix()
    {
        return $this->prefix;
    }
}
