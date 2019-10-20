<?php

namespace Rum;

use Exception;
use Rum\Log\Logger;

const NORMAL = 1; // 普通节点
const ROOT = 2; // 根节点
const PARAM = 4;    // 参数节点
const CATCHALL = 8; // 大参数节点


class Node
{
    // 此节点上的URL路径
    public $path;
    // 子节点是不是参数节点
    public $wildChild;
    // 节点类型
    public $nType;
    // 子节点索引
    public $indices;
    // 子节点
    public $children;
    // 处理方法
    public $handles;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->path = '';
        $this->wildChild = false;
        $this->nType = NORMAL;
        $this->indices = '';
        $this->children = [];
    }

    /**
     * 添加子节点
     * 调用此方法前，节点的path参数为空
     * @param string $path 路由路径
     * @param func[] 响应方法集合
     * @return void
     */
    public function insertChild($path, $handles)
    {
        $handleNode = $this;
        $handleNode->path = $path;
        for ($i = 0, $max = strlen($path); $i < $max; $i++) {
            $c = $path[$i];
            // 检测是否包含参数
            if ($c != ':' && $c != '*') {
                continue;
            }
            $end = $i + 1;
            while ($end < $max && $path[$end] != '/') {
                switch ($path[$end]) {
                    case ':':
                    case '*':
                        throw new Exception('一段路径中只能包含一个参数项');
                    default:
                        $end++;
                }
            }
            if (count($this->children) > 0) {
                throw new Exception('参数节点不可包含其他子节点');
            }
            if ($end - $i < 2) {
                throw new Exception('必须存在参数名称');
            }
            if ($c == ':') {
                if ($i > 0) {
                    $this->path = substr($path, 0, $i);
                }
                $this->wildChild = true;

                $child = new Node();
                $child->nType = PARAM;
                $child->path = substr($path, $i);
                $this->children = [$child];
                if ($end < $max) {
                    $child->path = substr($path, $i, $end - $i);
                    $nextChild = new Node();
                    $child->children = [$nextChild];
                    $nextChild->insertChild(substr($path, $end), $handles);
                    return;
                }
                $handleNode = $child;
                break;
            } else {
                if ($end != $max) {
                    throw new Exception('*参数只能出现在路由的最后一段');
                }
                if ($path[$i - 1] != '/') {
                    throw new Exception('*参数前必须为/');
                }
                // 当前节点路径
                $this->path = substr($path, 0, $i);
                $this->indices = $path[$i];
                $this->wildChild = true;

                $schild = new Node();
                $schild->nType = CATCHALL;
                $schild->path = substr($path, $i);
                $this->children = [$schild];

                $handleNode = $schild;
                break;
            }
        }
        // $handleNode->path=$path;
        $handleNode->handles = $handles;
    }


    /**
     * 添加路由
     * @param string $path 路由路径
     * @param func[] 响应方法集合
     * @return void
     */
    public function addRoute($path, $handles)
    {
        if (strlen($this->path) == 0 && count($this->children) == 0) {
            // 空树
            $this->insertChild($path, $handles);
            $this->nType = ROOT;
        } else {
            // 找到新路径和原路径的最小前缀
            $i = 0;
            $max = min(strlen($path), strlen($this->path));
            while ($i < $max && $path[$i] == $this->path[$i]) {
                $i++;
            }
            if ($i < strlen($this->path)) {
                // 取原路径的后一段生成新的节点并继承原节点的所有属性
                $child = new Node();
                $child->path = substr($this->path, $i);
                $child->wildChild = $this->wildChild;
                $child->nType = NORMAL;
                $child->indices = $this->indices;
                $child->children = $this->children;
                $child->handles = $this->handles;

                // 更新原节点：子节点，索引，路径等
                $this->children = [$child];
                $this->indices = $this->path[$i];
                $this->path = substr($path, 0, $i);
                $this->handles = null;
                $this->wildChild = false;
            }
            if ($i < strlen($path)) {
                // 新路径的后半段生成新的节点并插入
                $path = substr($path, $i);
                if ($this->wildChild) {
                    $firstChild = $this->children[0];
                    // 检测子节点是否是参数节点
                    if (
                        strlen($path) >= strlen($firstChild->path) &&
                        $firstChild->path == substr($path, 0, strlen($firstChild->path)) && (strlen($firstChild->path) >= strlen($path) ||
                            $path[strlen($firstChild->path)] == '/')
                    ) {
                        // 符合参数节点
                        $firstChild->addRoute($path, $handles);
                        return;
                    } else {
                        // 路径和参数节点冲突
                        throw new Exception('路径冲突');
                    }
                }
                $c = $path[0];
                // 当前节点为参数节点，并且只有一个子节点，递归进入下一级
                if ($this->nType == PARAM && $c == '/' && count($this->children) == 1) {
                    $firstChild = $this->children[0];
                    $firstChild->addRoute($path, $handles);
                    return;
                }
                // 通过索引查找所有子节点，符合条件的递归进入下一级节点
                for ($i = 0; $i < strlen($this->indices); $i++) {
                    if ($c == $this->indices[$i]) {
                        $firstChild = $this->children[$i];
                        $firstChild->addRoute($path, $handles);
                        return;
                    }
                }

                // 其他情况，生成子节点
                if ($c != ':' && $c != '*') {
                    $this->indices .= $c;
                    $otchild = new Node();
                    array_push($this->children, $otchild);
                    $otchild->insertChild($path, $handles);
                    return;
                }
                // 使用剩余路径构造子节点
                $this->insertChild($path, $handles);
                return;
            } else if ($i == strlen($path)) {
                if ($this->handles != null) {
                    throw new Exception('此路由的响应方法已存在，请勿重复添加');
                }
                $this->handles = $handles;
            }
            return;
        }
    }

    /**
     * 获取Handle
     * @param string $path 路由路径
     * @param array $params URL路径中的参数(父级节点)
     * @return array 
     */
    public function getValue($path, $params = [])
    {
        if (strlen($path) < strlen($this->path)) {
            return ['handles' => null, 'params' => $params];
        }
        if ($this->path == $path) {
            return ['handles' => $this->handles, 'params' => $params];
        }
        if (substr($path, 0, strlen($this->path)) != $this->path) {
            return ['handles' => null, 'params' => $params];
        }
        $path  = substr($path, strlen($this->path));
        if (!$this->wildChild) {
            // 子节点不包含参数节点
            $c = $path[0];
            for ($i = 0; $i < strlen($this->indices); $i++) {
                if ($c == $this->indices[$i]) {
                    return $this->children[$i]->getValue($path, $params);
                }
            }
            return ['handles' => null, 'params' => $params];
        }
        // 子节点包含参数
        $child = $this->children[0];
        switch ($child->nType) {
            case PARAM:
                $end = 0;
                while ($end < strlen($path) && $path[$end] != '/') {
                    $end++;
                }
                $params[substr($child->path, 1)] = substr($path, 0, $end);
                if ($end < strlen($path) && count($child->children) > 0) {
                    // 继续遍历子节点
                    $path = substr($path, $end);
                    return $child->children[0]->getValue($path, $params);
                }
                return ['handles' => $child->handles, 'params' => $params];
                break;
            case CATCHALL:
                $params[substr($child->path, 1)] = $path;
                return ['handles' => $child->handles, 'params' => $params];
                break;
            default:
                Logger::error('不支持的参数节点类型');
                return ['handles' => null, 'params' => []];
        }
    }
}
