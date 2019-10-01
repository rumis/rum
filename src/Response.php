<?php

namespace Rum;

/**
 * HTTP响应对象
 */
class Response{
    
    private $res; // swoole中的response对象

    public function __construct($res){
        $this->res=$res;
    }

    /**
     * 向浏览器写入内容
     * 可以多次调用
     * 发送数据大小受参数buffer_output_size影响
     * @param {mixed} $data
     * @return
     */
    public function write($data){
        return $this->res->write($data);
    }

    /**
     * 发送内容并结束处理
     * @param {mixed} $item
     * @return 
     */
    public function end($item){
        $this->res->end($item);
    }

    /**
     * 设置http响应状态码
     * 必须为合法的HttpCode，否则会报错
     * 需要在end之前配置
     * @param {int} $code
     * @return
     */
    public function status($code){
        return $this->status($code);
    }

    /**
     * 设置Http响应的Header信息
     * swoole底层不允许设置相同key的header
     * @param {string} $key Header的Key
     * @param {string} $value Header的Value
     * @return
     */
    public function header($key,$value){
        return $this->res->header($key,$value);
    }

    /**
     * 设置Cookie
     * cookie设置必须在end方法之前
     * @param {string} $name 
     * @param {string} $value
     * @param {int} $maxage
     * @param {string} $path
     * @param {string} $domain
     * @param {bool} $secure
     * @param {bool} $httponly
     */
    public function cookie($name,$value='',$maxage=0,$path='/',$domain='',$secure=false,$httponly=false){
        return $this->res->cookie($name,$value,empty($maxage)?0:time()+$maxage,$path,$domain,$secure,$httponly);
    }
    /**
     * 发送html到浏览器
     * @param {string} $value 内容
     * 
     * @return mixed
     */
    public function html($value,$code=200){
        $this->header(Header::CONTENTTYPE,Mime::HTML);
        $this->status($code);
        return $this->end($value);
    }
    /**
     * 发送json对象到浏览器
     * @param {array} $value 内容
     * 
     * @return mixed
     */
    public function json($value,$code=200){
        $this->header(Header::CONTENTTYPE,Mime::JSON);
        $this->status($code);
        return $this->end(json_encode($value));
    }
    /**
     * 发送XML到浏览器
     * @param {string} $value 内容
     * 
     * @return mixed
     */
    public function xml($value,$code=200){
        $this->header(Header::CONTENTTYPE,Mime::XML);
        $this->status($code);
        return $this->end($value);
    }

    /**
     * 发送字符串到浏览器
     * @param {string} $value 内容
     * 
     * @return mixed
     */
    public function string($value,$code=200){
        $this->header(Header::CONTENTTYPE,Mime::PLAIN);
        $this->status($code);
        return $this->end($value);
    }

    /**
     * 重定向
     * 调用此方法会自动end并结束响应
     * @param {string} $url 需要重定向到的网址 
     * @param {301|302} $code，默认302
     * @return
     */
    public function redirect($url,$code=302){
        return $this->res->redirect($url,$code);
    }

    /**
     * 发送文件到浏览器
     * 文件不存在或者没有访问权限会失败
     * 需要显式指定Content-Type值
     * 此方法和Write互斥。
     * @param {string} $filepath 文件路径
     * @return
     */
    public function file($filepath){
        return $this->res->sendfile($filepath);
    }

}