<?php
/**
 * Copyright (c) 2012 okumin, http://okumin.com/
 */
 
// テキストファイルのデータを操作するメソッド群
// DB版と対になっている
class DbMethods implements Methods
{
	/**
	 * DBを操作するメソッド群
	 * ファイル版と対応関係あり
	 */
	
	// データを読み込み、2次元配列で返す
	// 検索ワードはarray(列 => 値)で指定
	// 例:3列目の要素が1のデータのみ取得したい場合は$search = array(3 => '1')
	// 検索はAND検索
	public function read($name, array $select = array(), array $search = array()){
		return array(array());
	}
	
	// DBにデータを追加する
	// $dataは2次元配列で入力
	public function insert($name, array $data){
	}
	
	// DBのデータを削除する
	// 検索ワード(必須)はarray(列 => 値)で指定
	// 例:3列目の要素が1のデータのみ削除したい場合は$search = array(3 => '1')
	// 検索はAND検索
	public function delete($name, array $search){
	}
	
	// 上書き保存
	public function overWrite($name, array $data){
	}
	
	// 検索ワードに該当するデータを削除してから挿入
	// delete + insert
	public function update($name, $data, array $search){
	}
}