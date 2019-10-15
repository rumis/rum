<?php


namespace Rum\Annotation;

use Rum\Log\Logger;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Rum\Request;
use Rum\Response;

/**
 * 注解解析
 */
class RouterParser
{
    /**
     * 根命名空间
     */
    private $baseNamespace;
    /**
     * 控制器根目录
     */
    private $controllerBasePath;

    /**
     * 构造
     */
    public function __construct($baseNamespace, $controllerBasePath, $ignoreAnnotations = [])
    {
        // 注册类加载器
        AnnotationRegistry::registerLoader(function ($class) {
            return class_exists($class) || interface_exists($class);
        });

        // 添加需要忽略的注解
        foreach ($ignoreAnnotations as $ignoreClassName) {
            AnnotationReader::addGlobalIgnoredName($ignoreClassName);
        }

        $this->baseNamespace = $baseNamespace;
        $this->controllerBasePath = $controllerBasePath;
    }

    /**
     * 解析所有的注解路由
     */
    public function handle()
    {
        return $this->handleDirectory($this->controllerBasePath, $this->baseNamespace);
    }

    /**
     * 处理目录下内容
     */
    public function handleDirectory($path, $workspace)
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
                array_push($routers, ...$this->handleDirectory($absulatePath, $workspace . '\\' . $file));
                continue;
            }
            array_push($routers, ...$this->handleFile($absulatePath, $workspace));
        }
        return $routers;
    }

    /**
     *  解析Controller文件，读取所有routers
     */
    public function handleFile($path, $workspace)
    {
        $info = pathinfo($path);
        if ($info['extension'] !== 'php') {
            return [];
        }
        $className = $workspace . '\\' . $info['filename'];
        // echo $className . PHP_EOL;
        if (!class_exists($className)) {
            return [];
        }
        $routers = [];
        $annotationReader = new AnnotationReader();
        $reflectionClass = new \ReflectionClass($className);
        $controllerAnnotation = $annotationReader->getClassAnnotation($reflectionClass, 'Rum\Annotation\Controller');
        if (empty($controllerAnnotation)) {
            return $routers;
        }
        $methods = $reflectionClass->getMethods();
        if (empty($methods)) {
            return $routers;
        }
        // var_dump($methods);
        $cont = new $className();
        foreach ($methods as $mehtod) {
            $routerAnnotation = $annotationReader->getMethodAnnotation($mehtod, 'Rum\Annotation\Router');
            if (empty($routerAnnotation)) {
                continue;
            }
            $handleName = $mehtod->name;
            $routers[] = [
                'path' => $controllerAnnotation->prefix . $routerAnnotation->path,
                'methods' => $routerAnnotation->method,
                'handle' => function (Request $req, Response $res) use ($cont, $handleName) {
                    $cont->$handleName($req, $res);
                }
            ];
        }
        return $routers;
    }
}
