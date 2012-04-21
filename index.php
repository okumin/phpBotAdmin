<?php
/**
 * Copyright (c) 2012 okumin, http://okumin.com/
 */
 
// ベースとなるパスを設定
define('BASE_DIR', dirname(__FILE__));

// 基本設定
require_once BASE_DIR . '/Config.php';
Config::init();

// GETパラメータ、POSTパラメータを格納
Parameters::getInstance();

// 処理実行
$controller = new Controller();