<?php

// ログを出力する
ini_set('log_errors', 1);

class Log
{
    public static function error($message) {
        error_log($message . PHP_EOL, 3, "./php_error.log");
    }
}

