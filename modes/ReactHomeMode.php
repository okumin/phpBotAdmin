<?php
/**
 * Copyright (c) 2012 okumin, http://okumin.com/
 */
 
// home_timelineへの反応を設定
class ReactHomeMode extends ReactionBase
{
	// データ更新
	public function editAction(){
		$this->replace['head'] = 'timeline反応';
		$this->edit('reactHome');
	}
	
	// 編集データ選択画面
	public function selectAction(){
		$this->replace['head'] = 'timeline反応一覧';
		$this->select('reactHome');
	}
	
	// 削除
	public function removeAction(){
		$this->remove('reactHome');
	}
	
	// 該当するModeが存在しない場合
	public function indexAction(){
		$this->selectAction();
	}
}