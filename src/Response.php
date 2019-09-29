<?php

namespace Rum;

class Response{
    
    private $res; // swoole中的response对象

    public function __construct($res){
        $this->res=$res;
    }

    public function write(){

    }

    public function end($item){
        $this->res->end($item);
    }

    public function status($code){

    }

    public function header($key,$value){

    }

    /**
     * 设置Cookie
     * 
     * @param string $opts.name 
     * @param string $opts.value
     * @param int $opts.maxage
     * @param string $opts.path
     * @param string $opts.domain
     * @param bool $opts.secure
     * @param bool $opts.httponly
     */
    public function cookie($opts){

    }

    public function html($value,$code=200){

    }

    public function json($value,$code=200){

    }

    public function xml($value,$code=200){

    }

    public function string($value,$code=200){

    }


    public function redirect(){

    }

    public function file($filepath){

    }

}