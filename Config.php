<?php
/**
 * Copyright (c) 2012 okumin, http://okumin.com/
 */
 
// 設定クラス
class Config
{
	// データをDBに保存するならTRUE
	// テキストファイルに保存するならFALSE
	const USE_DATABASE = FALSE;
	
	private static $dataMethods; // データを扱うクラス
	
	// OAuth情報の設定
	private static $OAuth;
	
	// 基本設定
	private static $baseConfigs;
	
	public static function init(){
		self::defineInfo();
		self::requireFiles();
		self::$dataMethods = new DataMethods(MethodsFactory::create(Config::USE_DATABASE));
	}
	
	// $dataMethodsを設定
	public static function setDataMethods(DataMethods $dataMethods){
		self::$dataMethods = $dataMethods;
	}
			
	// 定数を定義
	public static function defineInfo(){
		if(defined('DATA_DIR')){
			return;
		}
		define('DATA_DIR', BASE_DIR . '/data');
		define('MODE_DIR', BASE_DIR . '/modes');
		define('METHOD_DIR', BASE_DIR . '/methods');
		define('UTIL_DIR', BASE_DIR . '/utils');
		define('TPL_DIR', BASE_DIR . '/templates');
		define('TITLE', 'phpBotAdmin(べーた)');
		define('URL', "{$_SERVER['SCRIPT_NAME']}");
		define('PORT', "{$_SERVER['SERVER_PORT']}");
	}
	
	// 必要なファイルを読み込み
	public static function requireFiles(){
		require_once BASE_DIR . '/Controller.php';
		require_once BASE_DIR . '/Exceptions.php';
		require_once BASE_DIR . '/Parameters.php';
		require_once BASE_DIR . '/CheckLogin.php';
		require_once BASE_DIR . '/twitteroauth/twitteroauth/twitteroauth.php';
		require_once METHOD_DIR . '/DataMethods.php';
		require_once METHOD_DIR . '/Methods.php';
		require_once METHOD_DIR . '/MethodsFactory.php';
		require_once METHOD_DIR . '/TwitterMethods.php';
		require_once MODE_DIR . '/ModeBase.php';
		require_once MODE_DIR . '/ReactionBase.php';
		require_once UTIL_DIR . '/CheckUtils.php';
		require_once UTIL_DIR . '/Utils.php';
	}
	
	// OAuth情報を設定
	public static function setOAuth(){
		if(!isset(self::$dataMethods)){
			self::setDataMethods(new DataMethods(MethodsFactory::create(Config::USE_DATABASE)));
		}
		$keys = self::$dataMethods->getMethods()->read('OAuth', array(DataMethods::CONSUMER_KEY_COL, DataMethods::CONSUMER_SECRET_COL, DataMethods::ACCESS_TOKEN_COL, DataMethods::ACCESS_TOKEN_SECRET_COL));
		$keys = $keys[0];
		self::$OAuth['consumer_key'] = $keys[DataMethods::CONSUMER_KEY_COL];
		self::$OAuth['consumer_secret'] = $keys[DataMethods::CONSUMER_SECRET_COL];
		self::$OAuth['access_token'] = $keys[DataMethods::ACCESS_TOKEN_COL];
		self::$OAuth['access_token_secret'] = $keys[DataMethods::ACCESS_TOKEN_SECRET_COL];
	}
	
	// OAuth情報を取得
	public static function getOAuth(){
		return self::$OAuth;
	}
	
	// 基本設定をセット
	public static function setBaseConfigs(){
		if(!isset(self::$dataMethods)){
			self::setDataMethods(new DataMethods(MethodsFactory::create(Config::USE_DATABASE)));
		}
		$configs = self::$dataMethods->getMethods()->read('baseConfigs');
		$configs = $configs[0];
		
		self::$baseConfigs[DataMethods::AUTO_FOLLOW_COL] = (isset($configs[DataMethods::AUTO_FOLLOW_COL]) && $configs[DataMethods::AUTO_FOLLOW_COL] === '1') ? '1' : '';
		self::$baseConfigs[DataMethods::CUT_COL] = (isset($configs[DataMethods::CUT_COL]) && $configs[DataMethods::AUTO_FOLLOW_COL] === '1') ? '1' : '';
	}
	
	// 基本設定をゲット
	public function getBaseConfigs(){
		return self::$baseConfigs;
	}
}