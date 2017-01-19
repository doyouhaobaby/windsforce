<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   媒体控制器($$)*/

!defined('Q_PATH') && exit;

class Media_C_Controller extends InitController{

	public function index(){
		$sFunction=trim(Q::G('function','G'));
		$this->assign('sFunction',$sFunction);
		$this->display('misc+music');
	}

	public function video(){
		$sFunction=trim(Q::G('function','G'));
		$this->assign('sFunction',$sFunction);
		$this->display('misc+video');
	}

}
