<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   全屏播放($$)*/

!defined('Q_PATH') && exit;

class Fullplayframe_C_Controller extends InitController{

	public function index(){
		$sFlashpath=trim(Q::G('url','G'));
		if(empty($sFlashpath)){
			$this->E(Q::L('没有指定播放的flash','Controller'));
		}
	
		$this->assign('sFlashpath',$sFlashpath);
		$this->display('attachment+fullplayframe');
	}

}
