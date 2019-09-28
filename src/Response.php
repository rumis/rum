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

}