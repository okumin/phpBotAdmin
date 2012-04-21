<?php
/**
 * Copyright (c) 2012 okumin, http://okumin.com/
 */
 
// GETパラメータ、POSTパラメータを格納するシングルトン
class Parameters
{
	private static $instance = NULL; // Parametersのインスタンスを格納(シングルトン)
	private $getParameter = array(); // GETパラメータ
	private $postParameter = array(); // POSTパラメータ
	
	// インスタンスを返す
	// インスタンス未作成の場合はインスタンス作成(シングルトン)
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new Parameters();
		}
		return self::$instance;
	}
	
	// GETパラメータ、POSTパラメータをセット
	private function __construct(){
		$this->getParameter = $this->convertData($_GET);
		$this->postParameter = $this->convertData($_POST);
	}
	
	// GETパラメータのゲッター
	public function getGetParameter($key = NULL){
		if(is_null($key)){
			return $this->getParameter;
		}elseif(isset($this->getParameter[$key])){
			return $this->getParameter[$key];
		}else{
			return NULL;
		}
	}
	
	// POSTパラメータのゲッター
	public function getPostParameter($key = NULL){
		if(is_null($key)){
			return $this->postParameter;
		}elseif(isset($this->postParameter[$key])){
			return $this->postParameter[$key];
		}else{
			return NULL;
		}
	}
	
	// データを安全な形に変換
	private function convertData(array $strs){
		foreach($strs as &$value){
			$value = Utils::convert_CRLF($value);
			$value = str_replace("\t", ' ', Utils::trim($value));
		}
		return $strs;
	}
}