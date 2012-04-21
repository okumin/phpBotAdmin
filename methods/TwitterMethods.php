<?php
/**
 * Copyright (c) 2012 okumin, http://okumin.com/
 */
 
// TwitterAPIを操作するクラス
class TwitterMethods
{
	private $OAuth; // twitteroauthクラスのインスタンス
	
	public function __construct($consumer_key, $consumer_secret, $access_token, $access_token_secret){
		$this->setOAuth($consumer_key, $consumer_secret, $access_token, $access_token_secret);
	}
	
	// $this->OAuthのセッター
	public function setOAuth($consumer_key, $consumer_secret, $access_token, $access_token_secret){
		$this->OAuth = new TwitterOAuth(
			$consumer_key,
			$consumer_secret,
			$access_token,
			$access_token_secret
		);
	}

	// home_timelineを取得
	public function getHome_timeline($count = '50', $since_id){
		if(!ctype_digit($since_id)){
			$since_id = '1';
		}
		$result = $this->getStatusesHome_timeline($count, $since_id);
		if(isset($result->error)){
			Utils::reportError(time(), 'TwitterMethods::getHome_timeline', $result->error);
			return FALSE;
		}elseif(count($result) === 0){
			return FALSE;
		}
		return $result;
	}
	
	// mentionを取得
	public function getMentions($count = '50', $since_id){
		if(!ctype_digit($since_id)){
			$since_id = '1';
		}
		$result = $this->getStatusesMentions($count, $since_id);
		if(isset($result->error)){
			Utils::reportError(time(), 'TwitterMethods::getMentions', $result->error);
			return FALSE;
		}elseif(count($result) === 0){
			return FALSE;
		}
		return $result;
	}
	
	// screen_nameから、user_idを取得
	public function getUserIds(array $screen_names){
		$data = $this->getUsersLookup($screen_names, 'screen_name');
		$return = array();
		if(isset($data->errors)){
			return $return;
		}
		foreach($data as $value){
			$return[] = $value->id_str;
		}
		return $return;
	}
	
	// user_idから、screen_nameを取得
	public function getScreenNames(array $user_ids){
		$data = $this->getUsersLookup($user_ids, 'user_id');
		$return = array();
		if(isset($data->errors) || is_null($data)){
			return $return;
		}
		foreach($data as $value){
			$return[] = $value->screen_name;
		}
		return $return;
	}
	
	// 全フォロワーを取得
	public function getAllFollowersIds($type, $search){
		$cursor = '-1';
		$ids = array();
		while(TRUE){
			$result = $this->getFollowersIds($type, $search, $cursor);
			$ids = array_merge($ids, $result->ids);
			if(!isset($result->error) && $result->next_cursor_str !== '0'){
				$cursor = $result->next_cursor_str;
			}else{
				break;
			}
		}
		return $ids;
	}
	
	// 全フォロー中ユーザーを取得
	public function getAllFriendsIds($type, $search){
		$cursor = '-1';
		$ids = array();
		while(TRUE){
			$result = $this->getFriendsIds($type, $search, $cursor);
			$ids = array_merge($ids, $result->ids);
			if(!isset($result->error) && $result->next_cursor_str !== '0'){
				$cursor = $result->next_cursor_str;
			}else{
				break;
			}
		}
		return $ids;
	}	
	
	////////////////////////////////////////
	//////////APIを実行するメソッド群
	////////////////////////////////////////
	
	// home_timelineを取得
	public function getStatusesHome_timeline($count = '50', $since_id = '1'){
		if(!ctype_digit($since_id)){
			$since_id = '1';
		}
		$result = $this->requestApi(
			'get',
			'statuses/home_timeline',
			array('count' => $count, 'since_id' => $since_id)
		);
		return $result;
	}
	
	// mentionを取得
	public function getStatusesMentions($count = '50', $since_id = '1'){
		if(!ctype_digit($since_id)){
			$since_id = '1';
		}
		$result = $this->requestApi(
			'get',
			'statuses/mentions',
			array('count' => $count, 'since_id' => $since_id)
		);
		return $result;
	}
	
	// つぶやき投稿
	public function postStatusesUpdate($status, $in_reply_to_status_id = '1'){
		$result = $this->requestApi(
			'post',
			'statuses/update',
			array('status' => $status, 'in_reply_to_status_id' => $in_reply_to_status_id)
		);
		return $result;
	}

	// 公式RT
	public function postStatusesRetweet($id){
		$result = $this->requestApi(
			'post',
			'statuses/retweet/' . $id
		);
		return $result;
	}
	
	// フォロワーのIDを取得
	public function getFollowersIds($type, $search, $cursor = '-1'){
		if($type != 'screen_name'){
			$type = 'user_id';
		}
		$result = $this->requestApi(
			'get',
			'followers/ids',
			array($type => $search, 'cursor' => $cursor, 'stringify_ids' => 'true')
		);
		return $result;
	}
	
	// フォローしているユーザーのIDを取得
	public function getFriendsIds($type, $search, $cursor = '-1'){
		if($type != 'screen_name'){
			$type = 'user_id';
		}
		$result = $this->requestApi(
			'get',
			'friends/ids',
			array($type => $search, 'cursor' => $cursor, 'stringify_ids' => 'true')
		);
		return $result;
	}
	
	// フォロー
	public function postFriendshipsCreate($target, $type = 'user_id'){
		if($type != 'user_id'){
			$type = 'screen_name';
		}
		$result = $this->requestApi(
			'post',
			'friendships/create',
			array($type => $target)
		);
		return $result;
	}
	
	// アンフォロー
	public function postFriendshipsDestroy($target, $type = 'user_id'){
		if($type != 'user_id'){
			$type = 'screen_name';
		}
		$result = $this->requestApi(
			'post',
			'friendships/destroy',
			array($type => $target)
		);
		return $result;
	}
	
	// お気に入り登録
	public function postFavoritesCreate($id){
		$result = $this->requestApi(
			'post',
			'favorites/create/' . $id
		);
		return $result;
	}
	
	// users/lookupを実行
	public function getUsersLookup(array $search, $type = 'user_id'){
		if($type != 'screen_name'){
			$type = 'user_id';
		}
		$search = implode(',', $search);
		$result = $this->requestApi(
			'get',
			'users/lookup',
			array($type => $search)
		);
		return $result;
	}
	
	// API残量チェック
	public function getAccountRate_limit_status(){
		$result = $this->requestApi(
			'get',
			'account/rate_limit_status'
		);
		return $result;
	}
	
	// 認証チェック
	public function getAccountVerify_credentials(){
		$result = $this->requestApi(
			'get',
			'account/verify_credentials'
		);
		return $result;
	}
	
	// API実行
	private function requestApi($method, $api, array $parameters = array()){
		if($method === 'post'){
			$result = $this->OAuth->post($api, $parameters);
		}else{
			$result = $this->OAuth->get($api, $parameters);
		}
		if(is_null($result)){
			throw new TwitterException('APIを実行できません。');
		}
		return $result;
	}
}