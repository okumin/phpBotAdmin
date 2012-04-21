<?php
/**
 * Copyright (c) 2012 okumin, http://okumin.com/
 */
 
// 自動ツイート(リプライ含まず)を編集するクラス
class AutoTweetMode extends ModeBase
{
	// 自動ツイートの設定を更新
	public function editAction(){
		$groupId = $this->parameters->getGetParameter('groupId');
		if($this->parameters->getPostParameter('remove') === '1'){
			Utils::jumpToNewURL(array(), array('mode' => 'autoTweet', 'action' => 'remove', 'groupId' => $groupId));
		}
		$this->template = 'editAutoTweet';
		$this->menu['定時ツイート一覧'] = URL . '?mode=autoTweet&action=select';
		$this->menu['定時ツイート'] = URL . '?mode=autoTweet&action=edit';
		if(isset($groupId)){
			$this->replace['disabled'] = '';
		}else{
			$this->replace['disabled'] = ' disabled';
		}
		try{
			if(isset($groupId) && is_null($this->dataMethods->fetchGroupName($groupId))){
				Utils::jumpToNewURL(array(), array('mode' => 'autoTweet', 'action' => 'select'));
			}
			$this->checkPost();
			$this->dataMethods->setGroupId($groupId);
			$this->dataMethods->updateGroupData(DataMethods::AUTO_TWEET_TYPE);
			$this->dataMethods->updateCron();
			$this->dataMethods->updateComments();
			Utils::jumpToNewURL($this->parameters->getGetParameter(), array('groupId' => $this->dataMethods->getGroupId(), 'edited' => '1'));
		}catch(PostNullException $e){
			$this->replace['SAFE_MESSAGE'] = !is_null($this->parameters->getGetParameter('edited')) ? self::EDITED : '';
			$this->replace['groupName'] = isset($groupId) ? $this->dataMethods->fetchGroupName($groupId) : '';
			$this->replace['CHECK_groupRun'] = isset($groupId) ? $this->dataMethods->fetchGroupRun($groupId) : '1';
			$this->replace['groupLimit'] = isset($groupId) ? $this->dataMethods->fetchGroupLimit($groupId) : '';
			$this->replace['CRON_min'] = isset($groupId) ? $this->dataMethods->fetchMin($groupId) : array_fill(0, 60, '');
			$this->replace['CRON_hour'] = isset($groupId) ? $this->dataMethods->fetchHour($groupId) : array_fill(0, 24, '');
			$this->replace['CRON_day'] = isset($groupId) ? $this->dataMethods->fetchDay($groupId) : array_fill(1, 31, '');
			$this->replace['CRON_month'] = isset($groupId) ? $this->dataMethods->fetchMonth($groupId) : array_fill(1, 12, '');
			$this->replace['CRON_week'] = isset($groupId) ? $this->dataMethods->fetchWeek($groupId) : array_fill(0, 7, '');
			$this->replace['comments'] = isset($groupId) ? implode("\n", $this->dataMethods->fetchComments($groupId)) : '';
		}catch(Exception $e){
			$this->replace['SAFE_MESSAGE'] = self::ERROR1 . $e->getMessage() . self::ERROR2;
			$this->replace['groupName'] = $this->parameters->getPostParameter('groupName');
			$this->replace['CHECK_groupRun'] = $this->parameters->getPostParameter('groupRun');
			$this->replace['groupLimit'] = $this->parameters->getPostParameter('groupLimit');
			$this->replace['CRON_min'] = $this->parameters->getPostParameter('min');
			$this->replace['CRON_hour'] = $this->parameters->getPostParameter('hour');
			$this->replace['CRON_day'] = $this->parameters->getPostParameter('day');
			$this->replace['CRON_month'] = $this->parameters->getPostParameter('month');
			$this->replace['CRON_week'] = $this->parameters->getPostParameter('week');
			$this->replace['comments'] = $this->parameters->getPostParameter('comments');
		}
	}
	
	// 編集データ選択画面
	public function selectAction(){
		$this->template = 'selectGroup';
		$this->menu['定時ツイート一覧'] = URL . '?mode=autoTweet&action=select';
		$this->replace['head'] = '定時ツイート一覧';
		$this->replace['MODEL'] = 'autoTweet';
		$this->replace['LIST_links'] = $this->dataMethods->getMethods()->read('groupData', array(), array(DataMethods::GROUP_TYPE_COL => DataMethods::AUTO_TWEET_TYPE));
		$this->replace['SAFE_MESSAGE'] = '';
	}
	
	// 削除
	public function removeAction(){
		$groupId = $this->parameters->getGetParameter('groupId');
		$this->template = 'remove';
		$this->menu['定時ツイート一覧'] = URL . '?mode=autoTweet&action=select';
		$this->menu['定時ツイート削除'] = URL . '?mode=autoTweet&action=remove';
		try{
			if(($groupName = $this->dataMethods->fetchGroupName($groupId)) === NULL){
				throw new Exception('グループIDが不正です。');
			}
			$this->replace['groupName'] = ($groupName !== '') ? "「{$groupName}」" : '「無題」';
			if(is_null($this->parameters->getPostParameter('check'))){
				throw new PostNullException();
			}
			$this->dataMethods->deleteData($groupId);
			Utils::jumpToNewURL(array(), array('mode' => 'autoTweet', 'action' => 'select'));
		}catch(PostNullException $e_post){
			$this->replace['SAFE_MESSAGE'] = '';
		}catch(Exception $e){
			$this->replace['SAFE_MESSAGE'] = self::ERROR1 . $e->getMessage() . self::ERROR2;
			$this->replace['groupName'] = '「?」';
		}
	}
	
	// 該当するModeが存在しない場合
	public function indexAction(){
		$this->selectAction();
	}
	
	// 入力チェック
	public function checkPost(){
		if(!count($this->parameters->getPostParameter())){
			throw new PostNullException();
		}
		CheckUtils::checkLn($this->parameters->getPostParameter('groupName'), 'グループ名');
		CheckUtils::checkBool($this->parameters->getPostParameter('groupRun'), '動作状況');
		if(!preg_match('/[0-9]{12}/', $this->parameters->getPostParameter('groupLimit')) && $this->parameters->getPostParameter('groupLimit') !== ''){
			throw new CheckException('有効期限は半角数字12桁、もしくは空欄にしてください。');
		}
		CheckUtils::checkMin($this->parameters->getPostParameter('min'));
		CheckUtils::checkHour($this->parameters->getPostParameter('hour'));
		CheckUtils::checkDay($this->parameters->getPostParameter('day'));
		CheckUtils::checkMonth($this->parameters->getPostParameter('month'));
		CheckUtils::checkWeek($this->parameters->getPostParameter('week'));
		CheckUtils::checkEmpty($this->parameters->getPostParameter('comments'), 'コメント');
	}
}