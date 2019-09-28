<?php

namespace Rum;

class Util{

    public static function path_join($base,$path){
        if(self::path_is_absolute($path)){
            return $path;
        }
        return rtrim($base,'/').'/'. ltrim($path,'/');
    }

    public static function path_is_absolute($path){
        if ((is_dir($path)||is_file($path))){
            return true;
        }
        if (realpath($path)==$path){
            return true;
        }
        if (strlen($path) == 0 || $path[0] == '.'){
            return false;
        }
 
        if (preg_match('#^[a-zA-Z]:\\\\#',$path)){
            return true;
        } 
        return ( $path[0] == '/' || $path[0] == '\\' );
    }
}
