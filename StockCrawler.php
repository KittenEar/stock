<?php

// https://github.com/FriendsOfPHP/Goutte
require_once "goutte-v2.0.4.phar";

use Goutte\Client;

/**
 * 株価クローラクラス
 *
 */
class StockCrawler {

    private $nowPrice;              // 現在値
    private $beforeClosingPrice;    // 前日終値
    private $openingPrice;          // 始値
    private $highPrice;             // 高値
    private $lowPrice;              // 安値

    /**
     * コンストラクタ
     */
    public function __construct() {

    }

    /**
     * 現在の株価を取得する
     *
     * @param 整数値 $code 取得する株価コード
     * @return bool true 成功
     */
    public function getStockPrice($code) {

        try {

            $client = new Client();
            $crawler = $client->request('GET', "http://stocks.finance.yahoo.co.jp/stocks/detail/?code={$code}.T");

            // 前日終値、始値、高値、安値を取得する
            $array = $crawler->filter('dd')->each(function ($element) {
                $array = $element->filter('strong')->each(function ($element) {
                    return $element->text();
                });

                // Debug::logPrintR($array);
                return count($array) > 0 ? $array[0] : null;
            });

            // Debug::logPrintR($array);

            // $array の要素は以下の様になっている
            // [2] 前日終値
            // [3] 始値
            // [4] 高値
            // [5] 安値
            if (count($array) >= 6) {
                $this->beforeClosingPrice = $this->removeComma($array[2]);
                $this->openingPrice = $this->removeComma($array[3]);
                $this->highPrice = $this->removeComma($array[4]);
                $this->lowPrice = $this->removeComma($array[5]);
            }

            // 現在値を取得する
            $element = $crawler->filter('td.stoksPrice')->last();
            $this->nowPrice = $this->removeComma($element->text());

        }
        catch (exception $e) {
            // Debug::logPrintR($e);
            return false;
        }

        return true;
    }

    public function getNowPrice() {
        return $this->nowPrice;
    }
    public function getBeforeClosingPrice() {
        return $this->beforeClosingPrice;
    }
    public function getOpeningPrice() {
        return $this->openingPrice;
    }
    public function getHighPrice() {
        return $this->highPrice;
    }
    public function getLowPrice() {
        return $this->lowPrice;
    }

    private function removeComma($value) {
        return str_replace(",", "", $value);
    }
}

