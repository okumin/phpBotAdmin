<?php
/**
 * Copyright (c) 2012 okumin, http://okumin.com/
 */
 
// ソフトウェア内で用いる、便利な関数を定義
class Utils
{
	// 1次元配列を2次元配列に
	// array(a, b) → array(array(a), array(b))
	public static function arrayToArrays(array $array){
		foreach($array as &$value){
			$value = array($value);
		}
		return $array;
	}
	
	// 2次元配列を1次元配列に
	// array(array(a), array(b)) → array(a, b)
	public static function arraysToArray(array $array){
		foreach($array as &$value){
			if(count($value) > 1){
				throw new UtilsException('Utils::arraysToArray:引数が不正です。');
			}elseif(count($value) === 1){
				$value = $value[0];
			}else{
				$value = '';
			}
		}
		return $array;
	}
	
	// 2次元配列の各要素にarray_unshift
	// array(array(a, b), array(c, d)) → array(array(new, a, b), array(new, c, d)
	public static function arrays_unshift(array $array, $var){
		foreach($array as &$value){
			array_unshift($value, $var);
		}
		return $array;
	}
	
	// 2次元配列が実質空であるか確かめる
	public static function emptyArrays(array $array){
		if(count($array) && (!count($array[0]) || $array[0][0]  === '')){
			return TRUE;
		}else{
			return FALSE;
		}
	}

	// エスケープ
	public static function h($text, $charset = null){
		if(is_array($text)){
			return array_map(array('Utils', 'h'), $text);
		}
		if(empty($charset)){
			$charset = 'UTF-8';
		}
		return htmlspecialchars($text, ENT_QUOTES, $charset);
	}
	
	// ファイルの権限チェック
	const READ_AND_WRITE = 0; // 読み書き両方チェック
	const READ = 1; // 読み込み権限のみチェック
	const WRITE = 2; // 書き込み権限のみチェック
	public static function check_right($filename, $type = self::READ_AND_WRITE){
		$return = array();
		if(!file_exists($filename)){
				throw new UtilsException("「{$filename}」が存在しません。");
		}
		if($type === self::READ_AND_WRITE){
			if(!is_readable($filename) && !is_writable($filename) && !@chmod($filename, 0666)){
				throw new UtilsException("「{$filename}」に読み込み・書き込み権限がありません。");
			}
		}
		if($type !== self::WRITE){
			if(!is_readable($filename) && !@chmod($filename, 0666)){
				throw new UtilsException("「{$filename}」に読み込み権限がありません。");
			}
		}
		if($type !== self::READ){
			if(!is_writable($filename) && !@chmod($filename, 0666)){
				throw new UtilsException("「{$filename}」に書き込み権限がありません。");
			}
		}
	}
	
	// 改行コードを統一
	public static function convert_CRLF($text){
		if(is_array($text)){
			return array_map(array('Utils', 'convert_CRLF'), $text);
		}
		return str_replace(array("\r\n","\r"), "\n", $text);
	}
	
	// 配列にも対応したtrim
	public static function trim($text){
		if(is_array($text)){
			return array_map(array('Utils', 'trim'), $text);
		}
		return trim($text);
	}
	
	// 指定したディレクトリに保存されているファイル名を取得
	public static function getFilenames($dir){
		if(!is_dir($dir)){
			throw new UtilsException("「{$dir}」というディレクトリは存在しません。");
		}
		$filenames = array();
		if($handle = opendir($dir)){
			while(false !== ($filename = readdir($handle))){
 				if($filename != "." && $file != ".."){
 					$filesnames[] = $filename;
  				}
  			}
  			closedir($handle);
  		}
  		return $filenamess;
  	}
  	
  	// エラー処理
  	public static function reportError($timestamp, $methodname, $message){
  	}
	
	// リダイレクト
	public static function jumpToNewURL(array $queries,array $addQueries = array()){
			foreach($addQueries as $key => $value){
				$queries[$key] = $value;
			}
			$newQueries = array();
			foreach($queries as $key => $value){
				$newQueries[] = $key . '=' . $value;
			}
			$newQuery = implode('&', $newQueries);
			if(PORT === '80'){
				$port = '';
			}else{
				$port = ':' . PORT;
			}
			if(!count($newQueries)){
	    		header("Location: http://{$_SERVER['SERVER_NAME']}{$port}{$_SERVER['SCRIPT_NAME']}");
	    	}else{
				header("Location: http://{$_SERVER['SERVER_NAME']}{$port}{$_SERVER['SCRIPT_NAME']}?{$newQuery}");
			}
			exit;
	}
}