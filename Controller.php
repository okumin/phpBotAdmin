<?php
/**
 * Copyright (c) 2012 okumin, http://okumin.com/
 */
 
// modeとtemplateの橋渡しを行う
class Controller
{
	private $methods; // データ操作を行うインスタンス
	
	// コンストラクタ
	// 処理から出力までを行う
	public function __construct(){
		$this->methods = MethodsFactory::create(Config::USE_DATABASE);
		$passcheck = new CheckLogin();
		$this->execMode();
		$this->printHtml();
	}
	
	// 指定された処理を実行
	private function execMode(){
		$mode = Parameters::getInstance()->getGetParameter('mode');
		$action = Parameters::getInstance()->getGetParameter('action');
		$this->modeInstance = $this->getModeInstance($mode);
		if(method_exists($this->modeInstance, $action . 'Action')){
			$methodName = $action . 'Action';
			$this->modeInstance->$methodName();
		}else{
			$this->modeInstance->indexAction();
		}
	}
	
	// $modeで指定したモデルのインスタンスを返す
	private function getModeInstance($mode){
		$checkedMode = ucfirst($mode) . 'Mode';
		$dataMethods = new DataMethods(MethodsFactory::create(Config::USE_DATABASE));
		$dataMethods->setParameters(Parameters::getInstance());
		if(file_exists(MODE_DIR . "/{$checkedMode}.php")){
			require MODE_DIR . "/{$checkedMode}.php";
			return new $checkedMode($dataMethods, Parameters::getInstance());
		}else{
			require MODE_DIR . '/IndexMode.php';
			return new IndexMode($dataMethods, Parameters::getInstance());
		}
	}
	
	// 出力
	private function printHtml(){
		$template = $this->modeInstance->getTemplate();
		$replaceContent = $this->modeInstance->getReplace();
		$replace['CONTENT'] = $this->replace($template, $replaceContent);
		$replace['BREADCRUMB'] = $this->makeBreadcrumb($this->modeInstance->getMenu());
		$replace = array_merge($replace, $this->makeCounts());
		$html = $this->replace('base', $replace, FALSE);
		echo $html;
	}
	
	// テンプレートを整形して返す
	private function replace($template, $replaced, $escape = TRUE){
		Utils::check_right(TPL_DIR . "/{$template}.txt", Utils::READ);
		$replaced['TITLE'] = TITLE;
		$replaced['URL'] = URL;
		$view = file_get_contents(TPL_DIR . "/{$template}.txt");
		foreach($replaced as $key => $value){
			if(preg_match('/^SAFE_.+/', $key)){
				$key = preg_replace('/^SAFE_/', '', $key);
			}elseif($escape){
				$value = Utils::h($value);
			}
			if(preg_match('/^CHECK_.+/', $key)){
				$key = preg_replace('/^CHECK_/', '', $key);
				$value = $this->makeCheckBox($key, $value);
			}
			if(preg_match('/^CRON_.+/', $key)){
				$key = preg_replace('/^CRON_/', '', $key);
				$value = $this->makeCronBox($key, $value);
			}
			if(preg_match('/^LIST_.+/', $key)){
				$key = preg_replace('/^LIST_/', '', $key);
				$value = $this->makeLinkList($value);
			}
			$view = str_replace("[{$key}]", $value, $view);
		}
		return $view;
	}
	
	// 自動ツイート設定件数取得
	private function makeCounts(){
		$mode = Parameters::getInstance()->getGetParameter('mode');
		if($mode == 'login' || $mode == 'init'){
			return array('TWEET_COUNT' => '', 'HOME_COUNT' => '', 'MENTION_COUNT' => '');
		}
		$tweets = $this->methods->read('groupData', array(), array(DataMethods::GROUP_TYPE_COL => DataMethods::AUTO_TWEET_TYPE));
		$homes = $this->methods->read('groupData', array(), array(DataMethods::GROUP_TYPE_COL => DataMethods::REACT_HOME_TYPE));
		$mentions = $this->methods->read('groupData', array(), array(DataMethods::GROUP_TYPE_COL => DataMethods::REACT_MENTION_TYPE));
		$replace['TWEET_COUNT'] = Utils::emptyArrays($tweets) ? '(0)' : '(' . count($tweets) . ')';
		$replace['HOME_COUNT'] = Utils::emptyArrays($homes) ? '(0)' : '(' . count($homes) . ')';
		$replace['MENTION_COUNT'] = Utils::emptyArrays($mentions) ? '(0)' : '(' . count($mentions) . ')';
		return $replace;
	}
	
	// チェックボックス作成
	private function makeCheckBox($name, $data){
		if(!is_array($data)){
			if($data == 1){
				$return = '<div class="checkbox on" onClick="changeChecked(this);"><input type="checkbox" name="' . $name . '" value="1" checked> ON</div>';
			}else{
				$return = '<div class="checkbox off" onClick="changeChecked(this);"><input type="checkbox" name="' . $name . '" value="1"> OFF</div>';
			}
			return $return;
		}
	}
	
	// cronの選択フォーム作成
	private function makeCronBox($name, $data){
		$return = '';
		foreach($data as $key => $value){
			$num = ($key < 10) ? "0$key" : "$key";
			if($value == 1){
				$return .= '<button type="button" class="btn btn-mini btn-primary" onClick="changeActive(this);"><input type="hidden" name="' . $name . '[' . $key . ']" value="1">' . $num . '</button>';
			}else{
				$return .= '<button type="button" class="btn btn-mini" onClick="changeActive(this);"><input type="hidden" name="' . $name . '[' . $key . ']" value="">' . $num . '</button>';
			}
		}
		return $return;
	}
	
	// リンク一覧を作成
	private function makeLinkList($data){
		if(Utils::emptyArrays($data)){
			return '';
		}
		krsort($data);
		$return = '<table class="table table-striped table-bordered">' . "\n";
		$return .= '<thead>' . "\n";
		$return .= '<tr><th>グループ名</th><th>動作状況</th><th>有効期限</th></tr>' . "\n";
		$return .= '</thead>' . "\n";
		$return .= '<tbody>' . "\n";
		foreach($data as $value){
			$title = ($value[DataMethods::GROUP_NAME_COL] !== '') ? Utils::h($value[DataMethods::GROUP_NAME_COL]) : '無題';
			$run = ($value[DataMethods::GROUP_RUN_COL] === '1') ? '稼働中' : '停止中';
			$limit = ($value[DataMethods::GROUP_LIMIT_COL] !== '') ? Utils::h(substr($value[DataMethods::GROUP_LIMIT_COL], 0 , 4)) . '/' . Utils::h(substr($value[DataMethods::GROUP_LIMIT_COL], 4 , 2)) . '/' . Utils::h(substr($value[DataMethods::GROUP_LIMIT_COL], 6 , 2)) . ' ' . Utils::h(substr($value[DataMethods::GROUP_LIMIT_COL], 8 , 2)) . ':' . Utils::h(substr($value[DataMethods::GROUP_LIMIT_COL], 10 , 2)) : '';
			$return .= '<tr><td><a href="' . URL . '?mode=' . Parameters::getInstance()->getGetParameter('mode') . '&amp;action=edit&amp;groupId=' . $value[DataMethods::GROUP_ID_COL] . '">' . $title . '</a></td><td>' . $run . '</td><td>' . $limit . '</td></tr>' . "\n";
		}
		$return .= '</tbody>' . "\n";
		$return .= '</table>';
		return $return;
	}
	
	// パンくずリストを作成
	private function makeBreadcrumb(array $list){
		$return = '';
		foreach($list as $key => $value){
			if(end(array_keys($list)) === $key){
				$return .= '<li class="active">' . $key . '</li>' . "\n";
				break;
			}
			$return .= '<li><a href="' . $value . '">' . $key . '</a> <span class="divider"><i class="icon-chevron-right"></i></span></li>' . "\n";
		}
		return $return;
	}	
}