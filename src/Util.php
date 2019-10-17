<?php

namespace Rum;

/**
 * 工具类
 */
class Util
{

    /**
     * 组合两个路径
     * @param string $base 路径1
     * @param string $path 路径2
     * @return string
     */
    public static function path_join($base, $path)
    {
        if (self::path_is_absolute($path)) {
            return $path;
        }
        return rtrim($base, '/') . '/' . ltrim($path, '/');
    }
    /**
     * 给定路径是否为绝对路径
     * @param string $path 路径
     * @return boolean 
     */
    public static function path_is_absolute($path)
    {
        if ((is_dir($path) || is_file($path))) {
            return true;
        }
        if (realpath($path) == $path) {
            return true;
        }
        if (strlen($path) == 0 || $path[0] == '.') {
            return false;
        }

        if (preg_match('#^[a-zA-Z]:\\\\#', $path)) {
            return true;
        }
        return ($path[0] == '/' || $path[0] == '\\');
    }
}
