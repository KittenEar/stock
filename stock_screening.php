<?php
// require php version 5.4~

require_once "autoload.php";

// エラーを出力する
// ini_set('display_errors', 1);

session_start();


$stock = new Stock();
$stockParamModel = new StockParamModel();

// [ポストされた値を取得]
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $searchDate = $_POST[StockParamModel::KEY_NAME_SEARCH_DATE];
    $selectedMarkets = $_POST[StockParamModel::KEY_NAME_SELECTED_MARKETS];
    $closingPrice = $_POST[StockParamModel::KEY_NAME_CLOSING_PRICE];
    $rangePrice = $_POST[StockParamModel::KEY_NAME_RANGE_PRICE];
    $volume = $_POST[StockParamModel::KEY_NAME_VOLUME];
    $unitShares = $_POST[StockParamModel::KEY_NAME_UNIT_SHARES];
    $unitVolume = $_POST[StockParamModel::KEY_NAME_UNIT_VOLUME];
    $sort = $_POST[StockParamModel::KEY_NAME_SORT];
    $order = $_POST[StockParamModel::KEY_NAME_ORDER];

}
// [セッションから値を取得]
else {
    $searchDate = "";
    $selectedMarkets = $_SESSION[StockParamModel::KEY_NAME_SELECTED_MARKETS];
    $closingPrice = $_SESSION[StockParamModel::KEY_NAME_CLOSING_PRICE];
    $rangePrice = $_SESSION[StockParamModel::KEY_NAME_RANGE_PRICE];
    $volume = $_SESSION[StockParamModel::KEY_NAME_VOLUME];
    $unitShares = $_POST[StockParamModel::KEY_NAME_UNIT_SHARES];
    $unitVolume = $_POST[StockParamModel::KEY_NAME_UNIT_VOLUME];
    $sort = $_SESSION[StockParamModel::KEY_NAME_SORT];
    $order = $_SESSION[StockParamModel::KEY_NAME_ORDER];

}

// [初期値設定]

// 初期状態の市場は"全選択"状態とする
if ( ! $selectedMarkets ) {
    $selectedMarkets = $stock->getMarketList();
}

// 初期状態のソートは"コード"
if ( ! $sort ) {
    $sort = $stock->getItemsName()[0];
}

// 初期状態のオーダーは"昇順"
if ( ! $order ) {
    $order = "asc";
}

// [セッションに値を保存]

// 検索日付は保存しない
$_SESSION[StockParamModel::KEY_NAME_SELECTED_MARKETS] = $selectedMarkets;
$_SESSION[StockParamModel::KEY_NAME_CLOSING_PRICE] = $closingPrice;
$_SESSION[StockParamModel::KEY_NAME_RANGE_PRICE] = $rangePrice;
$_SESSION[StockParamModel::KEY_NAME_VOLUME] = $volume;
$_SESSION[StockParamModel::KEY_NAME_UNIT_SHARES] = $unitShares;
$_SESSION[StockParamModel::KEY_NAME_UNIT_VOLUME] = $unitVolume;
$_SESSION[StockParamModel::KEY_NAME_SORT] = $sort;
$_SESSION[StockParamModel::KEY_NAME_ORDER] = $order;


// 空白や無効な日付形式の場合
if ( ! strtotime($searchDate) ) {
    // 現在日
    $date = date("Y-m-d");
}
else {
    // 指定日付
    $date = date("Y-m-d", strtotime($searchDate));
}


?>

<!Doctype html>
<html lang = "ja">
<head>
<title>銘柄スクリーニング</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="common.css">

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
<p><a href="./index.html">戻る</a>
<p><h3>銘柄スクリーニング</h3>
<form action = "" method = "POST">
検索日付
<input type = "text" id = "datepicker" name = "<?php echo StockParamModel::KEY_NAME_SEARCH_DATE ?>"
  placeholder = "2015-01-01" value = "<?php echo htmlspecialchars($searchDate, ENT_QUOTES, 'UTF-8'); ?>"
>
<input type = "submit" value = "実行">
<p>

市場<p>
<?php
$selectedMarketsName = StockParamModel::KEY_NAME_SELECTED_MARKETS;

foreach ($stock->getMarketList() as $value) {
    // 除外市場判定
    $checked = in_array($value, $selectedMarkets, true) ? "checked='checked'" : "";

    echo "<input type='checkbox' name='{$selectedMarketsName}[]' value='{$value}' {$checked}>{$value}<br>";
}
?>
</select>
<p>

終値
最小
<input type = "text" class = "custom" name = "<?php echo StockParamModel::KEY_NAME_CLOSING_PRICE ?>[]"
  value = "<?php echo htmlspecialchars($closingPrice[0], ENT_QUOTES, 'UTF-8'); ?>"
>
〜
<input type = "text" class = "custom" name = "<?php echo StockParamModel::KEY_NAME_CLOSING_PRICE ?>[]"
  value = "<?php echo htmlspecialchars($closingPrice[1], ENT_QUOTES, 'UTF-8'); ?>"
>
最大
<p>

<p>

高値-安値
最小
<input type = "text" class = "custom" name = "<?php echo StockParamModel::KEY_NAME_RANGE_PRICE ?>[]"
  value = "<?php echo htmlspecialchars($rangePrice[0], ENT_QUOTES, 'UTF-8'); ?>"
>
〜
<input type = "text" class = "custom" name = "<?php echo StockParamModel::KEY_NAME_RANGE_PRICE ?>[]"
  value = "<?php echo htmlspecialchars($rangePrice[1], ENT_QUOTES, 'UTF-8'); ?>"
>
最大
<p>

出来高
最小
<input type = "text" class = "custom" name = "<?php echo StockParamModel::KEY_NAME_VOLUME ?>[]"
  value = "<?php echo htmlspecialchars($volume[0], ENT_QUOTES, 'UTF-8'); ?>"
>
〜
<input type = "text" class = "custom" name = "<?php echo StockParamModel::KEY_NAME_VOLUME ?>[]"
  value = "<?php echo htmlspecialchars($volume[1], ENT_QUOTES, 'UTF-8'); ?>"
>
最大
<p>

単元株数
最小
<input type = "text" class = "custom" name = "<?php echo StockParamModel::KEY_NAME_UNIT_SHARES ?>[]"
  value = "<?php echo htmlspecialchars($unitShares[0], ENT_QUOTES, 'UTF-8'); ?>"
>
〜
<input type = "text" class = "custom" name = "<?php echo StockParamModel::KEY_NAME_UNIT_SHARES ?>[]"
  value = "<?php echo htmlspecialchars($unitShares[1], ENT_QUOTES, 'UTF-8'); ?>"
>
最大
<p>

単元出来高
最小
<input type = "text" class = "custom" name = "<?php echo StockParamModel::KEY_NAME_UNIT_VOLUME ?>[]"
  value = "<?php echo htmlspecialchars($unitVolume[0], ENT_QUOTES, 'UTF-8'); ?>"
>
〜
<input type = "text" class = "custom" name = "<?php echo StockParamModel::KEY_NAME_UNIT_VOLUME ?>[]"
  value = "<?php echo htmlspecialchars($unitVolume[1], ENT_QUOTES, 'UTF-8'); ?>"
>
最大
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

$stock->getPrepare($date, $selectedMarkets, $closingPrice, $rangePrice, $volume, $unitShares, $unitVolume, $sort, $order);

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

    $keys = array_keys($result);
    $countMax = count($keys);

    for ($i = 0; $i < $countMax; $i++) {

        $key = $keys[$i];
        $value = $result[$key];

        if ($i >= 3 && is_numeric($value)) {

            $colorValue = "000000";
            $prefixValue = "";
            $suffixValue = "";

            if ($key === '前日比') {
                $colorValue = colorForValue($value);
                $prefixValue = ($value < 0) ? "▼" : "▲";
            }

            if ($key === '騰落率') {
                $colorValue = colorForValue($value);
                $suffixValue = "%";
            }

            // 桁区切りを行う。小数点以下が0埋めされるので空白で置き換え
            echo "<td align='right'><span style='color: #{$colorValue}'>" .
                $prefixValue .
                Common::changeStockFormat($value) .
                $suffixValue .
                "</span></td>";
        }
        else {
            echo "<td>$value</td>";
        }
    }

    echo "</tr>";
    $count++;
}

echo "</table>";

function colorForValue($value) {
    $colorValue = ($value < 0) ? $colorValue = "008822" : $colorValue = "FF0000";
    return $colorValue;
}


?>



