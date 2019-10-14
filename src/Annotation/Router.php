<?php

namespace Rum\Annotation;

/**
 * 路由
 * @Annotation
 */
final class Router
{
    /**
     * URL访问路径
     * @Required()
     * @var string
     */
    public $path;
    /**
     * 支持的URL方法，多个使用逗号分隔
     * @Required()
     * @var string
     */
    public $method;
}
