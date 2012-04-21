<?php
/**
 * Copyright (c) 2012 okumin, http://okumin.com/
 */
 
// dataディレクトリに保存されているデータを扱うクラス
class DataMethods
{
	const TEXTAREA_DELIMITER = "\n";
	const MIN = 0;
	const HOUR = 1;
	const DAY = 2;
	const MONTH = 3;
	const WEEK = 4;
	const AUTO_TWEET_TYPE = '0';
	const REACT_HOME_TYPE = '1';
	const REACT_MENTION_TYPE = '2';
	
	// データのカラム番号
	const GROUP_ID_COL = 0; // グループID
	const GROUP_NAME_COL = 1; // グループ名
	const GROUP_RUN_COL = 2; // 動作状況
	const GROUP_TYPE_COL = 3; // グループタイプ(tweet, home, mention)
	const GROUP_LIMIT_COL = 4; // 動作の有効期間
	const CRON_COL = 1; // cronデータ
	const COMMENTS_COL = 1; // comments
	const PATTERN_COL = 1; // パターン
	const RETWEET_COL = 2; // リツイート
	const FAVORITE_COL = 3; // お気に入り登録
	const CONSUMER_KEY_COL = 0;
	const CONSUMER_SECRET_COL = 1;
	const ACCESS_TOKEN_COL = 2;
	const ACCESS_TOKEN_SECRET_COL = 3;
	const USER_COL = 0; // ユーザー名
	const PASS_COL = 1; // パスワード
	const HOME_TIMELINE_COL = 0; // home_timelineの取得済みID
	const MENTION_COL = 1; // mentionの取得済みID
	const AUTO_FOLLOW_COL = 0; // 自動フォロー返し
	const CUT_COL = 1; // 超過分のカット

	private $methods; // FileMethodsクラスのインスタンスを保存
	private $parameters = array(); // パラメータを格納
	private $groupId; // グループIDを格納
	private $groupData = array(); // groupDataを格納
	private $cron = array(); // cron情報を格納
	private $comments = array(); // commentsの情報を格納
	private $reactions = array(); // reactionsの情報を格納
	
	// コンストラクタ
	// ファイル操作用メソッドを格納
	public function __construct($methods){
		if(!is_object($methods)){
			throw new DataException("DataMehtods::__construct:コンストラクタの引数が不適切です。");
		}
		$this->methods = $methods;
	}
	
	// データ操作メソッドをGET
	public function getMethods(){
		return $this->methods;
	}
	
	// POSTパラメータをセット
	public function setParameters($parameters){
		$this->parameters = $parameters;
	}
	
	// groupIdをセット
	// 引数がNULLの場合はデータディレクトリから新しい値を取得
	public function setGroupId($id = NULL){
		if(isset($id)){
			if(!ctype_digit($id)){
				throw new DataException('DataMehtods::setGroupId:グループIDの値が不正です。');
			}
		}else{
			$id = $this->methods->read('groupId');
			$id = ctype_digit($id[0][0]) ? $id[0][0] : '1';
			if(!ctype_digit($id)){
				throw new DataException('DataMehtods::setGroupId:「groupId」の値が不正です。');
			}
			$nextId = $id + 1;
			$this->methods->overWrite('groupId', array(array($nextId)));
		}
		$this->groupId = $id;
	}

	// groupIdを取得
	public function getGroupId(){
		return $this->groupId;
	}
	
	/**
	 * データを更新するメソッド群
	 */
	
	// groupDataを更新
	public function updateGroupData($type){
		if(!isset($this->groupId)){
			throw new DataException('DataMehtods::updateGroupData:グループIDがセットされていません。');
		}
		$data = array(array(
			$this->groupId,
			$this->parameters->getPostParameter('groupName'),
			$this->parameters->getPostParameter('groupRun'),
			$type,
			$this->parameters->getPostParameter('groupLimit')
		));
		$this->methods->update('groupData', $data, array(self::GROUP_ID_COL => $this->groupId));
	}
	
	// cronを更新
	public function updateCron(){
		if(!isset($this->groupId)){
			throw new DataException('DataMehtods::updateCron:グループIDがセットされていません。');
		}
		$min = (!is_null($this->parameters->getPostParameter('min')) && count($this->fetchTrueValue($this->parameters->getPostParameter('min'))) < 60) ? $this->fetchTrueValue($this->parameters->getPostParameter('min')) : array('-1');
		$hour = (!is_null($this->parameters->getPostParameter('hour')) && count($this->fetchTrueValue($this->parameters->getPostParameter('hour'))) < 24) ? $this->fetchTrueValue($this->parameters->getPostParameter('hour')) : array('-1');
		$day = (!is_null($this->parameters->getPostParameter('day')) && count($this->fetchTrueValue($this->parameters->getPostParameter('day'))) < 31) ? $this->fetchTrueValue($this->parameters->getPostParameter('day')) : array('-1');
		$month = (!is_null($this->parameters->getPostParameter('month')) && count($this->fetchTrueValue($this->parameters->getPostParameter('month'))) < 12) ? $this->fetchTrueValue($this->parameters->getPostParameter('month')) : array('-1');
		$week = (!is_null($this->parameters->getPostParameter('week')) && count($this->fetchTrueValue($this->parameters->getPostParameter('week'))) < 7) ? $this->fetchTrueValue($this->parameters->getPostParameter('week')) : array('-1');
		$this->methods->update('min', Utils::arrays_unshift(Utils::arrayToArrays($min), $this->groupId), array(self::GROUP_ID_COL => $this->groupId));
		$this->methods->update('hour', Utils::arrays_unshift(Utils::arrayToArrays($hour), $this->groupId), array(self::GROUP_ID_COL => $this->groupId));
		$this->methods->update('day', Utils::arrays_unshift(Utils::arrayToArrays($day), $this->groupId), array(self::GROUP_ID_COL => $this->groupId));
		$this->methods->update('month', Utils::arrays_unshift(Utils::arrayToArrays($month), $this->groupId), array(self::GROUP_ID_COL => $this->groupId));
		$this->methods->update('week', Utils::arrays_unshift(Utils::arrayToArrays($week), $this->groupId), array(self::GROUP_ID_COL => $this->groupId));
	}
	
	// commentを更新
	public function updateComments(){
		if(!isset($this->groupId)){
			throw new DataException('DataMehtods::updateComment:グループIDがセットされていません。');
		}
		if($this->parameters->getPostParameter('comments') === ''){
			return;
		}
		$comments = explode(self::TEXTAREA_DELIMITER, $this->parameters->getPostParameter('comments'));
		$comments = Utils::arrays_unshift(Utils::arrayToArrays($comments), $this->groupId);
		$this->methods->update('comments', $comments, array(self::GROUP_ID_COL => $this->groupId));
	}
	
	// reactionsを更新
	public function updateReactions(){
		if(!isset($this->groupId)){
			throw new DataException('DataMehtods::updateReactions:グループIDがセットされていません。');
		}
		$reaction = array(array($this->groupId, $this->parameters->getPostParameter('pattern'), $this->parameters->getPostParameter('retweet'), $this->parameters->getPostParameter('favorite')));
		$this->methods->update('reactions', $reaction, array(self::GROUP_ID_COL => $this->groupId));
	}
	
	// oneSidedを更新
	public function updateOneSided($oneSided){
		$data = Utils::arrayToArrays($oneSided);
		$this->methods->overWrite('oneSided', $data);
	}
	
	// OAuth情報を更新
	public function updateOAuth(){
		$data = array(array($this->parameters->getPostParameter('consumer_key'), $this->parameters->getPostParameter('consumer_secret'), $this->parameters->getPostParameter('access_token'), $this->parameters->getPostParameter('access_token_secret')));
		$this->methods->overWrite('OAuth', $data);
	}
	
	// ユーザー情報を更新
	public function updateUserInfo(){
		$data = array(array($this->parameters->getPostParameter('user'), hash('sha256', $this->parameters->getPostParameter('pass'))));
		$this->methods->overWrite('userInfo', $data);
	}
	
	// タイムライン取得済みIDを更新
	public function updateSinceId($sinceId){
		$data = array(array($sinceId));
		$this->methods->overWrite('sinceId', $data);
	}
	
	// 基本設定を更新
	public function updateBaseConfigs(){
		$data = array(array(
			$this->parameters->getPostParameter('autoFollow'),
			$this->parameters->getPostParameter('cut')
		));
		$this->methods->overWrite('baseConfigs', $data);
	}
	
	/**
	 * データを取得するメソッド群
	 */
	
	// groupNameを取得
	public function fetchGroupName($id){
		$data = $this->fetchFromGroupData($id, self::GROUP_NAME_COL);
		return (!Utils::emptyArrays($data) || isset($data[0][0]) && $data[0][0] ==='') ? $data[0][0] : NULL;
	}
	
	// 動作状況を取得
	public function fetchGroupRun($id){
		$data = $this->fetchFromGroupData($id, self::GROUP_RUN_COL);
		return !Utils::emptyArrays($data) ? $data[0][0] : NULL;
	}
	
	// 動作の有効期間を取得
	public function fetchGroupLimit($id){
		$data = $this->fetchFromGroupData($id, self::GROUP_LIMIT_COL);
		return (!Utils::emptyArrays($data) || isset($data[0][0]) && $data[0][0] ==='') ? $data[0][0] : NULL;
	}
	
	// minを取得
	public function fetchMin($id){
		$data = $this->fetchFromCron($id, self::MIN);
		$data = Utils::arraysToArray($data);
		if(isset($data[0]) && $data[0] === '-1'){
			$data = range(0, 59);
		}
		$return = array();
		for($i = 0; $i < 60; $i++){
			if(in_array($i, $data)){
				$return[$i] = '1';
			}else{
				$return[$i] = '0';
			}
		}
		return $return;
	}
	
	// hourを取得
	public function fetchHour($id){
		$data = $this->fetchFromCron($id, self::HOUR);
		$data = Utils::arraysToArray($data);
		if(isset($data[0]) && $data[0] === '-1'){
			$data = range(0, 23);
		}
		$return = array();
		for($i = 0; $i < 24; $i++){
			if(in_array($i, $data)){
				$return[$i] = '1';
			}else{
				$return[$i] = '0';
			}
		}
		return $return;
	}
	
	// dayを取得
	public function fetchDay($id){
		$data = $this->fetchFromCron($id, self::DAY);
		$data = Utils::arraysToArray($data);
		if(isset($data[0]) && $data[0] === '-1'){
			$data = range(1, 31);
		}
		$return = array();
		for($i = 1; $i < 32; $i++){
			if(in_array($i, $data)){
				$return[$i] = '1';
			}else{
				$return[$i] = '0';
			}
		}
		return $return;
	}
	
	// monthを取得
	public function fetchMonth($id){
		$data = $this->fetchFromCron($id, self::MONTH);
		$data = Utils::arraysToArray($data);
		if(isset($data[0]) && $data[0] === '-1'){
			$data = range(1, 12);
		}
		$return = array();
		for($i = 1; $i < 13; $i++){
			if(in_array($i, $data)){
				$return[$i] = '1';
			}else{
				$return[$i] = '0';
			}
		}
		return $return;
	}
	
	// weekを取得
	public function fetchWeek($id){
		$data = $this->fetchFromCron($id, self::WEEK);
		$data = Utils::arraysToArray($data);
		if(isset($data[0]) && $data[0] === '-1'){
			$data = range(0, 6);
		}
		$return = array();
		for($i = 0; $i < 7; $i++){
			if(in_array($i, $data)){
				$return[$i] = '1';
			}else{
				$return[$i] = '0';
			}
		}
		return $return;
	}
	
	// commentsを取得
	public function fetchComments($id){
		$data = $this->fetchFromComments($id);
		return Utils::arraysToArray($data);
	}
	
	// パターンを取得
	public function fetchPattern($id){
		$data = $this->fetchFromReactions($id, self::PATTERN_COL);
		return !Utils::emptyArrays($data) ? $data[0][0] : NULL;
	}
	
	// retweetを取得
	public function fetchRetweet($id){
		$data = $this->fetchFromReactions($id, self::RETWEET_COL);
		return !Utils::emptyArrays($data) ? $data[0][0] : NULL;
	}
	
	// retweetを取得
	public function fetchFavorite($id){
		$data = $this->fetchFromReactions($id, self::FAVORITE_COL);
		return !Utils::emptyArrays($data) ? $data[0][0] : NULL;
	}
	
	// フォロー返ししないIDを取得
	public function fetchOneSided(){
		$data = $this->methods->read('oneSided');
		$data = Utils::arraysToArray($data);
		return $data;
	}
	
	// OAuth情報のどれかを取得
	public function fetchOAuth($col){
		$data = $this->methods->read('OAuth', array());
		return isset($data[0][$col]) && $data[0][$col] !== '' ? $data[0][$col] : '';
	}
	
	// userInfoのどれかを取得
	public function fetchUserInfo($col){
		$data = $this->methods->read('userInfo', array());
		return (isset($data[0][$col]) && $data[0][$col] !== '') ? $data[0][$col] : '';
	}
	
	// タイムラインの取得済みIDを取得
	public function fetchSinceId(){
		$data = $this->methods->read('sinceId');
		return !Utils::emptyArrays($data) ? $data[0][0] : '';
	}
	
	// 基本設定を取得
	public function fetchBaseConfigs($col){
		$data = $this->methods->read('baseConfigs');
		return isset($data[0][$col]) ? $data[0][$col] : '';
	}

	// groupDataの情報を取得
	public function fetchFromGroupData($id, $col){
		if(!isset($this->groupData[$id])){
			$this->setGroupData($id);
		}
		$return = array();
		foreach($this->groupData[$id] as $value){
			if(count($value) !== 5){
				continue;
			}
			$return[] = array($value[$col]);
		}
		if(!count($return)){
			$return = array(array());
		}
		return $return;
	}
	
	// cron情報を取得
	public function fetchFromCron($id, $type){
		if(!isset($this->cron[$id])){
			$this->setCron($id);
		}
		return $this->cron[$id][$type];
	}
	
	// commentsの情報を取得
	public function fetchFromComments($id){
		if(!isset($this->comments[$id])){
			$this->setComments($id);
		}
		return $this->comments[$id];
	}
	
	// reactionsの情報を取得
	public function fetchFromReactions($id, $col){
		if(!isset($this->reactions[$id])){
			$this->setReactions($id);
		}
		$return = array();
		foreach($this->reactions[$id] as $value){
			$return[] = array($value[$col - 1]);
		}
		return $return;
	}
	
	/**
	 * テーブルごとにデータをセットするメソッド群
	 */
	
	// groupDataをセット
	public function setGroupData($id){
		if(!ctype_digit($id)){
			throw new DataException('DataMehtods::setGroupData:グループIDの値が不正です。');
		}
		$this->groupData[$id] = $this->methods->read('groupData', array(self::GROUP_ID_COL, self::GROUP_NAME_COL, self::GROUP_RUN_COL, self::GROUP_TYPE_COL, self::GROUP_LIMIT_COL), array(self::GROUP_ID_COL => "$id"));		
	}
	
	// cronをセット
	public function setCron($id){
		if(!ctype_digit($id)){
			throw new DataException('DataMehtods::setCron:グループIDの値が不正です。');
		}
		$this->cron = array();
		$this->cron[$id][self::MIN] = $this->methods->read('min', array(self::CRON_COL), array(self::GROUP_ID_COL => "$id"));
		$this->cron[$id][self::HOUR] = $this->methods->read('hour', array(self::CRON_COL), array(self::GROUP_ID_COL => "$id"));
		$this->cron[$id][self::DAY] = $this->methods->read('day', array(self::CRON_COL), array(self::GROUP_ID_COL => "$id"));
		$this->cron[$id][self::MONTH] = $this->methods->read('month', array(self::CRON_COL), array(self::GROUP_ID_COL => "$id"));
		$this->cron[$id][self::WEEK] = $this->methods->read('week', array(self::CRON_COL), array(self::GROUP_ID_COL => "$id"));
	}
	
	// commentsをセット
	public function setComments($id){
		if(!ctype_digit($id)){
			throw new DataException('DataMehtods::setComments:グループIDの値が不正です。');
		}
		$this->comments[$id] = $this->methods->read('comments', array(self::COMMENTS_COL), array(self::GROUP_ID_COL => $id));
	}
	
	// reactionsをセット
	public function setReactions($id){
		if(!ctype_digit($id)){
			throw new DataException('DataMehtods::setReactions:グループIDの値が不正です。');
		}
		$this->reactions[$id] = $this->methods->read('reactions', array(self::PATTERN_COL, self::RETWEET_COL, self::FAVORITE_COL), array(self::GROUP_ID_COL => $id));
	}
	
	// 値がTRUE(1)のキーのみを返す
	private function fetchTrueValue($array){
		$return = array();
		foreach($array as $key => $value){
			if($value === '1'){
				$return[] = $key;
			}
		}
		return $return;
	}
	
	// 指定したグループIDのデータを消す
	public function deleteData($groupId){
		$this->methods->delete('min', array(DataMethods::GROUP_ID_COL => $groupId));
		$this->methods->delete('hour', array(DataMethods::GROUP_ID_COL => $groupId));
		$this->methods->delete('day', array(DataMethods::GROUP_ID_COL => $groupId));
		$this->methods->delete('month', array(DataMethods::GROUP_ID_COL => $groupId));
		$this->methods->delete('week', array(DataMethods::GROUP_ID_COL => $groupId));
		$this->methods->delete('comments', array(DataMethods::GROUP_ID_COL => $groupId));
		$this->methods->delete('reactions', array(DataMethods::GROUP_ID_COL => $groupId));
		$this->methods->delete('groupData', array(DataMethods::GROUP_ID_COL => $groupId));
	}
}