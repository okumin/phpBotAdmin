<?php
/**
 * Copyright (c) 2012 okumin, http://okumin.com/
 */
 
// 登録された動作を能動的に実行する
class ExecBotMode extends ModeBase
{
	// 確認or実行
	public function indexAction(){
		$this->template = 'execBot';
	}
	
	// 命令実行
	public function execAction(){
		$this->template = 'exec';
		try{
			require_once BASE_DIR . '/ExecBot.php';
			require_once BASE_DIR . '/ExecBot.php';
			Config::setOAuth();
			Config::setBaseConfigs();
			$object = new ExecBot(getdate(), Config::getBaseConfigs(), Config::getOAuth());
			$this->replace['body'] = '実行完了しました。';
		}catch(Exception $e){
			$this->replace['body'] = $e->getMessage();
		}
	}
}