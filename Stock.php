<?php

/**
 * 株価クラス
 *
 */
class Stock
{
    /**
     * DB接続文字列
     *
     * @var string
     */
    private $pdoConnect = "sqlite:stock.db";

    /**
     * 株価データ出力項目リスト
     *
     * @var array
     */
    private $itemNames = array(
        "コード",
        "銘柄名",
        "市場",
        "前日終値",
        "始値",
        "高値",
        "安値",
        "終値",
        "前日比",
        "騰落率",
        "高値-安値",
        "出来高",
        "売買代金");

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
     * 出力項目名を取得する
     *
     * @return Array 出力項目名
     */
    public function getItemsName() {
        return $this->itemNames;
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
                "CREATE TABLE stock( " .
                "  'code' text , " .
                "  'date' text , " .
                "  'market' text, " .
                "  'stock_name' text, " .
                "  'business_type' text, " .
                "  'opening_price' integer, " .
                "  'high_price' integer, " .
                "  'low_price' integer, " .
                "  'closing_price' integer, " .
                "  'volume' integer, " .
                "  'trading_value' integer, " .
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

        $date = $dateObj->format(Common::DATE_FORMAT);

        $db = null;

        try {

            $db = new PDO($this->pdoConnect);

            // 存在チェック
            $sql = "SELECT COUNT(code) AS ID FROM stock WHERE date = '$date'";
            $stmt = $db->query($sql);
            $result = $stmt->fetch(PDO::FETCH_BOTH);

            // DBに存在しないので追加する
            if ($result[0] == 0) {

                // CSVファイルパス作成
                $csvFilePath = Common::CSV_FILE_PATH . sprintf(Common::STOCK_CSV_FILE_NAME, $date);
                $str = file_get_contents($csvFilePath);

                // "SJIS" -> "UTF-8"
                $encodingStr = mb_convert_encoding($str, "UTF-8", "SJIS");
                $parseLine = explode("\r\n", $encodingStr);
                $lineCount = count($parseLine);

                $db->beginTransaction();

                $sql =
                    "INSERT INTO stock(" .
                    "  code, " .
                    "  date, " .
                    "  market, " .
                    "  stock_name, " .
                    "  business_type, " .
                    "  opening_price, " .
                    "  high_price, " .
                    "  low_price, " .
                    "  closing_price, " .
                    "  volume, " .
                    "  trading_value) " .
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
                    "  ?) ";

                $prepare = $db->prepare($sql);

                // スルー。1行目(日付のみ), 2行目(項目名), 最終行(空行)
                for ($i = 2; $i < $lineCount - 1; $i++) {

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

    /**
     * 株価データ取得
     *
     * @param 文字列 $date 日付形式("yyyy-MM-dd")の文字列
     * ★TODO:
     * @return boolean 成功 true、失敗 false
     */
    public function getPrepare($date, $selectedMarkets = "", $closingPrice = "", $rangePrice = "", $volume = "", $sort = "コード", $order = "asc") {

        if ( ! $this->checkDate($date) ) {
            return false;
        }

        $itemNames = $this->itemNames;

        // 市場
        $marketsSql = "AND (";
        foreach ($selectedMarkets as $value) {
            $marketsSql .= " {$itemNames[2]} = '{$value}' OR";
        }
        $marketsSql = rtrim($marketsSql, "OR");
        $marketsSql .= ") ";

        // 終値
        $closingPriceSql = $this->createWhereSqlMinMaxPrice($closingPrice, $itemNames[7]);

        // 高値-安値
        $rangePriceSql = $this->createWhereSqlMinMaxPrice($rangePrice, "range_price");

        // 出来高
        $volumeSql = $this->createWhereSqlMinMaxPrice($volume, $itemNames[11]);

        // ソート項目
        $sortIndex = array_search($sort, $this->itemNames, true);

        $db = new PDO($this->pdoConnect);

        // 株価データ取得
        $sql =
            "SELECT " .
            "  MAIN.code                   AS {$itemNames[0]}, " .
            "  MAIN.stock_name             AS {$itemNames[1]}, " .
            "  MAIN.market                 AS {$itemNames[2]}, " .
            "  DAY_BEFORE.closing_price    AS {$itemNames[3]}, " .
            "  MAIN.opening_price          AS {$itemNames[4]}, " .
            "  MAIN.high_price             AS {$itemNames[5]}, " .
            "  MAIN.low_price              AS {$itemNames[6]}, " .
            "  MAIN.closing_price          AS {$itemNames[7]}, " .
            "  MAIN.closing_price - DAY_BEFORE.closing_price          AS {$itemNames[8]}, " .
            "  cast((MAIN.closing_price - DAY_BEFORE.closing_price) as REAL) / DAY_BEFORE.closing_price * 100 AS {$itemNames[9]}, " .
            "  MAIN.high_price - low_price AS range_price, " .
            "  MAIN.volume                 AS {$itemNames[11]}, " .
            "  MAIN.trading_value          AS {$itemNames[12]} " .
            "FROM " .
            "  stock AS MAIN LEFT JOIN " .
            "    (SELECT " .                        // 前日終値を取得する
            "      code, " .
            "      closing_price ".
            "    FROM " .
            "      stock " .
            "    WHERE " .
            "      date = ( " .
            "        SELECT " .                     // 指定日付の前日を取得
            "          date " .
            "        FROM " .
            "          stock " .
            "        WHERE " .
            "          date < '$date' " .
            "        ORDER BY " .
            "          date DESC " .
            "        LIMIT 1 ) " .
            "    ) AS DAY_BEFORE ON MAIN.code = DAY_BEFORE.code " .
            "WHERE " .
            "  date = '$date'       " .
            $marketsSql .
            $closingPriceSql .
            $rangePriceSql .
            $volumeSql .
            "ORDER BY " .
            "  {$itemNames[$sortIndex]} {$order} ";

        // Debug::logPrintR($sql);

        $this->stmt = $db->query($sql);

        $db = null;

        return true;
    }

    /**
     * 市場一覧取得
     *
     * @return boolean 成功 true、失敗 false
     */
    public function getMarketList() {

        $db = new PDO($this->pdoConnect);

        $sql =
            "SELECT " .
            "  DISTINCT market " .
            "FROM " .
            "  stock " .
            "ORDER BY " .
            "  market ";

        $this->stmt = $db->query($sql);

        $db = null;

        return true;
    }

    /**
     * 取得メソッド実行後にこのメソッドをコールすることで該当するデータを1つ取得する
     *
     * @return mixed 取得成功、取得失敗 false
     */
    public function getNext() {

        return $this->stmt ? $this->stmt->fetch(PDO::FETCH_ASSOC) : false;
    }

    /**
     * 日付文字列が正しい形式("yyyy-MM-dd")かチェックする
     *
     * @param 文字列 $date 日付文字列
     * @return boolean 正しい true、不正 false
     */
    private function checkDate($date) {

        return strtotime($date);
    }

    /**
     * 最小〜最大の範囲を持つ項目のWHERE SQLを作成する
     *
     * @param 配列 $priceArray 最小最大の要素を含む配列 [0]:最小 [1]:最大
     * @param 文字列 $compItemName 比較する項目名
     * @return string SQL文字列
     */
    private function createWhereSqlMinMaxPrice($priceArray, $compItemName) {

        $retSql = "";

        if (count($priceArray) === 2) {
            $sign = [">=", "<="];

            foreach ($priceArray as $key => $value) {
                if ($value) {
                    $retSql .= " AND {$compItemName} {$sign[$key]} {$value} ";
                }
            }
        }

        return $retSql;
    }

}


