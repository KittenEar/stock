<?php
// require php version 5.4~

require_once "autoload.php";

session_start();


$stock = new Stock();
$stockParamModel = new StockParamModel();

// [ポストされた値を取得]

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $searchDate = $_POST[StockParamModel::KEY_NAME_SEARCH_DATE];
    $selectedMarkets = $_POST[StockParamModel::KEY_NAME_SELECTED_MARKETS];
    $sort = $_POST[StockParamModel::KEY_NAME_SORT];
    $order = $_POST[StockParamModel::KEY_NAME_ORDER];


}

// [セッションに値を保存]

// 初期状態の市場は"全選択"状態とする
if ( ! $selectedMarkets ) {
    $selectedMarkets = array();

    $stock->getMarketList();

    while ($result = $stock->getNext()) {
        array_push($selectedMarkets, $result[0]);
    }
}

// 初期状態のソートは"コード"
if ( ! $sort ) {
    $sort = $stock->getItemsName()[0];
}

// 初期状態のオーダーは"昇順"
if ( ! $order ) {
    $order = "asc";
}

$_SESSION[StockParamModel::KEY_NAME_SELECTED_MARKETS] = $selectedMarkets;
$_SESSION[StockParamModel::KEY_NAME_SORT] = $sort;
$_SESSION[StockParamModel::KEY_NAME_ORDER] = $order;

// [セッションから値を取得]

// 選択市場状態を取得
// $selectedMarkets = $_SESSION[StockParamModel::KEY_NAME_SELECTED_MARKETS];



// 空白や無効な日付形式の場合
if ( ! strtotime($searchDate) ) {
    // 現在日
    $date = date("Y-m-d");
}
else {
    // 指定日付
    $date = date("Y-m-d", strtotime($searchDate));
}

$stock->createDB();
$stock->add($date);

?>

<!Doctype html>
<html lang = "ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<!-- jQuery -->
<script src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js"></script>
<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css" />
<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1/themes/redmond/jquery-ui.css" >
<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1/themes/ui-lightness/jquery-ui.css" >
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/i18n/jquery-ui-i18n.min.js"></script>

<!-- 日付選択用カレンダー -->
<script>
$(function() {
    $.datepicker.setDefaults($.datepicker.regional['ja']);
    $( "#datepicker" ).datepicker({ dateFormat: 'yy-mm-dd' });
});
</script>

</head>
<body>

<form action = "" method = "POST">
検索日付<input type = "text" id = "datepicker" name = "<?php echo StockParamModel::KEY_NAME_SEARCH_DATE ?>" placeholder = "2015-01-01"
  value = "<?php echo htmlspecialchars($searchDate, ENT_QUOTES, 'UTF-8'); ?>"
>
<input type = "submit" value = "実行">
<p>
市場<p>
<?php
$selectedMarketsName = StockParamModel::KEY_NAME_SELECTED_MARKETS;

$stock->getMarketList();
while ($result = $stock->getNext()) {
    // 除外市場判定
    $checked = in_array($result[0], $selectedMarkets, true) ? "checked='checked'" : "";

    echo "<input type='checkbox' name='{$selectedMarketsName}[]' value='{$result[0]}' {$checked}>{$result[0]}<br>";
}
?>
</select>
<p>
並べ替え
<select name = "sort">
<?php
foreach ($stock->getItemsName() as $item) {
    // 選択状態判定
    $selected = ($sort === $item) ? "selected" : "";

    echo "<option value='{$item}' $selected>$item</option>>";
}
?>
</select>
<select name = "order">
<?php
$orderKey = ["asc" => "昇順", "desc" => "降順"];

foreach ($orderKey as $key => $value) {
    // 選択状態判定
    $selected = ($order === $key) ? "selected" : "";

    echo "<option value='{$key}' $selected>$value</option>>";
}
?>
</select>
</form>

</body>
</html>



<?php

$stock->getPrepare($date, $selectedMarkets, $sort, $order);

echo "<table rules='all' border='1' cellspacing='0' cellpadding='2' style='font-size : 14px;' bordercolor='#a0b0ff'>";
echo "<caption>$date</caption>";
echo "<tr>";

// 項目行
echo "<th bgcolor='#e0f0ff'>No.</th>";
foreach ($stock->getItemsName() as $item) {
    echo "<th bgcolor='#e0f0ff'>$item</th>";
}

echo "</tr>";

$count = 1;
while ($result = $stock->getNext()) {
    echo "<tr>";

    echo "<td align='right'>" . $count . "</td>";
    foreach ($result as $key => $value) {
        if ($key >= 3) {
            echo "<td align='right'>" . number_format($value) . "</td>";
        }
        else {
            echo "<td>$value</td>";
        }
    }

    echo "</tr>";
    $count++;
}

echo "</table>";


?>



