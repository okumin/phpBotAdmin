<?php
/**
 * Copyright (c) 2012 okumin, http://okumin.com/
 */
 
// テキストファイルのデータを操作するメソッド群
// DB版と対になっている
class FileMethods implements Methods
{
	const ROW_DELIMITER = "\n"; // 行の区切り文字
	const COL_DELIMITER = "\t"; // 列の区切り文字
	
	/**
	 * ファイルを操作するメソッド群
	 * DB版と対応関係あり
	 */
	
	// データを読み込み、2次元配列で返す
	// 検索ワードはarray(列 => 値)で指定
	// 例:3列目の要素が1のデータのみ取得したい場合は$search = array(3 => '1')
	// 検索はAND検索
	public function read($name, array $select = array(), array $search = array()){
		$filename = DATA_DIR . "/{$name}.txt";
		Utils::check_right($filename, Utils::READ);
		$lines = explode(self::ROW_DELIMITER, file_get_contents($filename));
		$return = array();
		foreach($lines as $value){
			$exploded = explode(self::COL_DELIMITER, $value);
			if(count($search)){
				foreach($search as $key => $search_value){
					if(array_key_exists($key, $exploded) && $exploded[$key] !== $search_value){
						continue 2;
					}
				}
			}
			if(count($select)){
				$return[] = $this->selectCol($exploded, $select);
			}else{
				$return[] = $exploded;
			}
		}
		if(count($return)){
			return $return;
		}else{
			return array(array());
		}
	}
	
	// テキストファイルにデータを追加する
	// $dataは2次元配列で入力
	public function insert($name, array $data){
		$filename = DATA_DIR . "/{$name}.txt";
		Utils::check_right($filename, Utils::WRITE);
		foreach($data as &$value){
			if(!is_array($value)){
				throw new UtilsException('FileMethods::insert:「$data」は2次元配列で入力してください。');
			}
			$value = implode(self::COL_DELIMITER, $value);
		}
		if(filesize($filename) !== 0){
			$delimiter = self::ROW_DELIMITER;
		}else{
			$delimiter = '';
		}
		$inserted = $delimiter . implode(self::ROW_DELIMITER, $data);
		if(file_put_contents($filename, $inserted, FILE_APPEND | LOCK_EX) === FALSE){
			throw new DataException("FileMethods::insert:「{$filename}」に書き込みできませんでした。");
		}
	}
	
	// テキストファイルのデータを削除する
	// 検索ワード(必須)はarray(列 => 値)で指定
	// 例:3列目の要素が1のデータのみ削除したい場合は$search = array(3 => '1')
	// 検索はAND検索
	public function delete($name, array $search){
		$filename = DATA_DIR . "/{$name}.txt";
		if(!count($search)){
			throw DataException("FileMethods::delete:検索ワードは必須です。");
		}
		Utils::check_right($filename, Utils::READ_AND_WRITE);
		$lines = explode(self::ROW_DELIMITER, file_get_contents($filename));
		$notDeleted = array();
		foreach($lines as $value){
			$exploded = explode(self::COL_DELIMITER, $value);
			foreach($search as $key => $search_value){
				if($exploded[$key] !== $search_value){
					$notDeleted[] = implode(self::COL_DELIMITER, $exploded);
				}
			}
		}
		$text = implode(self::ROW_DELIMITER, $notDeleted);
		if(file_put_contents($filename, $text, LOCK_EX) === FALSE){
			throw new DataException("FileMethods::delete:「{$filename}」に書き込みできませんでした。");
		}
	}
	
	// 上書き保存
	public function overWrite($name, array $data){
		$filename = DATA_DIR . "/{$name}.txt";
		Utils::check_right($filename, Utils::WRITE);
		foreach($data as &$value){
			if(!is_array($value)){
				throw new DataException('FileMethods::overWrite:「$data」は2次元配列で入力してください。');
			}
			$value = implode(self::COL_DELIMITER, $value);
		}
		$data = implode(self::ROW_DELIMITER, $data);
		if(file_put_contents($filename, $data, LOCK_EX) === FALSE){
			throw new DataException("FileMethods::overWrite:「{$filename}」に書き込みできませんでした。");
		}
	}
	
	// 検索ワードに該当するデータを削除してから挿入
	// delete + insert
	public function update($name, $data, array $search){
		$this->delete($name, $search);
		if(isset($data)){
			$this->insert($name, $data);
		}
	}
	
	/**
	 * 取得したデータをパースするメソッド
	 */
	 
	// 指定した列の要素を配列にして返す
	public function selectCol(array $data, array $col){
		$return = array();
		foreach($col as $value){
			if(!isset($data[$value])){
				throw new DataException('$data[' . $value . ']は定義されていません。');
			}
			$return[] = $data[$value];
		}
		return $return;
	}
}