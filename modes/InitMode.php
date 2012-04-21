<?php
/**
 * Copyright (c) 2012 okumin, http://okumin.com/
 */
 
// 初期設定
require_once MODE_DIR . '/ConfigMode.php';
class InitMode extends ConfigMode
{
	// スタート画面
	public function indexAction(){
		$this->template = 'initStart';
		$this->menu = array('初期設定' => URL . '?mode=init');
	}
	
	// 初期設定完了画面
	public function endAction(){
		$this->template = 'initEnd';
		$this->menu = array('初期設定' => URL . '?mode=init&action=end');
	}
	
	// オーバーライド
	public function oauthAction(){
		$this->template = 'initStart';
	}
	public function oneSidedAction(){
		$this->template = 'initStart';
	}
}