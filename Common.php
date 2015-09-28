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
     * CSVファイル保存パス
     *
     * @var string
     */
    const CSV_FILE_PATH = './stocks/';

    /**
     * CSVファイル保存名
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
     * Miz企画サイト 株価ZIPファイル取得URL
     *
     * @var string
     */
    const MIZ_STOCK_ZIP_URL = "http://mizkikaku.web.fc2.com/data/kabu%s.zip";

    /**
     * Miz企画サイト ZIPファイル保存パス
     *
     * @var string
     */
    const MIZ_ZIP_FILE_PATH = './miz_stocks/';

    /**
     * Miz企画サイト ZIPファイル保存名
     *
     * @var string
     */
    const MIZ_STOCK_ZIP_FILE_NAME = "kabu%s.zip";

    /**
     * Miz企画サイト ZIPファイル展開されたCSVファイル保存名
     *
     * @var string
     */
    const MIZ_STOCK_CSV_FILE_NAME = "kabu%s.csv";

    /**
     * Miz企画サイト 日付フォーマット
     *
     * @var string
     */
    const MIZ_DATE_FORMAT = 'ymd';      // ex)150131

    /**
     * コンストラクタ
     */
    private function __construct() {
    }



}


