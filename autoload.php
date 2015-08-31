<?php

/**
 * クラスのオートローディング
 */
function __autoload($name)
{
    $file = $name . '.php';
    if (is_readable($file)) {
        require_once $file;
    }
}

