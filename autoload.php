<?php

/**
 * クラスのオートローディング
 */
// function __autoload($name)
// {
//     $file = $name . '.php';
//
//     if (is_readable($file)) {
//         require_once $file;
//     }
// }

function stockAutoloader($name) {
    include $name . '.php';
}

spl_autoload_register('stockAutoloader');

