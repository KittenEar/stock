<?php

require_once "autoload.php";

/**
 * Miz企画 株価情報ファイル取得クラス
 *
 */
class StockMizDownloader
{
    /**
     * コンストラクタ
     */
    public function __construct() {

        if ( ! file_exists(Common::MIZ_ZIP_FILE_PATH) ) {
            // ファイル保存フォルダ作成
            mkdir(Common::MIZ_ZIP_FILE_PATH, 0755);
        }
    }

    /**
     * 指定された日付の株価ZIPファイルをダウンロードする
     *
     * @param DateTime $dateObj ダウンロードするファイルの日付
     * @return boolean 成功 true、失敗 false
     */
    public function downloadZipFile($dateObj) {

        // 引数チェック
        if ( ! $dateObj instanceof DateTime ) {
            return false;
        }

        $ymdDate = $dateObj->format(Common::MIZ_DATE_FORMAT);
        $filename = sprintf(Common::MIZ_STOCK_ZIP_FILE_NAME, $ymdDate);
        $filepath = Common::MIZ_ZIP_FILE_PATH . $filename;
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

        // ZIPファイルダウンロード
        $url = sprintf(Common::MIZ_STOCK_ZIP_URL, $ymdDate);
        $str = file_get_contents($url);

        // Debug::logPrintR($http_response_header);

        if ( ! $str ) {

            if ($http_response_header) {
                // Miz企画ページの構成上、404に該当するURLを指定するとリダイレクト(302)される
                // http_response_header を走査すれば 404 は検出できるが
                // 簡易的に 302 を "not found" とみなす
                if (strpos($http_response_header[0], "302")) {
                    Log::error("ZIPファイル ダウンロード失敗 祝日の可能性大 {$ymdDate}");
                    return false;
                }
            }

            Log::error("ZIPファイル ダウンロード失敗");
            return false;
        }

        // ZIPファイル保存
        $ret = file_put_contents($filepath, $str);

        if ( ! $ret ) {
            Log::error("ZIPファイル 保存に失敗");
            return false;
        }

        $zip = new ZipArchive;

        if ($zip->open($filepath) !== true) {

            Log::error("ZIP open 失敗");
            return false;
        }

        $zip->extractTo(Common::MIZ_ZIP_FILE_PATH);
        $zip->close();

        return true;
    }


}


