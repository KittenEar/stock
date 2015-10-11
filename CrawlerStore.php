<?php

require_once "autoload.php";

session_start();

/**
 * 監視銘柄ストア
 */
class CrawlerStore
{
    private $monitorStocks = null;

    public function __construct() {
        $this->loadSession();
    }

    public function getMonitorStocks() {
        return $this->monitorStocks;
    }

    public function allDelete() {
        unset($this->monitorStocks);
        $this->monitorStocks = [];
        $this->saveSession();
    }

    public function addCode($addCode) {
        $addCode = trim($addCode);
        $array = explode(" ", $addCode);

        $this->monitorStocks = array_merge($this->monitorStocks, $array);
        $this->saveSession();
    }

    public function delete($index) {
        $keys = array_keys($index);
        $index = $keys[0];

        unset($this->monitorStocks[$index - 1]);

        // unsetではキーが抜けたままになるので、再配置を行う
        $this->monitorStocks = array_merge($this->monitorStocks);
        $this->saveSession();
    }

    private function saveSession() {
        $_SESSION['monitor_stocks'] = $this->monitorStocks;
    }

    private function loadSession() {
        $this->monitorStocks = isset($_SESSION['monitor_stocks']) ? $_SESSION['monitor_stocks'] : [];
    }
}

