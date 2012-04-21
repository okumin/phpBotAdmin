<?php
/**
 * Copyright (c) 2012 okumin, http://okumin.com/
 */
 
// エラー等、メッセージを格納するクラス
class Message
{
	private static $message = array(); // メッセージ
	
	// $messageのセッター
	public static function setMessage($str){
		self::$message[] = $str;
	}
	
	// $messageのゲッター
	public static function getMessage(){
		return self::$message;
	}
}