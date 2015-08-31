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
     * 株価データを取得する
     *
     * @var string
     */
    private $urlFormat = "http://k-db.com/stocks/%s?download=csv";

    /**
     * 株価データ出力項目リスト
     *
     * @var array
     */
    private $itemNames = array(
        "コード",
        "銘柄名",
        "市場",
        "始値",
        "高値",
        "安値",
        "終値",
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
     * @param 文字列 $date 日付形式("yyyy-MM-dd")の文字列
     * @return boolean 成功 true、失敗 false
     */
    public function add($date) {

        if ( ! $this->checkDate($date) ) {
            return false;
        }

        $db = null;

        try {

            $db = new PDO($this->pdoConnect);

            // 存在チェック
            $sql = "SELECT COUNT(code) AS ID FROM stock WHERE date = '$date'";
            $stmt = $db->query($sql);
            $result = $stmt->fetch(PDO::FETCH_BOTH);

            // DBに存在しないので追加する
            if ($result[0] == 0) {

                // CSVファイルダウンロード
                $url = sprintf($this->urlFormat, $date);
                $str = file_get_contents($url);

                // "SJIS" -> "UTF-8"
                $encodingStr = mb_convert_encoding($str, "UTF-8", "SJIS");
                $parseLine = explode("\r\n", $encodingStr);
                $lineCount = count($parseLine);

                // 取引が行われない土日などの日付でダウンロードすると
                // 最新のデータが取得されるので、
                // 入力した日付と実際に取得したデータの日付が一致するかチェックする
                $csvDate = DateTime::createFromFormat("Y年m月d日", $parseLine[0]);
                $checkDate = $csvDate->format('Y-m-d');

                // パラメータの日付が正しくない場合
                if ($date != $checkDate) {
                    $db = null;
                    return false;
                }

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
    public function getPrepare($date, $selectedMarkets = "", $sort = "コード", $order = "asc") {

        if ( ! $this->checkDate($date) ) {
            return false;
        }

        $sortIndex = array_search($sort, $this->itemNames, true);
        $itemNames = $this->itemNames;

        $marketsSql = "(";
        foreach ($selectedMarkets as $value) {
            $marketsSql .= " {$itemNames[2]} = '{$value}' OR";
        }
        $marketsSql = rtrim($marketsSql, "OR");
        $marketsSql .= ") AND ";

        $db = new PDO($this->pdoConnect);

        // 株価データ取得
        $sql =
            "SELECT " .
            "  code                   AS {$itemNames[0]}, " .
            "  stock_name             AS {$itemNames[1]}, " .
            "  market                 AS {$itemNames[2]}, " .
            "  opening_price          AS {$itemNames[3]}, " .
            "  high_price             AS {$itemNames[4]}, " .
            "  low_price              AS {$itemNames[5]}, " .
            "  closing_price          AS {$itemNames[6]}, " .
            "  high_price - low_price AS range_price, " .
            "  volume                 AS {$itemNames[8]}, " .
            "  trading_value          AS {$itemNames[9]} " .
            "FROM " .
            "  stock " .
            "WHERE " .
            "  date = '$date'       AND " .
            $marketsSql .
            "  range_price >= 30              " .
            "ORDER BY " .
            "  {$itemNames[$sortIndex]} {$order} ";
            // "  {$itemNames[8]} >= 1000000 AND " .
            // "  {$itemNames[6]} >= 200     AND " .
            // "  {$itemNames[6]} < 8000     AND " .


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

        return $this->stmt ? $this->stmt->fetch(PDO::FETCH_NUM) : false;
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


}


