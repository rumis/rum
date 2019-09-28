<?php

namespace Rum;

interface IMiddleware{
    public function invoke(Request $req, Response $res);
}