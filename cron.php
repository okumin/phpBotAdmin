<?php
/**
 * Copyright (c) 2012 okumin, http://okumin.com/
 */
 
define('BASE_DIR', dirname(__FILE__));

// 基本設定
require_once BASE_DIR . '/Config.php';
require_once BASE_DIR . '/ExecBot.php';
Config::init();

Config::setOAuth();
Config::setBaseConfigs();

$object = new ExecBot(getdate(), Config::getBaseConfigs(), Config::getOAuth());