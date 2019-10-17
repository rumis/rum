<?php

namespace Rum\Annotation;

/**
 * 控制器注解
 * 
 * @Annotation
 */
class Controller
{
    /**
     * 仅包含的一个匿名参数的名称
     * @var string
     */
    protected $defaultFieldName = 'prefix';
    /**
     * @Required()
     * @var string
     */
    public $prefix;
}
