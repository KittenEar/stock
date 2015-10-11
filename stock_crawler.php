<?php

// require php version 5.4~

require_once "autoload.php";

$stock = new Stock();
$crawlerStore = new CrawlerStore();

$date = new DateTime();
$date->modify("-1 day");
$beforeDate = $date->format(Common::DATE_FORMAT);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $addCode = isset($_POST['addCode']) ? $_POST['addCode'] : null;
    $allDelete = isset($_POST['allDelete']) ? $_POST['allDelete'] : null;
    $delete = isset($_POST['delete']) ? $_POST['delete'] : null;

    // 監視銘柄 全削除
    if ($allDelete) {
        $crawlerStore->allDelete();
    }

    // 監視銘柄 追加
    if ($addCode) {
        $crawlerStore->addCode($addCode);
    }

    if ($delete) {
        $crawlerStore->delete($delete);
    }

}

$monitorStocks = $crawlerStore->getMonitorStocks();

?>

<!Doctype html>
<html lang = "ja">
<head>
<title>銘柄監視</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<script Language="JavaScript">
<!--
    function pageReload()
    {
        location.reload();
    }
setTimeout("pageReload()", 60 * 1000);


function nowDateTime() {
    var date = Date();
    document.writeln(date);
}
// -->
</script>

</head>
<body>
<p><a href="./index.html">戻る</a>
<p><h3>銘柄監視</h3>


<form action = "" method = "POST">
監視銘柄コード<input type = "text" name = "addCode" placeholder = "xxxx" value = "">
<input type = "submit" value = "追加">
<p>

<?php

echo "<table rules='all' border='1' cellspacing='0' cellpadding='2' style='font-size : 14px;' bordercolor='#a0b0ff'>";
echo "<caption><script type='text/javascript'>nowDateTime()</script></caption>";
echo "<tr>";

// 項目行
echo "<th bgcolor='#e0f0ff'>No.</th>";
echo "<th bgcolor='#e0f0ff'>コード</th>";
echo "<th bgcolor='#e0f0ff'>銘柄名</th>";
echo "<th bgcolor='#e0f0ff'>市場</th>";
echo "<th bgcolor='#e0f0ff'>前日終値</th>";
echo "<th bgcolor='#e0f0ff'>前日高値</th>";
echo "<th bgcolor='#e0f0ff'>前日安値</th>";
echo "<th bgcolor='#e0f0ff'>始値</th>";
echo "<th bgcolor='#e0f0ff'>高値</th>";
echo "<th bgcolor='#e0f0ff'>安値</th>";
echo "<th bgcolor='#e0f0ff'>現在値</th>";
echo "<th bgcolor='#e0f0ff'>GU/GD</th>";
echo "<th bgcolor='#e0f0ff'>前日高値/安値ブレイク</th>";
echo "<th bgcolor='#e0f0ff'>削除</th>";

echo "</tr>";


$count = 1;
foreach ($monitorStocks as $code) {

    echo "<tr>";

    $stock->getStockDayInfoFromCode($code . "-T", $beforeDate);
    $beforeStock = $stock->getNext();

    $crawler = new StockCrawler;
    $crawler->getStockPrice($code);

    $beforeClosingPrice = $beforeStock["終値"];
    $beforeHighPrice = $beforeStock["高値"];
    $beforeLowPrice = $beforeStock["安値"];
    $openingPrice = $crawler->getOpeningPrice();
    $highPrice = $crawler->getHighPrice();
    $lowPrice = $crawler->getLowPrice();
    $nowPrice = $crawler->getNowPrice();

    // GU/GD 判定
    $gugd = "-";
    if ($openingPrice > $beforeHighPrice) {
        $gugd = "GU +" . Common::changeStockFormat($openingPrice - $beforeHighPrice);
    }
    else if ($openingPrice < $beforeLowPrice) {
        $gugd = "GD -" . Common::changeStockFormat($beforeLowPrice - $openingPrice);
    }

    // ブレイク判定
    $break = "-";
    if ($beforeHighPrice < $highPrice) {
        $break = "前日高値ブレイク";
    }
    else if ($beforeLowPrice > $lowPrice) {
        $break = "前日安値ブレイク";
    }

    echo "<td>" . $count . "</td>";
    echo "<td>" . $beforeStock["コード"] . "</td>";
    echo "<td>" . $beforeStock["銘柄名"] . "</td>";
    echo "<td>" . $beforeStock["市場"] . "</td>";
    echo "<td>" . Common::changeStockFormat($beforeClosingPrice) . "</td>";
    echo "<td>" . Common::changeStockFormat($beforeHighPrice) . "</td>";
    echo "<td>" . Common::changeStockFormat($beforeLowPrice) . "</td>";
    echo "<td>" . Common::changeStockFormat($openingPrice) . "</td>";
    echo "<td>" . Common::changeStockFormat($highPrice) . "</td>";
    echo "<td>" . Common::changeStockFormat($lowPrice) . "</td>";
    echo "<td>" . Common::changeStockFormat($nowPrice) . "</td>";
    echo "<td>" . $gugd . "</td>";
    echo "<td>" . $break . "</td>";
    echo "<td><input type = 'submit' name = 'delete[{$count}]' value = '削除'></td>";

    echo "</tr>";

    $count++;
}

echo "</table>";
?>

<p>
監視銘柄全削除<input type = "submit" name = "allDelete" value = "全削除">
</form>



</body>
</html>






