<?php
/**
 * Copyright (c) 2012 okumin, http://okumin.com/
 */
 
// DbMethodsかFileMethodsのインスタンスを作成するファクトリー
class MethodsFactory
{
	public static function create($use_database){
		if($use_database){
			require_once METHOD_DIR . '/DbMethods.php';
			return new DbMethods();
		}else{
			require_once METHOD_DIR . '/FileMethods.php';
			return new FileMethods();
		}
	}
}