<?php

namespace Rum;


class Request{

    private $req;

    public function __construct($req){
        $this->req=$req;
    }

    /**
     * 本次请求的方法
     * @author huanjiesm
     */
    public function method(){
        return "GET";
    }
    /**
     * 请求的Path
     * @author huanjiesm
     */
    public function path(){
        return '/test';
    }

    /**
     * 缓存数据
     * 一般用于插件和路由Handle之间传递数据
     * @author huanjiesm
     */
    public function set($key,$val){

    }
    /**
     * 获取缓存的数据
     * @author huanjiesm
     */
    public function get($key){

    }
    /**
     * URL参数
     * :,*
     * @author huanjiesm
     */
    public function params($key=null){

    }
    /**
     * 查询参数
     * URL?后携带
     * @author huanjiesm
     */
    public function query($key=null){

    }
    /**
     * 获取请求体数据
     * @author huanjiesm
     */
    public function body($key=null){

    }
    /**
     * 获取表单中包含的文件
     * @author huanjiesm
     */
    public function file($key=null){

    }
    /**
     * 获取请求内容的二进制数据库
     * @author huanjiesm
     */
    public function raw(){

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

    }
    /**
     * 获取请求数据类型
     * @author huanjiesm
     */
    public function contentType(){

    }
    /**
     * 获取请求的Header值
     * @author huanjiesm
     */
    public function header($key=null){

    }
    /**
     * 获取请求包含的cookie值
     * @author huanjiesm
     */
    public function cookie($name=null){

    }

}