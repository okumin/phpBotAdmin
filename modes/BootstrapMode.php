<?php
/**
 * Copyright (c) 2012 okumin, http://okumin.com/
 */
 
// Twitter Bootstrapのファイルを出力
class BootstrapMode extends ModeBase
{
	private $bootstrapDir;
	
	// cssファイルの出力
	public function cssAction(){
		header('Content-type: text/css');
		$this->setBootstrapDir('css');
		$filename = $this->parameters->getGetParameter('filename');
		if(file_exists($this->bootstrapDir . "/{$filename}.css")){
			$css = file_get_contents($this->bootstrapDir . "/{$filename}.css");
			$css = preg_replace('/"\.\.\/img\/(.+).png"/', '"' . URL . '?mode=bootstrap&action=img&filename=$1"', $css);
			echo $css;
			exit;
		}else{
			echo "";
			exit;
		}
	}
	
	// 画像の出力
	public function imgAction(){
		header('Content-type: image/png');
		$this->setBootstrapDir('img');
		$filename = $this->parameters->getGetParameter('filename');
		if(file_exists($this->bootstrapDir . "/{$filename}.png")){
			require_once $this->bootstrapDir . "/{$filename}.png";
			exit;
		}else{
			echo "";
			exit;
		}
	}
	
		// JavaScriptの出力
	public function jsAction(){
		header('Content-type: text/javascript');
		$this->setBootstrapDir('js');
		$filename = $this->parameters->getGetParameter('filename');
		if(file_exists($this->bootstrapDir . "/{$filename}.js")){
			require_once $this->bootstrapDir . "/{$filename}.js";
			exit;
		}else{
			echo "";
			exit;
		}
	}
	
	private function setBootstrapDir($type){
		$this->bootstrapDir = BASE_DIR . "/bootstrap/$type";
	}
}