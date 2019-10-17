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
     * 路由注解解析
     * @param string $baseNamespace 路由根命名空间
     * @param string $controllerBasePath 路由php文件的根目录
     * @param string[] $ignoreAnnotations 需要忽略的注解(比如@author,@date等)
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
     * @return GroupItem[] 路由组集合
     */
    public function handle()
    {
        return $this->handleDirectory($this->controllerBasePath, $this->baseNamespace);
    }

    /**
     * 解析某目录下的所有路由
     * @param string $path 路径
     * @param string $workspace 命名空间
     * @return GroupItem[] 路由组集合
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
        $groups = [];
        while (($file = readdir($dir)) !== false) {
            if ($file[0] == '.' || $file == 'vendor') {
                // 忽略隐藏、vendor目录
                continue;
            }
            $absulatePath = $path . '/' . $file;
            if (is_dir($absulatePath)) {
                array_push($groups, ...$this->handleDirectory($absulatePath, $workspace . '\\' . $file, $file));
                continue;
            }
            array_push($groups, $this->handleFile($absulatePath, $workspace));
        }
        return $groups;
    }

    /**
     * 解析Controller文件，读取所有routers
     * @param string $path 文件路径
     * @param string $workspace 命名空间
     * @return GroupItem 路由
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
        // var_dump($className);
        $group = new GroupItem($info['filename']);
        // 读取Controller注解
        $annotationReader = new AnnotationReader();
        $reflectionClass = new \ReflectionClass($className);
        $controllerAnnotation = $annotationReader->getClassAnnotation($reflectionClass, 'Rum\Annotation\Controller');
        if (empty($controllerAnnotation)) {
            return $group;
        }
        // 记录当前路由组的统一前缀路径
        $group->setPrefix($controllerAnnotation->prefix);
        $methods = $reflectionClass->getMethods();
        if (empty($methods)) {
            return $group;
        }
        // var_dump($methods);
        $cont = new $className();
        foreach ($methods as $method) {
            // 解析Router，Middleware注解
            $routerAnnotations = $annotationReader->getMethodAnnotations($method);
            foreach ($routerAnnotations as $anno) {
                $handleName = $method->name;
                // var_dump($anno);
                switch (get_class($anno)) {
                    case 'Rum\Annotation\Router':
                        $group->addRouter([
                            'path' => $anno->path,
                            'methods' => $anno->method,
                            'handle' => function (Request $req, Response $res) use ($cont, $handleName) {
                                $cont->$handleName($req, $res);
                            }
                        ]);
                        break;
                    case 'Rum\Annotation\Middleware':
                        $group->addMiddleware(function (Request $req, Response $res) use ($cont, $handleName) {
                            $cont->$handleName($req, $res);
                        });
                        break;
                    default:
                        break;
                }
            }
        }
        return $group;
    }
}
