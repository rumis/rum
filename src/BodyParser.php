<?php

namespace Rum;

/**
 * body解析
 * 目前只实现json解析
 */
class BodyParser implements IMiddleware{
    public function invoke(Request &$req, Response &$res){
        $raw = $req->raw();
        if(!empty($raw)||$req->contentType==Mime::JSON){
            $data = json_decode($raw);
            $req->setBody($data);
        }
    }
}