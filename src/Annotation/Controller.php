<?php

namespace Rum\Annotation;

/**
 * 控制器
 * @Annotation
 */
class Controller
{
    /**
     * only one unnamed param
     * @var string
     */
    protected $defaultFieldName = 'prefix';
    /**
     * @Required()
     * @var string
     */
    public $prefix;
}
