<?php

namespace Rum;

/**
 * body解析
 * 目前只实现json解析
 */
function BodyParser()
{
    return function (Request &$req, Response &$res) {
        if ($req->contentType() == Mime::JSON) {
            $raw = $req->raw();
            if (!empty($raw)) {
                $data = json_decode($raw, true);
                $req->setBody($data);
            }
        }
    };
}
