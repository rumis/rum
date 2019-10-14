<?php


namespace Rum\Annotation;

use Rum\Log\Logger;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\FileCacheReader;
use Doctrine\Common\Annotations\AnnotationRegistry;

/**
 * 注解解析
 */
class RouterParser
{
    private $baseNamespace;
    private $controllerBasePath;

    /**
     * 构造
     */
    public function __construct($baseNamespace, $controllerBasePath)
    {
        // 注册类加载器
        AnnotationRegistry::registerLoader(function ($class) {
            return class_exists($class) || interface_exists($class);
        });

        $this->baseNamespace = $baseNamespace;
        $this->controllerBasePath = $controllerBasePath;
    }

    /**
     * 解析所有的注解路由
     */
    public function handle()
    {
        return $this->handleDirectory($this->controllerBasePath);
    }

    /**
     * 处理目录下内容
     */
    public function handleDirectory($path)
    {
        if (!is_dir($path)) {
            return;
        }
        $dir = opendir($path);
        if ($dir === false) {
            Logger::error('can not open controller directory {path}', ['path' => $dir]);
            return [];
        }
        $routers = [];
        while (($file = readdir($dir)) !== false) {
            if ($file[0] == '.' || $file == 'vendor') {
                // 隐藏、vendor目录忽略
                continue;
            }
            $absulatePath = $path . '/' . $file;
            if (is_dir($absulatePath)) {
                array_push($routers, ...$this->handleDirectory($absulatePath));
                continue;
            }
            array_push($routers, ...$this->handleFile($absulatePath));
        }
        return $routers;
    }

    /**
     * 
     */
    public function handleFile($path)
    {
        // echo $path . PHP_EOL;
        $info = pathinfo($path);
        if ($info['extension'] !== 'php') {
            return [];
        }
        $className = $this->baseNamespace . '\\' . $info['filename'];
        // echo $className . PHP_EOL;
        if (!class_exists($className)) {
            return [];
        }
        $routers = [];
        $annotationReader = new AnnotationReader();
        $reflectionClass = new \ReflectionClass($className);
        // var_dump($className);
        $controllerAnnotations = $annotationReader->getClassAnnotations($reflectionClass);
        $controllerAnnotation = $annotationReader->getClassAnnotation($reflectionClass, 'Rum\Annotation\Controller');
        // var_dump($controllerAnnotations);
        // var_dump($controllerAnnotation);
        if (empty($controllerAnnotation)) {
            return $routers;
        }
        // var_dump($controllerAnnotation);
        $methods = $reflectionClass->getMethods();
        var_dump($methods);
        if (empty($methods)) {
            return $routers;
        }
        var_dump($methods);
        $cont = new $className();
        foreach ($methods as $mehtod) {
            $routerAnnotation = $annotationReader->getMethodAnnotation($mehtod, 'Router');
            if (empty($routerAnnotation)) {
                continue;
            }
            $routers[] = [
                'path' => $controllerAnnotation['prefix'] . $routerAnnotation['path'],
                'methods' => $routerAnnotation['method'],
                'handle' => $cont->$mehtod,
            ];
        }
        return $routers;
    }
}
