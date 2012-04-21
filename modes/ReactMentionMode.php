<?php
/**
 * Copyright (c) 2012 okumin, http://okumin.com/
 */
 
// メンションへの反応を設定
class ReactMentionMode extends ReactionBase
{
	// データ更新
	public function editAction(){
		$this->replace['head'] = 'mention反応';
		$this->edit('reactMention');
	}
	
	// 編集データ選択画面
	public function selectAction(){
		$this->replace['head'] = 'mentnon反応一覧';
		$this->select('reactMention');
	}
	
	// 削除
	public function removeAction(){
		$this->remove('reactMention');
	}
	
	// 該当するModeが存在しない場合
	public function indexAction(){
		$this->selectAction();
	}
}