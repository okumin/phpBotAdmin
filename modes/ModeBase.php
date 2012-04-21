<?php
/**
 * Copyright (c) 2012 okumin, http://okumin.com/
 */
 
// modesの親クラス
class ModeBase
{
	const EDITED = '<div class="control-group" style="margin-top:5px;margin-bottom:5px;"><div class="message alert alert-success">変更しました。</div></div>';
	const ERROR1 = '<div class="control-group" style="margin-top:5px;margin-bottom:5px"><div class="message alert alert-error">';
	const ERROR2 = '</div></div>';
	
	protected $template; // テンプレートファイルの種類
	protected $replace = array(); // テンプレートにreplaceする変数
	protected $dataMethods; // データ更新クラスのインスタンスを格納
	protected $parameters; // パラメータを取得するクラス
	protected $message = array(); // メッセージ(エラー告知等)を格納
	protected $menu = array('HOME' => URL); // パンくずリストに表示するリンク
	
	// コンストラクタ
	// データ更新クラスのインスタンスを格納
	public function __construct($methods, $parameters){
		$this->dataMethods = $methods;
		$this->parameters = $parameters;
	}
	
	// $this->templateのゲッター
	public function getTemplate(){
		return $this->template;
	}
	
	// $this->replaceのセッター
	protected function setReplace(array $replace){
		foreach($replace as $key => $value){
			$this->replace[$key] = $value;
		}
	}
	
	// $this->replaceのゲッター
	public function getReplace(){
		return $this->replace;
	}
	
	// $this->menuのゲッター
	public function getMenu(){
		return $this->menu;
	}
	
	// indexMode
	// 実行するmodeが存在しない場合に呼び出される
	public function indexAction(){
		$this->template = 'index';
	}
}