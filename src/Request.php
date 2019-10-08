<?php

namespace Rum;


class Request{

    private $req;
    private $param;
    private $data;
    private $cache;

    public function __construct($req,$param=[]){
        $this->req=$req;
        $this->param=$param;
        $this->cache=[];
        $this->data=$req->post;
    }

    /**
     * 本次请求的方法
     * @author huanjiesm
     */
    public function method(){
        return $this->req->server['request_method'];
    }
    /**
     * 请求的Path
     * @author huanjiesm
     */
    public function path(){
        return $this->req->server['request_uri'];
    }

    /**
     * 缓存数据
     * 一般用于插件和路由Handle之间传递数据
     * @author huanjiesm
     */
    public function set($key,$val){
        $this->cache[$key]=$val;
    }
    /**
     * 获取缓存的数据
     * @author huanjiesm
     */
    public function get($key){
        return empty($this->cache[$key])?'':$this->cache[$key];
    }
    /**
     * URL参数
     * :,*
     * @author huanjiesm
     */
    public function params($key=''){
        return empty($this->req->param[$key])?'':$this->req->param[$key];
    }
    /**
     * 查询参数
     * URL?后携带
     * @author huanjiesm
     */
    public function query($key=null){
        if(empty($key)){
            return $this->req->get;
        }
        return empty($this->req->get[$key])?'':$this->req->get[$key];
    }
    /**
     * 获取请求体数据
     * @author huanjiesm
     */
    public function body($key=null){
        if(empty($key)){
            return $this->data;
        }
        return empty($this->data[$key])?'':$this->data[$key];
    }

    /**
     * 设置新的Body参数
     * 一般用于非表单类型的post数据解析
     */
    public function setBody($data){
        $this->data=$data;
    }

    /**
     * 获取表单中包含的文件
     * @author huanjiesm
     */
    public function file($key=''){
        return empty($this->req->files[$key])?[]:$this->req->files[$key];
    }
    /**
     * 获取请求内容的二进制数据库
     * @author huanjiesm
     */
    public function raw(){
        return $this->req->rawContent();
    }
    /**
     * 直接将表单中上传的数据保存到本地
     * @author huanjiesm
     */
    public function saveUploadFile($key,$des){

    }
    /**
     * 获取请求客户端的真实IP地址
     * @author huanjiesm
     */
    public function ip(){

        $forwarded = $this->header('X-Forwarded-For');
        if(!empty($forwarded)){
            $arr = explode(',',$forwarded);
            if(count($arr)>0){
                return trim($arr[0]);
            }
        }
        $realIP = $this->header('X-Real-Ip');
        if(!empty($realIP)){
            return trim($realIP);
        }
        return $this->req->server['remote_addr'];
    }
    /**
     * 获取请求数据类型
     * @author huanjiesm
     */
    public function contentType(){
        return $this->header(Header::CONTENTTYPE);
    }
    /**
     * 获取请求的Header值
     * @author huanjiesm
     */
    public function header($key=''){
        $key = strtolower($key);// swoole要求所有key均为小写;
        return empty($this->req->header[$key])?'':$this->req->header[$key];
    }
    /**
     * 获取请求包含的cookie值
     * @author huanjiesm
     */
    public function cookie($name=''){
        return empty($this->req->cookie[$name])?'':$this->req->cookie[$name];
    }

}