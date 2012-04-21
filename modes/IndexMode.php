<?php
/**
 * Copyright (c) 2012 okumin, http://okumin.com/
 */
 
// トップページ
class IndexMode extends ModeBase{
		
		public function indexAction(){
			$this->template = 'index';
			$this->replace = $this->makeCounts();
		}

		// 自動ツイート設定件数取得
	private function makeCounts(){
		$mode = $this->parameters->getGetParameter('mode');
		$tweets = $this->dataMethods->getMethods()->read('groupData', array(), array(DataMethods::GROUP_TYPE_COL => DataMethods::AUTO_TWEET_TYPE));
		$homes = $this->dataMethods->getMethods()->read('groupData', array(), array(DataMethods::GROUP_TYPE_COL => DataMethods::REACT_HOME_TYPE));
		$mentions = $this->dataMethods->getMethods()->read('groupData', array(), array(DataMethods::GROUP_TYPE_COL => DataMethods::REACT_MENTION_TYPE));
		$replace['tweetCount'] = Utils::emptyArrays($tweets) ? '(0)' : '(' . count($tweets) . ')';
		$replace['homeCount'] = Utils::emptyArrays($homes) ? '(0)' : '(' . count($homes) . ')';
		$replace['mentionCount'] = Utils::emptyArrays($mentions) ? '(0)' : '(' . count($mentions) . ')';
		return $replace;
	}
}