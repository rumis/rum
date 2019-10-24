<?php

namespace Rum;

/**
 * HTTP响应对象
 */
class Response
{
    private $res; // swoole中的response对象
    private $droped;

    /**
     * 响应对象
     * @param Http\Response $res  swoole中的response对象
     * @return
     */
    public function __construct($res)
    {
        $this->res = $res;
        $this->droped = false;
    }

    /**
     * 向浏览器写入内容
     * 可以多次调用
     * 发送数据大小受参数buffer_output_size影响
     * @param {mixed} $data 内容
     * @return 
     */
    public function write($data)
    {
        return $this->res->write($data);
    }

    /**
     * 发送内容并结束处理,整个生命周期中只可以调用一次
     * @param {mixed} $item 内容
     * @return 
     */
    public function end($item)
    {
        return $this->res->end($item);
    }

    /**
     * 设置http响应状态码
     * 必须为合法的HttpCode，否则会报错
     * 需要在end之前配置
     * @param {int} $code 状态码
     * @return
     */
    public function status($code)
    {
        return $this->res->status($code);
    }

    /**
     * 设置Http响应的Header信息
     * swoole底层不允许设置相同key的header
     * @param {string} $key Header的Key
     * @param {string} $value Header的Value
     * @return
     */
    public function header($key, $value)
    {
        return $this->res->header($key, $value);
    }

    /**
     * 设置Cookie
     * cookie设置必须在end方法之前
     * @param {string} $name  键
     * @param {string} $value 值
     * @param {int} $maxage 生命周期
     * @param {string} $path 路径
     * @param {string} $domain 域
     * @param {bool} $secure 只有https协议才允许发送
     * @param {bool} $httponly 是否只允许http发送，js不可读取cookie内容
     * @return
     */
    public function cookie($name, $value = '', $maxage = 0, $path = '/', $domain = '', $secure = false, $httponly = false)
    {
        return $this->res->cookie($name, $value, empty($maxage) ? 0 : time() + $maxage, $path, $domain, $secure, $httponly);
    }
    /**
     * 发送html到浏览器
     * @param {string} $value 内容
     * @param {int} $code 状态码，默认好评
     * @return mixed
     */
    public function html($value, $code = 200)
    {
        $this->header(Header::CONTENTTYPE, Mime::HTML);
        $this->status($code);
        return $this->end($value);
    }
    /**
     * 发送json对象到浏览器
     * @param {array} $value 内容
     * @param {int} $code 状态码，默认200
     * @return mixed
     */
    public function json($value, $code = 200)
    {
        $this->header(Header::CONTENTTYPE, Mime::JSON);
        $this->status($code);
        return $this->end(json_encode($value));
    }
    /**
     * 发送XML到浏览器
     * @param {string} $value 内容
     * @param {int} $code 状态码，默认200
     * @return mixed
     */
    public function xml($value, $code = 200)
    {
        $this->header(Header::CONTENTTYPE, Mime::XML);
        $this->status($code);
        return $this->end($value);
    }

    /**
     * 发送字符串到浏览器
     * @param {string} $value 内容
     * @param {int} $code 状态码，默认200
     * @return mixed
     */
    public function string($value, $code = 200)
    {
        $this->header(Header::CONTENTTYPE, Mime::PLAIN);
        $this->status($code);
        return $this->end($value);
    }

    /**
     * 重定向
     * 调用此方法会自动end并结束响应
     * @param {string} $url 需要重定向到的网址 
     * @param {301|302} $code 状态码，默认302
     * @return
     */
    public function redirect($url, $code = 302)
    {
        return $this->res->redirect($url, $code);
    }

    /**
     * 发送文件到浏览器
     * 文件不存在或者没有访问权限会失败
     * 需要显式指定Content-Type值
     * 此方法和Write互斥。
     * @param {string} $filepath 文件路径
     * @return
     */
    public function file($filepath)
    {
        return $this->res->sendfile($filepath);
    }

    /**
     * 放弃整个请求流程
     * 一般用于中间件中，放弃后返回前端的内容由中间件本身确定。
     * @return void
     */
    public function abort()
    {
        $this->droped = true;
    }

    /**
     * 当前流程是否已经被放弃
     * @return boolean
     */
    public function aborted()
    {
        return $this->droped;
    }
}
