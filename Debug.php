<?php

/**
 * デバッグ用クラス
 */
class Debug
{
    /**
     * ログ出力 print_r
     *
     * @param mixed $var 出力変数
     */
    public static function logPrintR($var)
    {
        echo "<pre>";
        Debug::outputFileLine(1);
        print_r($var);
        echo "</pre>";
    }

    /**
     * ログ出力 var_dump
     *
     * @param mixed $var 出力変数
     */
    public static function logVarDump($var)
    {
        echo "<pre>";
        Debug::outputFileLine(1);
        var_dump($var);
        echo "</pre>";
    }

    /**
     * ファイルパスと行数を出力する
     *
     * @param 数値 $traceCount メソッドの呼び出し元をいくつ遡るか
     */
    private static function outputFileLine($traceCount)
    {
        $backtrace = debug_backtrace();
        echo "{$backtrace[$traceCount]['file']} {$backtrace[$traceCount]['line']}\n";
    }

}





