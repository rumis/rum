<?php

namespace Rum;

const NORMAL = 1; // 普通节点
const ROOT = 2; // 根节点
const PARAM = 4;    // 参数节点
const CATCHALL = 8; // 大参数节点


class Node{

    // 此节点上的URL路径
    public $path;
    // 是不是参数节点
    public $wildChild;
    // 节点类型
    public $nType;
    // 子节点索引
    public $indices;
    // 子节点
    public $children;
    // 处理方法
    public $handle;


    /**
     * 构造函数
     */
    public function __construct(){
        $this->path='';
        $this->wildChild=false;
        $this->nType=NORMAL;
        $this->indices='';
        $this->children=[];
    }

    /**
     * 添加子节点
     * 调用此方法前，节点的path参数为空
     */
    public function insertChild($path,$handle){
        $handleNode=$this;
        $handleNode->path = $path;
        for($i=0,$max=strlen($path);$i<$max;$i++){
            $c = $path[$i];
            // 检测是否包含参数
            if($c!= ':' && $c !='*'){
                continue;
            }
            $end = $i+1;
            while($end<$max && $path[$end]!='/'){
                switch($path[$end]){
                    case ':':
                    case '*':
                        echo '一段路径中只能包含一个参数项';
                        die();
                    default:
                    $end++;
                }
            }
            if(count($this->children)>0){
                echo '参数节点不可包含其他子节点';
                die();
            }
            if($end-$i<2){
                echo '必须存在参数名称';
                die();
            }
            if ($c==':'){
                $this->path= substr($path,0,$i);
                $this->wildChild=true;

                $child = new Node();
                $child->nType=PARAM;
                $child->path=substr($path,$i);
                $this->children=[$child];
                if($end<$max){
                    $child->path=substr($path,$i,$end-$i);
                    $nextChild= new Node();
                    $child->children = [$nextChild];
                    $nextChild->insertChild(substr($path,$end),$handle);
                    return;
                }
                $handleNode=$child;
                break;                
            }else {
                if($end!=$max){
                    echo '*参数只能出现在路由的最后一段';
                    die();
                }
                if(strlen($this->path)>0&&substr($this->path,strlen($this->path)-1)=='/'){
                    echo '和根节点冲突';
                    die();
                }
                // $i--;
                if($path[$i-1]!='/'){
                    echo '*参数前必须为/';
                    die();
                }
                // 当前节点路径
                $this->path= substr($path,0,$i);
                $this->indices = $path[$i];
                $this->wildChild=true;

                $schild = new Node();
                $schild->nType=CATCHALL;
                $schild->path=substr($path,$i);
                $this->children=[$schild];

                $handleNode=$schild;
                break;
            }
            
        } 
        // $handleNode->path=$path;
        $handleNode->handle=$handle;
    }


    /**
     * 添加路由
     */
    public function addRoute($path,$handle){
        if(strlen($this->path)==0&&count($this->children)==0){
            // 空树
            $this->insertChild($path,$handle);
            $this->nType=ROOT;
        }else{
            // 找到新路径和原路径的最小前缀
            $i=0;
            $max = min(strlen($path),strlen($this->path));
            while($i<$max && $path[$i]==$this->path[$i]){
                $i++;
            }
            if($i<strlen($this->path)){
                $child = new Node();
                $child->path=substr($this->path,$i);
                $child->wildChild = $this->wildChild;
                $child->nType=NORMAL;
                $child->indices = $this->indices;
                $child->children=$this->children;
                $child->handle = $this->handle;

                $this->children=[$child];
                $this->indices= $this->path[$i];
                $this->path = substr($path,0,$i);
                $this->handle=null;
                $this->wildChild=false;
            }
            if ($i<strlen($path)){
                $path = substr($path,$i);
                if($this->wildChild){
                    $firstChild=$this->children[0];
                    // 检测是否符合参数节点
                    if(strlen($path)>=strlen($firstChild->path)&&
                    $firstChild->path==substr($path,0,strlen($firstChild->path))&&
                    (strlen($firstChild->path)>=strlen($path)||
                    $path[strlen($firstChild->path)] == '/')){
                        // 符合参数节点
                        $firstChild->addRoute($path,$handle);
                        // continue walk;
                        return;
                    }else{
                        // 参数冲突
                        echo '冲突';
                        die();
                    }
                }
                $c = $path[0];
                if($this->nType==PARAM&&$c=='/'&&count($this->children)==1){
                    $firstChild = $this->children[0];
                    $firstChild->addRoute($path,$handle);
                    // continue walk;
                    return;
                }
                
                for($i=0;$i<strlen($this->indices);$i++){
                    if ($c == $this->indices[$i]){
                        $firstChild = $this->children[$i];
                        $firstChild->addRoute($path,$handle);
                        // continue walk;
                        return;
                    }
                }

                // 其他情况
                if($c!=':'&&$c!='*'){
                    $this->indices .= $c;
                    $otchild= new Node();
                    array_push($this->children,$otchild);
                    $otchild->insertChild($path,$handle);
                    return;
                }
                $this->insertChild($path,$handle);
                return;
            }else if($i==strlen($path)){
                if ($this->handle!=null){
                    echo '关联方法已存在';
                    die();
                }
                $this->handle=$handle;
            }
            return;
        }
    }

    /**
     * 获取Handle
     */
    public function getValue($path,$params=[]){
        if(strlen($path)<strlen($this->path)){
            return ['handle'=>null,'params'=>$params];
        }
        if($this->path==$path){
            return ['handle'=>$this->handle,'params'=>$params];
        }
        if(substr($path,0,strlen($this->path))!=$this->path){
            return ['handle'=>null,'params'=>$params];    
        }
        $path  = substr($path,strlen($this->path));
        if(!$this->wildChild){
            // 子节点不包含参数节点
            $c=$path[0];
            for($i=0;$i<strlen($this->indices);$i++){
                if($c==$this->indices[$i]){
                    return $this->children[$i]->getValue($path,$params);
                }
            }
            return ['handle'=>null,'params'=>$params]; 
        }
        // 子节点包含参数
        $child = $this->children[0];
        switch($child->nType){
            case PARAM:
                $end=0;
                while($end<strlen($path)&&$path[$end]!='/'){
                    $end++;
                }
                $params[substr($child->path,1)]=substr($path,0,$end);
                if($end<strlen($path)&&count($child->children)>0){
                    // 继续遍历子节点
                    $path=substr($path,$end);
                    return $child->children[0]->getValue($path,$params);
                }
                return ['handle'=>$child->handle,'params'=>$params]; 
                break;
            case CATCHALL:
                $params[substr($child->path,1)]=$path;
                return ['handle'=>$child->handle,'params'=>$params]; 
                break;
            default:
                echo '不支持的参数节点类型';
                return ['handle'=>null,'params'=>[]]; 
        }
    }
}
