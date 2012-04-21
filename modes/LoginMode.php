<?php
/**
 * Copyright (c) 2012 okumin, http://okumin.com/
 */
 
// ログイン
class LoginMode extends ModeBase
{
	// ログイン
	public function indexAction(){
		$this->template = 'login';
		$this->menu = array('ログイン' => URL . '?mode=config&action=base');
		try{
			$this->checkPost();
			$_SESSION['loginStatus'] = TRUE;
			Utils::jumpToNewURL(array(), array());
		}catch(PostNullException $e_post){
			$this->replace['SAFE_MESSAGE'] = '';
		}catch(DataException $e_data){
			Utils::jumpToNewURL(array(), array('mode' => 'init'));
		}catch(Exception $e){
			$this->replace['SAFE_MESSAGE'] = self::ERROR1 . $e->getMessage() . self::ERROR2;
		}
		$this->replace['user'] = isset($e) ?
			$this->parameters->getPostParameter('user') :
			'';
		$this->replace['pass'] = '';
	}
	
	// ログアウト
	public function logoutAction(){
		$_SESSION = array();
		if(ini_get("session.use_cookies")){
			$params = session_get_cookie_params();
			setcookie(session_name(), '', time() - 42000,
				$params["path"], $params["domain"],
				$params["secure"], $params["httponly"]
			);
		}
		session_destroy();
		Utils::jumpToNewURL(array(), array());
	}
	
	// 入力チェック
	private function checkPost(){
		if(!count($this->parameters->getPostParameter())){
			throw new PostNullException();
		}
		$user = $this->dataMethods->fetchUserInfo(DataMethods::USER_COL);
		$pass = $this->dataMethods->fetchUserInfo(DataMethods::PASS_COL);
		if(
			$this->parameters->getPostParameter('user') !== $user ||
			hash('sha256', $this->parameters->getPostParameter('pass')) !== $pass
		){
			throw new CheckException('ユーザー名かパスワードが間違っています。');
		}

	
	}
}