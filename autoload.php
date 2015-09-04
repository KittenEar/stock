<?php

/**
 * クラスのオートローディング
 */
function __autoload($name)
{
    // 先頭文字を小文字へ変換
    $file = lcfirst($name . '.php');

    if (is_readable($file)) {
        require_once $file;
    }
}

