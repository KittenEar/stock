<?php

// require php version 5.4~

require_once "autoload.php";

session_start();

$stock = new Stock();

$date = new DateTime();
$date->modify("-1 day");
$dateFormat = $date->format(Common::DATE_FORMAT);
$code = "4661-T";


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $addCode = isset($_POST['addCode']) ? $_POST['addCode'] : null;
    $allDelete = isset($_POST['allDelete']) ? $_POST['allDelete'] : null;
    $delete = isset($_POST['delete']) ? $_POST['delete'] : null;

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

    if ($delete) {

        $monitorStocks = $_SESSION['monitor_stocks'];

        $keys = array_keys($delete);
        $index = $keys[0];

        unset($monitorStocks[$index - 1]);

        $_SESSION['monitor_stocks'] = $monitorStocks;
    }

}

$monitorStocks = $_SESSION['monitor_stocks'];

// var_dump($monitorStocks);



?>

<!Doctype html>
<html lang = "ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

</head>
<body>


<form action = "" method = "POST">
監視銘柄コード<input type = "text" name = "addCode" placeholder = "xxxx" value = "">
<input type = "submit" value = "追加">
<p>

<?php

echo "<table rules='all' border='1' cellspacing='0' cellpadding='2' style='font-size : 14px;' bordercolor='#a0b0ff'>";
echo "<caption></caption>";
echo "<tr>";

// 項目行
echo "<th bgcolor='#e0f0ff'>No.</th>";
echo "<th bgcolor='#e0f0ff'>コード</th>";
echo "<th bgcolor='#e0f0ff'>銘柄名</th>";
echo "<th bgcolor='#e0f0ff'>市場</th>";
echo "<th bgcolor='#e0f0ff'>前日終値</th>";
echo "<th bgcolor='#e0f0ff'>前日高値</th>";
echo "<th bgcolor='#e0f0ff'>前日安値</th>";
echo "<th bgcolor='#e0f0ff'>現在値</th>";
echo "<th bgcolor='#e0f0ff'>削除</th>";

echo "</tr>";


$count = 1;
foreach ($monitorStocks as $code) {

    echo "<tr>";

    $stock->getStockDayInfoFromCode($code . "-T", $dateFormat);
    $result = $stock->getNext();

    $price = StockCrawler::getStockPrice($code);


    echo "<td>" . $count . "</td>";
    echo "<td>" . $result["コード"] . "</td>";
    echo "<td>" . $result["銘柄名"] . "</td>";
    echo "<td>" . $result["市場"] . "</td>";
    echo "<td>" . Common::changeStockFormat($result["終値"]) . "</td>";     // 前日終値は前々回になるのでココでは終値を指定
    echo "<td>" . Common::changeStockFormat($result["高値"]) . "</td>";
    echo "<td>" . Common::changeStockFormat($result["安値"]) . "</td>";
    echo "<td>" . $price . "</td>";
    echo "<td><input type = 'submit' name = 'delete[{$count}]' value = '削除'></td>";

    echo "</tr>";

    $count++;
}

echo "</table>";
?>

<p>
監視銘柄全削除<input type = "submit" name = "allDelete" value = "全削除">
</form>


<SCRIPT Language="JavaScript">
<!--
    function pageReload()
    {
        location.reload();
    }
setTimeout("pageReload()", 60 * 1000);
// -->
</SCRIPT>

</body>
</html>






