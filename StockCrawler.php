<?php

// https://github.com/FriendsOfPHP/Goutte
require_once "goutte-v2.0.4.phar";

use Goutte\Client;

/**
 * 株価クローラクラス
 *
 */
class StockCrawler {

    /**
     * コンストラクタ
     */
    public function __construct() {

    }

    /**
     * 現在の株価を取得する
     *
     * @param 整数値 $code 取得する株価コード
     * @return mixed 株価、取得できなかった場合はNULL
     */
    public static function getStockPrice($code) {

        $client = new Client();
        $crawler = $client->request('GET', "http://stocks.finance.yahoo.co.jp/stocks/detail/?code={$code}.T");

        // 2箇所ヒットしてしまう
        $ary = $crawler->filter('td.stoksPrice')->each(function ($element) {
            return $element->text();
        });

        // 絞込
        foreach ($ary as $value) {
            if (trim($value) != "") {
                return $value;
            }
        }

        return NULL;
    }

}

