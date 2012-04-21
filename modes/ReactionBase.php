<?php
/**
 * Copyright (c) 2012 okumin, http://okumin.com/
 */
 
// homeTL反応、mention反応の基礎クラス
class ReactionBase extends ModeBase
{
	// 自動ツイートの設定を更新
	public function edit($mode){
		$groupId = $this->parameters->getGetParameter('groupId');
		if($this->parameters->getPostParameter('remove') === '1'){
			Utils::jumpToNewURL(array(), array('mode' => $mode, 'action' => 'remove', 'groupId' => $groupId));
		}
		$this->template = 'editReaction';
		if($mode === 'reactHome'){
			$title = 'timeline反応';
		}else{
			$title = 'mention反応';
		}
		$this->menu[$title . '一覧'] = URL . '?mode=' . $mode . '&action=select';
		$this->menu[$title] = URL . '?mode=' . $mode . '&action=edit';
		if(isset($groupId)){
			$this->replace['disabled'] = '';
		}else{
			$this->replace['disabled'] = ' disabled';
		}
		try{
			if(isset($groupId) && $this->dataMethods->fetchGroupName($groupId) === NULL){
				Utils::jumpToNewURL(array(), array('mode' => $mode, 'action' => 'select'));
			}
			$this->checkPost();
			$this->dataMethods->setGroupId($groupId);
			$groupType = ($mode == 'reactHome') ? DataMethods::REACT_HOME_TYPE : DataMethods::REACT_MENTION_TYPE;
			$this->dataMethods->updateGroupData($groupType);
			$this->dataMethods->updateCron();
			$this->dataMethods->updateReactions();
			$this->dataMethods->updateComments();
		    Utils::jumpToNewURL($this->parameters->getGetParameter(), array('groupId' => $this->dataMethods->getGroupId(), 'edited' => '1'));
		}catch(PostNullException $e_post){
			$this->replace['SAFE_MESSAGE'] = !is_null($this->parameters->getGetParameter('edited')) ? self::EDITED : '';
			$this->replace['groupName'] = isset($groupId) ? $this->dataMethods->fetchGroupName($groupId) : '';
			$this->replace['CHECK_groupRun'] = isset($groupId) ? $this->dataMethods->fetchGroupRun($groupId) : '1';
			$this->replace['groupLimit'] = isset($groupId) ? $this->dataMethods->fetchGroupLimit($groupId) : '';
			$this->replace['pattern'] = isset($groupId) ? $this->dataMethods->fetchPattern($groupId) : '';
			$this->replace['CHECK_retweet'] = isset($groupId) ? $this->dataMethods->fetchRetweet($groupId) : '';
			$this->replace['CHECK_favorite'] = isset($groupId) ? $this->dataMethods->fetchFavorite($groupId) : '';
			$this->replace['comments'] = isset($groupId) ? implode("\n", $this->dataMethods->fetchComments($groupId)) : '';
		}catch(Exception $e){
			$this->replace['SAFE_MESSAGE'] = self::ERROR1 . $e->getMessage() . self::ERROR2;
			$this->replace['groupName'] = $this->parameters->getPostParameter('groupName');
			$this->replace['CHECK_groupRun'] = $this->parameters->getPostParameter('groupRun');
			$this->replace['groupLimit'] = $this->parameters->getPostParameter('groupLimit');
			$this->replace['pattern'] = $this->parameters->getPostParameter('pattern');
			$this->replace['CHECK_retweet'] = $this->parameters->getPostParameter('retweet');
			$this->replace['CHECK_favorite'] = $this->parameters->getPostParameter('favorite');
			$this->replace['comments'] = $this->parameters->getPostParameter('comments');
		}
	}
	
	// 編集データ選択画面
	public function select($mode){
		$this->template = 'selectGroup';
		if($mode === 'reactHome'){
			$title = 'timeline反応';
		}else{
			$title = 'mention反応';
		}
		$this->menu[$title . '一覧'] = URL . '?mode=' . $mode . '&action=select';
		$this->replace['MODEL'] = $mode;
		$groupType = ($mode == 'reactHome') ? DataMethods::REACT_HOME_TYPE : DataMethods::REACT_MENTION_TYPE;
		$this->replace['LIST_links'] = $this->dataMethods->getMethods()->read('groupData', array(), array(DataMethods::GROUP_TYPE_COL => $groupType));
		$this->replace['SAFE_MESSAGE'] = '';
	}
	
	// 削除
	public function remove($mode){
		$groupId = $this->parameters->getGetParameter('groupId');
		$this->template = 'remove';
		if($mode === 'reactHome'){
			$title = 'timeline反応';
		}else{
			$title = 'mention反応';
		}
		$this->menu[$title . '一覧'] = URL . '?mode=' . $mode . '&action=select';
		$this->menu[$title . '削除'] = URL . '?mode=' . $mode . '&action=remove';
		try{
			if(($groupName = $this->dataMethods->fetchGroupName($groupId)) === NULL){
				throw new Exception('グループIDが不正です。');
			}
			$this->replace['groupName'] = ($groupName !== '') ? "「{$groupName}」" : '「無題」';
			if(is_null($this->parameters->getPostParameter('check'))){
				throw new PostNullException();
			}
			$this->dataMethods->deleteData($groupId);
			Utils::jumpToNewURL(array(), array('mode' => $mode, 'action' => 'select'));
		}catch(PostNullException $e_post){
			$this->replace['SAFE_MESSAGE'] = '';
		}catch(Exception $e){
			$this->replace['SAFE_MESSAGE'] = self::ERROR1 . $e->getMessage() . self::ERROR2;
			$this->replace['groupName'] = '「?」';
		}
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
		CheckUtils::checkPattern($this->parameters->getPostParameter('pattern'));
		CheckUtils::checkBool($this->parameters->getPostParameter('retweet'), 'RT');
		CheckUtils::checkBool($this->parameters->getPostParameter('favorite'), 'お気に入り登録');
	}
}