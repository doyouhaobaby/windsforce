<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   弹出播放($$)*/

!defined('Q_PATH') && exit;

class Playout_C_Controller extends InitController{

	public function index(){
		$sFlashpath=trim(Q::G('url','G'));
		if(empty($sFlashpath)){
			Q::E(Q::L('没有指定播放的flash','Controller'));
		}
		
		$this->assign('sFlashpath',$sFlashpath);
		$this->display('attachment+playout');
	}
}