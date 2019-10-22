<?php

/**
 * 获取一个可用的端口
 */
function get_one_free_port()
{
    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    if (!socket_bind($socket, "0.0.0.0", 0)) {
        return false;
    }
    if (!socket_listen($socket)) {
        return false;
    }
    if (!socket_getsockname($socket, $addr, $port)) {
        return false;
    }
    socket_close($socket);
    return $port;
}
