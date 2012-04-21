<?php
/**
 * Copyright (c) 2012 okumin, http://okumin.com/
 */
 
// 設定を行うモデル
class ConfigMode extends ModeBase
{
	// 基本設定を編集
	public function baseAction(){
		$this->template = 'baseConfigs';
		if($this->parameters->getGetParameter('mode') === 'config'){
			$this->menu['基本設定'] = URL . '?mode=config&action=base';
		}else{
			$this->menu = array('初期設定' => URL . '?mode=init&action=base');
		}
		try{
			$this->checkBasePost();
			$this->dataMethods->updateBaseConfigs();
			if($this->parameters->getGetParameter('mode') === 'init'){
				Utils::jumpToNewURL(array(), array('mode' => 'init', 'action' => 'end'));
			}
			$this->replace['SAFE_MESSAGE'] = self::EDITED;
		}catch(PostNullException $e_post){
			$this->replace['SAFE_MESSAGE'] = '';
		}catch(Exception $e){
			$this->replace['SAFE_MESSAGE'] = self::ERROR1 . $e->getMessage() . self::ERROR2;
		}
		$this->replace['CHECK_autoFollow'] = isset($e) ?
			$this->parameters->getPostParameter('autoFollow') :
			$this->dataMethods->fetchBaseConfigs(DataMethods::AUTO_FOLLOW_COL);
		$this->replace['CHECK_cut'] = isset($e) ?
			$this->parameters->getPostParameter('cut') :
			$this->dataMethods->fetchBaseConfigs(DataMethods::CUT_COL);
	}
	
	// ユーザー情報を編集
	public function userInfoAction(){
		$this->template = 'userInfo';
		if($this->parameters->getGetParameter('mode') === 'config'){
			$this->menu['ユーザー情報設定'] = URL . '?mode=config&action=userInfo';
		}else{
			$this->menu = array('初期設定' => URL . '?mode=init&action=userInfo');
		}
		try{
			$this->checkUserInfoPost();
			$this->dataMethods->updateUserInfo();
			if($this->parameters->getGetParameter('mode') === 'init'){
				Utils::jumpToNewURL(array(), array('mode' => 'init', 'action' => 'editOauth'));
			}
			$this->replace['SAFE_MESSAGE'] = self::EDITED;
		}catch(PostNullException $e_post){
			$this->replace['SAFE_MESSAGE'] = '';
		}catch(Exception $e){
			$this->replace['SAFE_MESSAGE'] = self::ERROR1 . $e->getMessage() . self::ERROR2;
		}
		$this->replace['user'] = isset($e) ?
			$this->parameters->getPostParameter('user') :
			$this->dataMethods->fetchUserInfo(DataMethods::USER_COL);
		$this->replace['pass'] = '';
	}
	
	// 登録しているOAuth情報を表示
	public function OauthAction(){
		$this->template = 'OAuth';
		$this->menu['OAuth情報確認'] = URL . '?mode=config&action=oauth';
		$this->replace['SAFE_MESSAGE'] = '';
		$this->replace['consumer_key'] = preg_replace('/[0-9a-zA-Z]/', '*', $this->dataMethods->fetchOAuth(DataMethods::CONSUMER_KEY_COL));
		$this->replace['consumer_secret'] = preg_replace('/[0-9a-zA-Z]/', '*', $this->dataMethods->fetchOAuth(DataMethods::CONSUMER_SECRET_COL));
		$this->replace['access_token'] = preg_replace('/[0-9a-zA-Z]/', '*', $this->dataMethods->fetchOAuth(DataMethods::ACCESS_TOKEN_COL));
		$this->replace['access_token_secret'] = preg_replace('/[0-9a-zA-Z]/', '*', $this->dataMethods->fetchOAuth(DataMethods::ACCESS_TOKEN_SECRET_COL));
	}

	// OAuth情報を編集
	public function editOauthAction(){
		$this->template = 'editOAuth';
		if($this->parameters->getGetParameter('mode') === 'config'){
			$this->menu['OAuth情報確認'] = URL . '?mode=config&action=oauth';
			$this->menu['OAuth情報設定'] = URL . '?mode=config&action=editOauth';
		}else{
			$this->menu = array('初期設定' => URL . '?mode=init&action=editOauth');
		}
		try{
			$this->checkOAuthPost();
			$this->dataMethods->updateOAuth();
			if($this->parameters->getGetParameter('mode') === 'init'){
				Utils::jumpToNewURL(array(), array('mode' => 'init', 'action' => 'base'));
			}else{
				Utils::jumpToNewURL(array(), array('mode' => 'config', 'action' => 'oauth'));
			}
		}catch(PostNullException $e_post){
			$this->replace['SAFE_MESSAGE'] = '';
		}catch(Exception $e){
			$this->replace['SAFE_MESSAGE'] = self::ERROR1 . $e->getMessage() . self::ERROR2;
		}
		$this->replace['consumer_key'] = $this->parameters->getPostParameter('consumer_key');
		$this->replace['consumer_secret'] = $this->parameters->getPostParameter('consumer_secret');
		$this->replace['access_token'] = $this->parameters->getPostParameter('access_token');
		$this->replace['access_token_secret'] = $this->parameters->getPostParameter('access_token_secret');
	}
	
	// 自動フォロー返しの例外を設定
	public function oneSidedAction(){
		$this->template = 'oneSided';
		$this->menu['フォロー返しの例外設定'] = URL . '?mode=config&action=oneSided';
		Config::setOAuth();
		$OAuth = Config::getOAuth();
		$twitterMethods = new TwitterMethods($OAuth['consumer_key'], $OAuth['consumer_secret'], $OAuth['access_token'], $OAuth['access_token_secret']);
		try{
			$result = $twitterMethods->getAccountVerify_credentials();
			if(isset($result->error)){
				throw new Exception('APIエラー: ' . $result->error);
			}
			$this->checkOneSidedPost();
			$oneSided = $this->parseOneSided($twitterMethods);
			$this->dataMethods->updateOneSided($oneSided);
			if($this->parameters->getGetParameter('mode') == 'init'){
				Utils::jumpToNewURL(array(), array('mode' => 'init', 'action' => 'end'));
			}
			$this->replace['oneSided'] = $this->formatOneSided($twitterMethods, $this->dataMethods->fetchOneSided());
			$this->replace['SAFE_MESSAGE'] = self::EDITED;
		}catch(PostNullException $e_post){
			try{
				$this->replace['oneSided'] = $this->formatOneSided($twitterMethods, $this->dataMethods->fetchOneSided());
				$this->replace['SAFE_MESSAGE'] = '';
			}catch(Exception $e){
			$this->replace['SAFE_MESSAGE'] = self::ERROR1 . $e->getMessage() . self::ERROR2;
				$this->replace['oneSided'] = '';
			}
		}catch(Exception $e){
			$this->replace['SAFE_MESSAGE'] = self::ERROR1 . $e->getMessage() . self::ERROR2;
			$this->replace['oneSided'] = $this->parameters->getPostParameter('oneSided');
		}
	}
	
	// 入力チェック(基本設定)
	private function checkBasePost(){
		if(!count($this->parameters->getPostParameter())){
			throw new PostNullException();
		}
		CheckUtils::checkBool($this->parameters->getPostParameter('autoFollow'), '自動フォロー返し');
		CheckUtils::checkBool($this->parameters->getPostParameter('cut'), '140字を超えるつぶやきの処理');
	}
	
	// 入力チェック(ユーザー情報)
	private function checkUserInfoPost(){
		if(!count($this->parameters->getPostParameter())){
			throw new PostNullException();
		}
		CheckUtils::checkEmpty($this->parameters->getPostParameter('user'), 'ユーザー名');
		CheckUtils::checkHalfWidth($this->parameters->getPostParameter('user'), 'ユーザー名');
		if(strlen($this->parameters->getPostParameter('user')) < 4 || strlen($this->parameters->getPostParameter('user')) > 12){
			throw new CheckException('ユーザー名は半角英数字4〜12文字で入力してください。');
		}
		CheckUtils::checkEmpty($this->parameters->getPostParameter('pass'), 'パスワード');
		CheckUtils::checkHalfWidth($this->parameters->getPostParameter('pass'), 'パスワード');
		if(strlen($this->parameters->getPostParameter('pass')) < 4 || strlen($this->parameters->getPostParameter('pass')) > 12){
			throw new CheckException('パスワードは半角英数字4〜12文字で入力してください。');
		}
	}
	
	// 入力チェック(OAuth情報)
	private function checkOAuthPost(){
		if(!count($this->parameters->getPostParameter())){
			throw new PostNullException();
		}
	}
	
	// 入力チェック(片思い)
	private function checkOneSidedPost(){
		if(!count($this->parameters->getPostParameter())){
			throw new PostNullException();
		}
	}
	
	// oneSidedに入力されたデータを、ユーザーIDの配列にして返す
	public function parseOneSided(TwitterMethods $twitterMethods){
		$lines = explode("\n", $this->parameters->getPostParameter('oneSided'));
		$user_ids = array();
		$screen_names = array();
		while($screen_name = array_shift($lines)){
			if(!preg_match('/^[0-9a-zA-Z_]+$/', $screen_name)){
				continue;
			}
			$screen_names[] = $screen_name;
			if(count($screen_names) == 100){
				$user_ids = array_merge($user_ids, $twitterMethods->getUserIds($screen_names));
				$screen_names = array();
			}
		}
		$user_ids = array_merge($user_ids, $twitterMethods->getUserIds($screen_names));
		return $user_ids;
	}
	
	// oneSidedに保存されているデータを、screen_name形式にして返す
	public function formatOneSided(TwitterMethods $twitterMethods, $data){
		$screen_names = array();
		$user_ids = array();
		while($user_id = array_shift($data)){
			$user_ids[] = $user_id;
			if(count($user_ids) == 100){
				$screen_names = array_merge($screen_names, $twitterMethods->getScreenNames($user_ids));
				$user_ids = array();
			}
		}
		$screen_names = array_merge($screen_names, $twitterMethods->getScreenNames($user_ids));
		return (count($screen_names) !== 0) ? implode("\n", $screen_names) : '';
	}
}