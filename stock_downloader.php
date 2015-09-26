<!Doctype html>
<html lang = "ja">
<head>
<title>銘柄情報取得</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
<p><a href="./index.html">戻る</a>
<p><h3>銘柄情報取得</h3>
<progress id='progress' value='0' max='100'><span id="progressValue">0</span></progress>
<span id="currentProcess"></span>
</body>
</html>

<?php

require_once "autoload.php";

$downloader = new StockDownloader();
$stock = new Stock();

$stock->createDB();

$date = new DateTime("2015-09-01");
$nowDate = new DateTime();

$subDate = $nowDate->diff($date);
$dayCountMax = $subDate->days;

$dayCount = 0;

while ($date <= $nowDate) {

    // プログレスバー更新
    $progressPer = $dayCount / $dayCountMax * 100;

    // 全部表示しないで間引く
    if (floor($progressPer % 10) == 0) {
        echo "<script>";
        echo "var progressBar = document.getElementById('progress');";
        echo "var progressBarValue = document.getElementById('progressValue');";
        echo "var currentProcess = document.getElementById('currentProcess');";
        echo "progressBar.value = {$progressPer};";
        echo "progressBarValue.innerHTML = {$progressPer};";
        echo "currentProcess.innerHTML = '{$date->format('Y-m-d')}';";
        echo "</script>";
    }

    // 株価CSVファイルをダウンロード
    $ret = $downloader->downloadCsvFile($date);

    // 株価データを追加
    $stock->add($date);

    // echo "{$date->format('Y-m-d')}</br>";
    ob_flush();
    flush();

    $date->modify("+1 day");
    $dayCount++;
}


?>


