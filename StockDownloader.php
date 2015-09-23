<?php

require_once "autoload.php";

/**
 * 株価CSVファイル取得クラス
 *
 */
class StockDownloader
{
    /**
     * ファイル取得URL
     *
     * @var string
     */
    private $urlFormat = "http://k-db.com/stocks/%s?download=csv";

    private $saveFolder = './stocks/';

    private $dateFormat = 'Y-m-d';

    /**
     * コンストラクタ
     */
    public function __construct() {

        if ( ! file_exists($this->saveFolder) ) {
            // ファイル保存フォルダ作成
            mkdir($this->saveFolder, 0755);
        }
    }

    /**
     * 指定された日付の株価CSVファイルをダウンロードする
     *
     * @param DateTime $dateObj ダウンロードするファイルの日付
     * @return boolean 成功 true、失敗 false
     */
    public function downloadCsvFile($dateObj) {

        // 引数チェック
        if ( ! $dateObj instanceof DateTime ) {
            return false;
        }

        $ymdDate = $dateObj->format(Common::DATE_FORMAT);
        $filename = sprintf(Common::STOCK_CSV_FILE_NAME, $ymdDate);
        $filepath = $this->saveFolder . $filename;
        $week = $dateObj->format("w");      // 曜日(0:日曜 〜 6:土曜)

        // 土日チェック
        if ($week === "0" || $week === "6") {
            Log::error("日曜または土曜の日付 {$ymdDate}");
            return false;
        }

        // ファイル名存在チェック
        if (file_exists($filepath)) {
            Log::error("既にダウンロードしている {$filename}");
            // 成功したとみなす
            return true;
        }

        // CSVファイルダウンロード
        $url = sprintf($this->urlFormat, $ymdDate);
        $str = file_get_contents($url);

        if ( ! $str ) {
            Log::error("CSVファイル ダウンロードに失敗");
            return false;
        }

        // [ダウンロードサイト(k-db.com)の仕様]
        // ダウンロードファイルが存在しないURLを指定すると、
        // 現在日のファイルがダウンロードされてしまう。
        // CSVファイルの一行目に年月日が記載されているのを使用してチェックする。
        $parseLine = explode("\r\n", $str);
        $encodingFirstLine = mb_convert_encoding($parseLine[0], "UTF-8", "SJIS");
        $csvDate = DateTime::createFromFormat("Y年m月d日", $encodingFirstLine);
        $checkDate = $csvDate->format(Common::DATE_FORMAT);

        // 引数の年月日とダウンロードファイルの年月日が一致しない
        if ($ymdDate !== $checkDate) {
            Log::error("年月日が一致しない {$ymdDate} {$checkDate}");
            return false;
        }

        // CSVファイル保存
        $ret = file_put_contents($filepath, $str);

        if ( ! $ret ) {
            Log::error("CSVファイル 保存に失敗");
            return false;
        }

        return true;
    }


}


