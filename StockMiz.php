<?php

/**
 * Miz企画 株価クラス
 *
 */
class StockMiz
{
    /**
     * DB接続文字列
     *
     * @var string
     */
    private $pdoConnect = "sqlite:stock.db";

    /**
     * 株価取得用ステートメント
     *
     * @var PDOStatement
     */
    private $stmt = null;

    /**
     * コンストラクタ
     */
    public function __construct() {

    }

    /**
     * 株価DBを作成する
     *
     * @return boolean 成功 true、失敗 false
     */
    public function createDB() {

        $db = null;

        try {

            $db = new PDO($this->pdoConnect);

            $sql =
                "CREATE TABLE stock_miz( " .
                "  'code' TEXT , " .
                "  'date' TEXT , " .
                "  'market' TEXT, " .
                "  'stock_name' TEXT, " .
                "  'opening_price' REAL, " .
                "  'high_price' REAL, " .
                "  'low_price' REAL, " .
                "  'closing_price' REAL, " .
                "  'volume' INTEGER, " .
                "  'unit_shares' INTEGER, " .               // 単元株数
                "  'unit_volume' INTEGER, " .               // 単元出来高
                "  'per' REAL, " .                          // PER
                "  'pbr' REAL, " .                          // PBR
                "  'avg5days' REAL, " .                     // 5日平均
                "  'avg25days' REAL, " .                    // 25日平均
                "  'avg75days' REAL, " .                    // 75日平均
                "  'avg5days_updown' TEXT, " .              // 5日平均上下
                "  'avg25days_updown' TEXT, " .             // 25日平均上下
                "  'avg75days_updown' TEXT, " .             // 75日平均上下
                "  'volume_rate' REAL, " .                  // 出来高増減率
                "  'before_5days_updown' REAL, " .          // 5日前からの上下率
                "  'before_closing_price' REAL, " .         // 前日終値
                "  PRIMARY KEY(code, date)) ";

            $db->query($sql);

            // db close
            $db = null;

            return true;
        }
        catch (pdoexception $e) {
            var_dump($e);

            if ($db) {
                $db->rollBack();
            }

            $db = null;

            return false;
        }
    }

    /**
     * 株価データを追加する
     *
     * @param DateTime $dateObj 追加する日付
     * @return boolean 成功 true、失敗 false
     */
    public function add($dateObj) {

        if ( ! $dateObj instanceof DateTime ) {
            return false;
        }

        $date = $dateObj->format(Common::MIZ_DATE_FORMAT);

        $db = null;

        try {

            $db = new PDO($this->pdoConnect);

            // 存在チェック
            $sql = "SELECT COUNT(code) AS ID FROM stock_miz WHERE date = '$date'";
            $stmt = $db->query($sql);
            $result = $stmt->fetch(PDO::FETCH_BOTH);

            // DBに存在しないので追加する
            if ($result[0] == 0) {

                // CSVファイルパス作成
                $csvFilePath = Common::MIZ_ZIP_FILE_PATH . sprintf(Common::MIZ_STOCK_CSV_FILE_NAME, $date);
                $str = file_get_contents($csvFilePath);

                // "SJIS" -> "UTF-8"
                $encodingStr = mb_convert_encoding($str, "UTF-8", "SJIS");
                $parseLine = explode("\r\n", $encodingStr);
                $lineCount = count($parseLine);

                $db->beginTransaction();

                $sql =
                    "INSERT INTO stock_miz(" .
                    "  code, " .
                    "  date, " .
                    "  market, " .
                    "  stock_name, " .
                    "  opening_price, " .
                    "  high_price, " .
                    "  low_price, " .
                    "  closing_price, " .
                    "  volume, " .
                    "  unit_shares, " .
                    "  unit_volume, " .
                    "  per, " .
                    "  pbr, " .
                    "  avg5days, " .
                    "  avg25days, " .
                    "  avg75days, " .
                    "  avg5days_updown, " .
                    "  avg25days_updown, " .
                    "  avg75days_updown, " .
                    "  volume_rate, " .
                    "  before_5days_updown, " .
                    "  before_closing_price) " .
                    "VALUES( " .
                    "  ?, " .
                    "  '$date', " .
                    "  ?, " .
                    "  ?, " .
                    "  ?, " .
                    "  ?, " .
                    "  ?, " .
                    "  ?, " .
                    "  ?, " .
                    "  ?, " .
                    "  ?, " .
                    "  ?, " .
                    "  ?, " .
                    "  ?, " .
                    "  ?, " .
                    "  ?, " .
                    "  ?, " .
                    "  ?, " .
                    "  ?, " .
                    "  ?, " .
                    "  ?, " .
                    "  ?) ";

                $prepare = $db->prepare($sql);

                // スルー。1行目(項目名), 最終行(空行)
                for ($i = 1; $i < $lineCount - 1; $i++) {

                    $parseCsv = str_getcsv($parseLine[$i], ",");

                    // 値
                    $prepare->execute($parseCsv);
                }

                $db->commit();
            }

            $db = null;

            return true;
        }
        catch (pdoexception $e) {
            var_dump($e);

            if ($db) {
                $db->rollBack();
            }

            $db = null;

            return false;
        }
    }


}


