<?php

namespace Rum\Test\Controller\Teacher;

use Rum\Annotation\Controller;
use Rum\Annotation\Router;
use Rum\Request;
use Rum\Response;

/**
 * @date 2019年10月27日17:38:05
 * @Controller("/teacher/counselor")
 */
class Counselor
{

    public function __construct()
    {
    }
    /**
     * @Middleware
     */
    public function auth(Request $req, Response $res)
    {
        $req->set('auth', 'murong');
    }
    /**
     * @Router(path="get",method="POST")
     */
    public function get(Request $req, Response $res)
    {
        $res->string('/teacher/counselor/get');
    }
    /**
     * @Router(path="get/:item",method="POST,GET")
     */
    public function getItem(Request $req, Response $res)
    {
        $res->string($req->params('item'));
    }
}
