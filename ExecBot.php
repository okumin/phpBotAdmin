<?php
/**
 * Copyright (c) 2012 okumin, http://okumin.com/
 */
 
// 命令実行
class ExecBot
{
	// 正規表現用パターン
	const URL_PATTERN = 'https?(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)';
	const SCREEN_NAME_PATTERN = '@[a-z0-9_]+';
	const HASHTAG_PATTERN = '(#|＃)[_a-zA-ZＡ-Ｚａ-ｚ０-９ぁ-ヶ亜-黑]+';

	private $now; // 現在の分・時・日・月・週
	private $dataMethods; // データ操作クラス
	private $twitterMethods; // TwitterAPI操作クラス
	private $myId;
	private $statuses_info = array(); // 反応するつぶやきの情報
	private $sindeId = array(); // 取得済みツイートのID
	private $baseConfigs = array(); // 設定

	public function __construct($now, $baseConfigs, $OAuth){
		try{
			$this->setNow($now);
			$this->baseConfigs = $baseConfigs;
			$this->dataMethods = new DataMethods(MethodsFactory::create(Config::USE_DATABASE));
			$this->sinceId[] = $this->dataMethods->fetchSinceId();
			$this->twitterMethods = new TwitterMethods($OAuth['consumer_key'], $OAuth['consumer_secret'], $OAuth['access_token'], $OAuth['access_token_secret']);
			
			$this->checkOAuth(); // OAuth認証を行えるかチェック
			$groupId = $this->checkTime($this->now); // 実行時刻と設定時刻がマッチしているIDを格納
			$actions = $this->checkGroupData($groupId, $now); // groupDataと照らし合わせる
			$this->setTweetsInfo($actions[DataMethods::AUTO_TWEET_TYPE]); // 自動ツイート情報を格納
			$this->setHomeReactions($actions[DataMethods::REACT_HOME_TYPE]); // TL反応を格納
			$this->setMentionReactions($actions[DataMethods::REACT_MENTION_TYPE]); // メンション反応を格納
			rsort($this->sinceId);
			$this->dataMethods->updateSinceId($this->sinceId[0]);
			$this->postComments($this->statuses_info); // つぶやきを投稿
			$this->postRtFav($this->statuses_info); // RTとお気に入り登録
			$this->autoFollow();
		}catch(Exception $e){
			throw new Exception($e->getMessage());
		}
	}

	// 実行時刻をセット
	private function setNow($now){
		$this->now['min'] = $now['minutes'];
		$this->now['hour'] = $now['hours'];
		$this->now['day'] = $now['mday'];
		$this->now['month'] = $now['mon'];
		$this->now['week'] = $now['wday'];
	}
	
	// OAuth認証を行えるかチェック
	private function checkOAuth(){
		$result = $this->twitterMethods->getAccountVerify_credentials();
		if(isset($result->error)){
			$this->reportError(__METHOD__, $result->error);
			throw new Exception('APIエラー: ' . $result->error);
		}
		$this->myId = $result->id_str;
	}
	
	// cron情報の中から、現在時刻とマッチしているgroupIdを返す
	private function checkTime($now, array $over = NULL){
		if(!count($now)){
			return $over;
		}
		$keys = array_keys($now);
		$type = $keys[0];
		$data = (string) array_shift($now);
		$matched = array();
		$lines = $this->dataMethods->getMethods()->read($type);
		if(Utils::emptyArrays($lines)){
			return array();
		}
		foreach($lines as $value){
			if(($value[DataMethods::CRON_COL] === $data || $value[DataMethods::CRON_COL] === '-1') && (is_null($over) || in_array($value[DataMethods::GROUP_ID_COL], $over))){
				$matched[] = $value[DataMethods::GROUP_ID_COL];
			}
		}
		return $this->checkTime($now, $matched);
	}
	
	// groupDataの情報をチェック
	private function checkGroupData(array $ids, $now){
		$lines = $this->dataMethods->getMethods()->read('groupData');
		$now = $now['year'];
		// 「tweet」「home」「mention」の3タイプに分けて取得
		$actions = array(DataMethods::AUTO_TWEET_TYPE => array(), DataMethods::REACT_HOME_TYPE => array(), DataMethods::REACT_MENTION_TYPE => array());
		foreach($lines as $value){
			// groupIdが引数の配列に含まれていて、かつ稼働中であればタイプ毎に取得
			if(in_array($value[DataMethods::GROUP_ID_COL], $ids) && $value[DataMethods::GROUP_RUN_COL] === '1'){
				if($value[DataMethods::GROUP_LIMIT_COL] > date('YmdHi', $now[0])){
					$this->dataMethods->deleteData($value[DataMethods::GROUP_ID_COL]);
					continue;
				}
				$actions[$value[DataMethods::GROUP_TYPE_COL]][] = $value[DataMethods::GROUP_ID_COL];
			}
		}
		return $actions;
	}
	
	// 自動ツイートの情報をセットする
	private function setTweetsInfo($tweets){
		foreach($tweets as $value){
			$this->setStatuses_info($value);
		}
	}
	
	// TL反応の情報をセット
	private function setHomeReactions($ids){
		$TL = $this->twitterMethods->getHome_timeline('100', $this->sinceId[0]);
		$patterns = $this->getPatterns($ids);
		$this->setReactions($patterns, $TL);
	}
		
	// メンション反応の情報をセット
	private function setMentionReactions($ids){
		$TL = $this->twitterMethods->getMentions('100', $this->sinceId[0]);
		$patterns = $this->getPatterns($ids);
		$this->setReactions($patterns, $TL);
	}

	// つぶやきの情報をセットする
	private function setStatuses_info($groupId, $id = NULL, $in_reply_to = NULL, $user_id = NULL, $user_screen_name = NULL, $user_name = NULL){
		$this->statuses_info[] = array(
			'groupId' => $groupId,
			'id_str' => $id,
			'in_reply_to_status_id_str' => $in_reply_to,
			'user_id_str' => $user_id,
			'user_screen_name' => $user_screen_name,
			'user_name' => $user_name
		);
	}
	
	// タイムラインをループしてパターンマッチ
	private function setReactions($patterns, $TL){
		if($TL === FALSE){
			return;
		}
		foreach($TL as $value){
			if(isset($value->retweeted_status)){
				$value = $value->retweeted_status;
			}
			if(($ids = $this->preg_match_patterns($patterns, $value->text)) === FALSE){
				continue;
			}
			foreach($ids as $id){
				$this->setStatuses_Info(
					$id,
					$value->id_str,
					$value->in_reply_to_status_id_str,
					$value->user->id_str,
					$value->user->screen_name,
					$value->user->name
				);
			}
		}
		$this->sinceId[] = $value->id_str;
	}
	
	// マッチさせるパターンを取得
	private function getPatterns($ids){
		$patterns = array();
		$lines = $this->dataMethods->getMethods()->read('reactions');
		foreach($lines as $value){
			if(in_array($value[DataMethods::GROUP_ID_COL], $ids)){
				$patterns[$value[DataMethods::GROUP_ID_COL]] = $value[DataMethods::PATTERN_COL];
			}
		}
		return $patterns;
	}
	
	// 複数のパターンとパターンマッチ
	private function preg_match_patterns(array $patterns, $subject){
		$return = array();
		foreach($patterns as $key => $value){
			if(preg_match('/' . $value . '/', $subject)){
				$return[] = $key;
			}
		}
		if(count($return)){
			return $return;
		}else{
			return FALSE;
		}
	}
	
	// 登録したコメントの中から、条件に合致したものを投稿
	private function postComments(array $tweets){
		$data = $this->dataMethods->getMethods()->read('comments');
		$comments = array();
		foreach($data as $value){
			$comments[$value[DataMethods::GROUP_ID_COL]][] = $value[DataMethods::COMMENTS_COL];
		}
		foreach($tweets as $value){
			if(array_key_exists($value['groupId'], $comments)){
				$this->postTweet($comments[$value['groupId']], $value);
			}
		}
	}
	
	// $this->tweet_infoを解析して投稿
	private function postTweet($comments, $info){
		shuffle($comments);
		while(count($comments)){
			$comment = array_shift($comments);
			$in_reply_to_status_id = NULL;
			if(isset($info['id_str'])){
				$in_reply_to_status_id = preg_match('/[SCREEN_NAME]/', $comment) ? $info['id_str'] : NULL;
				$search = array('[SCREEN_NAME]', '[NAME]');
				$replace = array('@' . $info['user_screen_name'], $info['user_name']);
				$comment = str_replace($search, $replace, $comment);
			}
			if($this->baseConfigs[DataMethods::CUT_COL] === '1'){
				$parts = $this->splitComment($comment);
				$comment = $this->formatStatus($parts);
			}
			$result = $this->twitterMethods->postStatusesUpdate($comment, $in_reply_to_status_id);
			if(!isset($result->error) || $result->error != 'Status is a duplicate.'){
				break;
			}
		}
	}
	
	// 投稿文を分割
	private function splitComment($comment){
		$return = array();
		while(strlen($comment)){
			if(!preg_match('/' . self::URL_PATTERN . '|' . self::SCREEN_NAME_PATTERN . '|' . self::HASHTAG_PATTERN . '/u', $comment, $match, PREG_OFFSET_CAPTURE)){
				$return[] = $comment;
				break;
			}
			if(strlen($firstPart = substr($comment, 0, $match[0][1]))){
				$return[] = $firstPart;
			}
			$return[] = substr($comment, $match[0][1], strlen($match[0][0]));
			$comment = substr($comment, $match[0][1] + strlen($match[0][0]));
		}
		return $return;
	}
	
	// 140字に収まる投稿文を作成
	private function formatStatus(array $parts){
		$return = '';
		$strCount = 0;
		foreach($parts as $value){
			if(preg_match('/' . self::URL_PATTERN . '/', $value)){
				$strCount += 20;
			}else{
				$strCount += mb_strlen($value);
			}
			if(preg_match('/' . self::URL_PATTERN . '|' . self::SCREEN_NAME_PATTERN . '|' . self::HASHTAG_PATTERN . '/u', $value)){
				if($strCount > 140){
					break;
				}
			}
			if($strCount >= 140){
				$return .= mb_substr($value, 0, mb_strlen($value) + 140 - $strCount);
				break;
			}else{
				$return .= $value;
			}
		}
		return $return;
	}
	
	// 公式RTとお気に入り登録
	private function postRtFav($info){
		$lines = $this->dataMethods->getMethods()->read('reactions');
		if(Utils::emptyArrays($lines)){
			return;
		}
		$reactions = array();
		foreach($lines as $value){
			$reactions[$value[DataMethods::GROUP_ID_COL]] = array($value[DataMethods::RETWEET_COL], $value[DataMethods::FAVORITE_COL]);
		}
		foreach($info as $value){
			if(!array_key_exists($value['groupId'], $reactions)){
				continue;
			}
			if($reactions[$value['groupId']][0] === '1'){
				$result = $this->twitterMethods->postStatusesRetweet($value['id_str']);
			}
			if($reactions[$value['groupId']][1] === '1'){
				$result = $this->twitterMethods->postFavoritesCreate($value['id_str']);
			}
		}
	}
	
	// 自動フォロー返し
	private function autoFollow(){
		if($this->baseConfigs[DataMethods::AUTO_FOLLOW_COL] !== '1'){
			return;
		}
		$exceptions = $this->dataMethods->fetchOneSided();
		$followers = $this->twitterMethods->getAllFollowersIds('user_id', $this->myId);
		$friends = $this->twitterMethods->getAllFriendsIds('user_id', $this->myId);
		$ids = array_diff($followers, $friends, $exceptions);
		foreach($ids as $value){
			$this->twitterMethods->postFriendshipsCreate($value);
		}
	}
	
	// エラー報告
	private function reportError($methodname, $result){
		if(isset($result->error)){
			Utils::reportError(time(), $methodname, $result->error);
		}
	}
}