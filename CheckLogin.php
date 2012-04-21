<?php
/**
 * Copyright (c) 2012 okumin, http://okumin.com/
 */
 
// 認証
class CheckLogin
{
	public function __construct(){
		if(
			Parameters::getInstance()->getGetParameter('mode') === 'init' ||
			Parameters::getInstance()->getGetParameter('mode') === 'bootstrap'	
		){
			return;
		}
		if($this->checkSession()){
			if(
				Parameters::getInstance()->getGetParameter('mode') === 'login' &&
				Parameters::getInstance()->getGetParameter('action') !== 'logout'
			){
				Utils::jumpToNewURL(array(), array());
			}
			return;
		}
		if(!$this->checkUserPass()){
			Utils::jumpToNewURL(array(), array('mode' => 'init'));
		}elseif(!$this->checkOAuth()){
			Utils::jumpToNewURL(array(), array('mode' => 'init', 'action' => 'editOauth'));
		}elseif(!$this->checkBaseConfigs()){
			Utils::jumpToNewURL(array(), array('mode' => 'init', 'action' => 'baseConfigs'));
		}else{
			if(Parameters::getInstance()->getGetParameter('mode') !== 'login'){
				Utils::jumpToNewURL(array(), array('mode' => 'login'));
			}
		}
	}
	
	// セッションをチェック
	private function checkSession(){
		session_start();
		if(isset($_SESSION['loginStatus']) && $_SESSION['loginStatus'] === TRUE){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	
	// ユーザー名とパスワードが登録されているか確認
	private function checkUserPass(){
		$methods = MethodsFactory::create(Config::USE_DATABASE);
		try{
			$data = $methods->read('userInfo');
			if(count($data[0]) !== 2){
				throw new Exception('userInfoに保存されているデータが不正です。');
			}
			return TRUE;
		}catch(Exception $e){
			return FALSE;
		}
	}
	
	// OAuth情報が登録されているか確認
	private function checkOAuth(){
		$methods = MethodsFactory::create(Config::USE_DATABASE);
		try{
			$data = $methods->read('OAuth');
			if(count($data[0]) !== 4){
				throw new Exception('OAuthに保存されているデータが不正です。');
			}
			return TRUE;
		}catch(Exception $e){
			return FALSE;
		}
	}
	
	// 基本情報が保存されているか確認
	private function checkBaseConfigs(){
		$methods = MethodsFactory::create(Config::USE_DATABASE);
		try{
			$data = $methods->read('baseConfigs');
			if(count($data[0]) < 1){
				throw new Exception('OAuthに保存されているデータが不正です。');
			}
			return TRUE;
		}catch(Exception $e){
			return FALSE;
		}
	}
}