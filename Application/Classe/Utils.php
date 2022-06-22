<?php
namespace Classe;

class Utils{
    public static function fileExist(string $path):bool{
        if(preg_match('(https|http|ftp)',$path)){
           return preg_match('(200 OK)',get_headers($path)[0]);
        }
        return file_exists($path);
    }
}