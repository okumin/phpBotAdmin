<?php
/**
 * Copyright (c) 2012 okumin, http://okumin.com/
 */
 
// 拡張された例外クラス
// ApiUtilsで発生した例外
class ApiException extends Exception{}
// CheckUtilsで発生した例外
class CheckException extends Exception{}
// データの読み書きで発生した例外
class DataException extends Exception{}
// TwitterAPI実行時に発生した例外
class TwitterException extends Exception{}
// Utilsで発生した例外
class UtilsException extends Exception{}
// POSTパラメータが送信されていなかった場合の例外
class PostNullException extends Exception{}