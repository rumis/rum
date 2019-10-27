<?php

namespace Rum\Test\Controller;

use Rum\Annotation\Controller;
use Rum\Annotation\Router;
use Rum\Annotation\Middleware;
use Rum\Request;
use Rum\Response;

/**
 * @date 2019年10月27日17:39:17
 * @Controller("/user")
 */
class User
{

    public function __construct()
    { }

    /**
     * @Router(path="get",method="POST")
     */
    public function get(Request $req, Response $res)
    {
        $res->string('/user/get');
    }
    /**
     * @Router(path="getitem",method="POST")
     */
    public function getItem(Request $req, Response $res)
    {
        $res->string('/user/getitem');
    }
    /**
     * @Router(path="getparam",method="POST")
     */
    public function getParam(Request $req, Response $res)
    {
        $res->string($req->get('auth'));
    }

    /**
     * @Middleware
     */
    public function auth(Request $req, Response $res)
    {
        $req->set('auth', 'murong');
    }
}
