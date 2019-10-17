<?php

namespace Rum;

/**
 * HTTP请求对象
 */
class Request
{
    private $req;
    private $param;
    private $data;
    private $cache;

    /**
     * 请求对象
     * @param Http\Request $req swoole原生对象
     * @param array $param URL中的参数
     * @return
     */
    public function __construct($req, $param = [])
    {
        $this->req = $req;
        $this->param = $param;
        $this->cache = [];
        $this->data = $req->post;
    }

    /**
     * 本次请求的方法
     * @return string
     */
    public function method()
    {
        return $this->req->server['request_method'];
    }
    /**
     * 请求的Path
     * @return string
     */
    public function path()
    {
        return $this->req->server['request_uri'];
    }

    /**
     * 缓存数据
     * 一般用于插件和路由Handle之间传递数据
     * 如果数据量较大，建议使用其他方式
     * @param string $key 
     * @param mixed $val 内容
     * @return void
     */
    public function set($key, $val)
    {
        $this->cache[$key] = $val;
    }
    /**
     * 获取缓存的数据,key不存在返回空字符串
     * @param string $key 
     * @return mixed
     */
    public function get($key)
    {
        return empty($this->cache[$key]) ? '' : $this->cache[$key];
    }
    /**
     * 获取URL参数,不存在返回空字符串
     * 不传参数返回所有值
     * @param string $key
     * @return string
     */
    public function params($key = null)
    {
        if (empty($key)) {
            return $this->param;
        }
        return empty($this->param[$key]) ? '' : $this->param[$key];
    }
    /**
     * 设置URL中的参数
     * @param array $param URL中的参数
     * @return void
     */
    public function setParams($param = [])
    {
        $this->param = $param;
    }
    /**
     * 查询参数,URL?后携带的参数
     * @param null|string $key 
     * @return mixed
     */
    public function query($key = null)
    {
        if (empty($key)) {
            return $this->req->get;
        }
        return empty($this->req->get[$key]) ? '' : $this->req->get[$key];
    }
    /**
     * 获取请求体数据
     * @param null|string $key
     * @return mixed
     */
    public function body($key = null)
    {
        if (empty($key)) {
            return $this->data;
        }
        return empty($this->data[$key]) ? '' : $this->data[$key];
    }

    /**
     * 设置新的Body参数
     * 一般用于非表单类型的post数据解析
     * @param array $data 请求参数内容
     * @return void
     */
    public function setBody($data)
    {
        $this->data = $data;
    }

    /**
     * 获取表单中包含的文件
     * @param string $key 文件字段名称
     * @return array 上传文件结构
     */
    public function file($key = '')
    {
        return empty($this->req->files[$key]) ? [] : $this->req->files[$key];
    }
    /**
     * 获取请求内容的二进制数据库
     * @return byte[]
     */
    public function raw()
    {
        return $this->req->rawContent();
    }
    /**
     * 直接将表单中上传的数据保存到本地
     * @param string $key 文件字段名称
     * @param string $des 目标路径
     */
    public function saveUploadFile($key, $des)
    { }
    /**
     * 获取请求客户端的真实IP地址
     * 过滤下代理
     * @return string 
     */
    public function ip()
    {
        $forwarded = $this->header('X-Forwarded-For');
        if (!empty($forwarded)) {
            $arr = explode(',', $forwarded);
            if (count($arr) > 0) {
                return trim($arr[0]);
            }
        }
        $realIP = $this->header('X-Real-Ip');
        if (!empty($realIP)) {
            return trim($realIP);
        }
        return $this->req->server['remote_addr'];
    }
    /**
     * 获取请求数据类型
     * @return string
     */
    public function contentType()
    {
        return $this->header(Header::CONTENTTYPE);
    }
    /**
     * 获取请求的Header值
     * @param string $key header的key
     * @return string
     */
    public function header($key = '')
    {
        $key = strtolower($key); // swoole要求所有key均为小写;
        return empty($this->req->header[$key]) ? '' : $this->req->header[$key];
    }
    /**
     * 获取请求包含的cookie值
     * @param string $name 键
     * @return string 
     */
    public function cookie($name = '')
    {
        return empty($this->req->cookie[$name]) ? '' : $this->req->cookie[$name];
    }
}
