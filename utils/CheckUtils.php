<?php
/**
 * Copyright (c) 2012 okumin, http://okumin.com/
 */
 
// 入力値をチェックするメソッド群
class CheckUtils
{
	const TRUE_VALUE = '1';
	
	// チェックボックスによる真偽判定のチェック
	public static function checkBool($str, $item){
		if(isset($str) && $str !== self::TRUE_VALUE && $str !== ''){
			throw new CheckException( $item . 'の入力値が不正です。');
		}
	}

	// 入力値が空であるかを確かめる
	public static function checkEmpty($str, $item){
		if($str == ''){
			throw new CheckException($item . 'は必須項目です。');
		}
	}
	
	// 入力値に改行が含まれているか調べる
	public static function checkLn($str, $item){
		if(strpos($str, "\n") !== FALSE){
			throw new CheckException($item . 'が改行を含んでいます。');
		}
	}
	
	// 入力値が半角英数字であるかチェック
	public static function checkHalfWidth($str, $item){
		if(!preg_match('/^[a-zA-Z0-9]+$/', $str)){
			throw new CheckException($item . 'は半角英数字のみ入力できます。');
		}
	}

	// 入力値が数値であるかチェック
	public static function checkDigit($str, $item){
		if(!ctype_digit($str)){
			throw new CheckException($item . 'は数値のみ入力できます。');
		}
	}
	
	// パターンをチェック
	public static function checkPattern($str){
		self::checkLn($str, 'パターン');
		self::checkEmpty($str, 'パターン');
	}
	
	// 実行時刻の分をチェック
	public static function checkMin($min){
		if(!self::checkCron($min, range(0, 59), '分')){
			throw new CheckException('実行時刻(分)を1つ以上選んでください。');
		}
	}

	// 実行時刻の時をチェック
	public static function checkHour($hour){
		if(!self::checkCron($hour, range(0, 23), '時')){
			throw new CheckException('実行時刻(時)を1つ以上選んでください。');
		}
	}

	// 実行時刻の日をチェック
	public static function checkDay($day){
		if(!self::checkCron($day, range(1, 31), '日')){
			throw new CheckException('実行時刻(日)を1つ以上選んでください。');
		}
	}

	// 実行時刻の月をチェック
	public static function checkMonth($month){
		if(!self::checkCron($month, range(1, 12), '月')){
			throw new CheckException('実行時刻(月)を1つ以上選んでください。');
		}
	}

	# 実行時刻の週をチェック
	public static function checkWeek($week){
		if(!self::checkCron($week, range(0, 6), '曜日')){
			throw new CheckException('実行時刻(曜日)を1つ以上選んでください。');
		}
	}

	// cronの入力値が正しいかチェック
	public static function checkCron(array $cron, $range, $type){
		$count = 0;
		foreach($cron as $key => $value){
			if(!in_array($key, $range) || ($value !== '1' && $value !== '')){
				throw new CheckException('実行時刻(' . $type . ')が不正です。');
			}
			if($value === '1'){
				$count++;
			}
		}
		if($count === 0){
			return FALSE;
		}else{
			return TRUE;
		}
	}
}