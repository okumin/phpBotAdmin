<?php
/**
 * Copyright (c) 2012 okumin, http://okumin.com/
 */
 
// TwitterAPIを利用した、様々な操作を行う。
class ApiMode extends ModeBase
{
	private $twitterMethods; // Twitter操作クラス
	
	// API残量確認
	public function limitAction(){
		$this->template = 'limit';
		$this->menu['API実行可能回数確認'] = URL . '?mode=api&action=limit';
		try{
			$this->setTwitterMethods();
			$result = $this->twitterMethods->getAccountRate_limit_status();
			if(!isset($result->photos)){
				throw new Exception('OAuth情報が正しく設定されていません。');
			}
			$this->replace['SAFE_MESSAGE'] = '';
		}catch(Exception $e){
			$this->replace['SAFE_MESSAGE'] = self::ERROR1 . $e->getMessage() . self::ERROR2;
		}
		$this->replace['remaining'] = isset($e) ? '?' : $result->remaining_hits;
		$this->replace['time'] = isset($e) ? '?' : date('H:i:s', $result->reset_time_in_seconds);
	}
	
	// botアカウントのプロフィール確認
	public function profileAction(){
		$this->template = 'profile';
		$this->menu['プロフィール確認'] = URL . '?mode=api&action=profile';
		try{
			$this->setTwitterMethods();
			$result = $this->twitterMethods->getAccountVerify_credentials();
			if(isset($result->error)){
				throw new Exception('APIエラー: ' . $result->error);
			}
			$this->replace['SAFE_MESSAGE'] = '';
		}catch(Exception $e){
			$this->replace['SAFE_MESSAGE'] = self::ERROR1 . $e->getMessage() . self::ERROR2;
		}
		$this->replace['id'] = isset($e) ? '' : $result->id_str;
		$this->replace['name'] = isset($e) ? '' : $result->name;
		$this->replace['screen_name'] = isset($e) ? '' : $result->screen_name;
		$this->replace['followers_count'] = isset($e) ? '' : $result->followers_count;
		$this->replace['favorites_count'] = isset($e) ? '' : $result->favourites_count;
		$this->replace['description'] = isset($e) ? '' : $result->description;
		$this->replace['statuses_count'] = isset($e) ? '' : $result->statuses_count;
		$this->replace['friends_count'] = isset($e) ? '' : $result->friends_count;
		$this->replace['listed_count'] = isset($e) ? '' : $result->listed_count;
		$this->replace['profile_url'] = isset($e) ? '' : $result->url;
	}
	
	// 片思いフォローの確認&フォロー外し
	public function unfollowAction(){
		$this->template = 'unfollow';
		$this->menu['フォロー解除'] = URL . '?mode=api&action=unfollow';
		$unfollowUser = '';
		try{
			$this->setTwitterMethods();
			$this->checkUnfollowPost();
			$users = $this->parameters->getPostParameter('unfollow');
			foreach($users as $value){
				$this->twitterMethods->postFriendshipsDestroy($value);
			}
			$this->replace['SAFE_MESSAGE'] = '<div class="alert alert-success">フォローユーザーを整理しました。</div>';
		}catch(PostNullException $e_post){
			$this->replace['SAFE_MESSAGE'] = '';
		}catch(Exception $e){
			$this->replace['SAFE_MESSAGE'] = self::ERROR1 . $e->getMessage() . self::ERROR2;
		}
		try{
			$unfollowUser = $this->returnUnfollowUser();
			$this->replace['SAFE_unfollow'] = $this->formatUnfollowUser($unfollowUser);
		}catch(Exception $e){
			$this->replace['SAFE_MESSAGE'] = self::ERROR1 . $e->getMessage() . self::ERROR2;
			$this->replace['SAFE_unfollow'] = '';
		}
	}
	
	// 入力チェック(片思い)
	private function checkUnfollowPost(){
		if(!count($this->parameters->getPostParameter())){
			throw new PostNullException();
		}
		$users = $this->parameters->getPostParameter('unfollow');
		foreach($users as $value){
			if(!ctype_digit($value)){
				throw new CheckException('入力が不正です。');
			}
		}
	}
	
	// Twitter操作クラスのインスタンスを作成
	// OAuth情報が正しくない場合はエラー
	private function setTwitterMethods(){
		Config::setOAuth();
		$OAuth = Config::getOAuth();
		$this->twitterMethods = new TwitterMethods($OAuth['consumer_key'], $OAuth['consumer_secret'], $OAuth['access_token'], $OAuth['access_token_secret']);
		$result = $this->twitterMethods->getAccountRate_limit_status();
		if(isset($result->error)){
			throw new ApiException('APIエラー: ' . $result->error);
		}
	}
	
	// 片思いフォローをしているユーザーを返す
	private function returnUnfollowUser(){
		$result = $this->twitterMethods->getAccountVerify_credentials();
		if(isset($result->error)){
			throw new Exception('APIエラー: ' . $result->error);
		}
		$myId = $result->id_str;
		$followers = $this->twitterMethods->getAllFollowersIds('user_id', $myId);
		$friends = $this->twitterMethods->getAllFriendsIds('user_id', $myId);
		$ids = array_diff($friends, $followers);
		if(!count($ids)){
			return array();
		}
		$target = array();
		$unfollowUser = array();
		while($id = array_shift($ids)){
			$target[] = $id;
			if(count($target) > 99){
				$data = $this->twitterMethods->getUsersLookup($target);
				if(!isset($data->error)){
					$unfollowUser = array_merge($unfollowUser, $data);
					$target = array();
				}
			}
		}
		$data = $this->twitterMethods->getUsersLookup($target);
		if(!isset($data->error)){
			$unfollowUser = array_merge($unfollowUser, $data);
		}
		return $unfollowUser;
	}
	
	// 片思いフォローをしているユーザーの情報を出力する形式に加工する
	private function formatUnfollowUser(array $data){
		if(!count($data)){
			return '<div class="alert">現在片思いしているユーザーは存在しません。</div>';
		}
		$return = "<ul class=\"unstyled\">\n";
		foreach($data as $value){
			$return .= '<li><label class="checkbox"><input type="checkbox" name="unfollow[]" value="' . $value->id_str . '"> <a href="https://twitter.com/#!/' . $value->screen_name . '" target="_blank">@' . $value->screen_name . '</a></label></li>' . "\n";
		}
		$return .= "</ul>\n";
		return $return;
	}
}