<?php
// require php version 5.4~

require_once 'goutte-v2.0.4.phar';

use Goutte\Client;

session_start();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $addCode = $_POST['addCode'];
    $allDelete = $_POST['allDelete'];

    // 監視銘柄 全削除
    if ($allDelete) {
        unset($_SESSION['monitor_stocks']);
    }

    // 監視銘柄 追加
    if ($addCode) {
        // 登録済みの監視銘柄取得（この変数に追加する）
        $monitorStocks = $_SESSION['monitor_stocks'];

        if ( ! $monitorStocks ) {
            $monitorStocks = array();
        }

        array_push($monitorStocks, $addCode);

        $_SESSION['monitor_stocks'] = $monitorStocks;
    }


}

$monitorStocks = $_SESSION['monitor_stocks'];

// var_dump($monitorStocks);

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


?>

<!Doctype html>
<html lang = "ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

</head>
<body>


<form action = "" method = "POST">
監視銘柄コード<input type = "text" name = "addCode" placeholder = "xxxx" value =
  "<?php echo htmlspecialchars($addCode, ENT_QUOTES, 'UTF-8'); ?>"
>
<input type = "submit" value = "追加">
</form>


<?php

foreach ($monitorStocks as $code) {

    $price = StockCrawler::getStockPrice($code);
    echo "{$code} -> {$price}";
    echo "<br>";
}
?>


<form action = "" method = "POST">
監視銘柄全削除<input type = "submit" name = "allDelete" value = "全削除">
</form>


<SCRIPT Language="JavaScript">
<!--
    function pageReload()
    {
        location.reload();
    }
setTimeout("pageReload()", 15 * 1000);
// -->
</SCRIPT>

</body>
</html>






