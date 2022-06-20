<?php

spl_autoload_register(function($className){
    $realPath ='Application/';
    $className = str_replace('\\','/',$className);
    require_once($realPath . $className . '.php');
});