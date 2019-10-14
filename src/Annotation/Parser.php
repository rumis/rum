<?php


namespace Rum\Annotation;

use Rum\Log\Logger;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\FileCacheReader;
use Doctrine\Common\Annotations\AnnotationRegistry;

/**
 * 注解解析
 */
class Parser
{

    public function __construct()
    { }

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
                $this->handleDirectory($absulatePath);
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
        // echo $path . PHP_EOL;
        include_once($path);       // 加载类文件
        $className = $info['filename'];
        $x = new $className();
        // echo $className . PHP_EOL;
        if (!class_exists($className)) {
            return [];
        }
        $annotationReader = new AnnotationReader();
        $reflectionClass = new \ReflectionClass($className);

        $controllerAnnotation = $annotationReader->getClassAnnotation($reflectionClass, 'Controller');
        var_dump($controllerAnnotation);
        // echo $className . PHP_EOL;
        return [];
    }


    /**
     * 
     */
    public function controller()
    { }

    /**
     * 
     */
    public function router()
    { }
}
