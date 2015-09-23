<?php

require_once "autoload.php";

/**
 * 株価共通クラス
 *
 */
final class Common
{
    /**
     * 株価CSVファイル取得URL
     *
     * @var string
     */
    const STOCK_CSV_URL = "http://k-db.com/stocks/%s?download=csv";

    /**
     * CSV保存パス
     *
     * @var string
     */
    const CSV_FILE_PATH = './stocks/';

    /**
     * CSV保存ファイル名
     *
     * @var string
     */
    const STOCK_CSV_FILE_NAME = "stocks_%s.csv";

    /**
     * 日付フォーマット
     *
     * @var string
     */
    const DATE_FORMAT = 'Y-m-d';        // ex)2015-01-31

    /**
     * コンストラクタ
     */
    private function __construct() {
    }



}


