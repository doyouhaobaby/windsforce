<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   设置中心($$)*/

!defined('Q_PATH') && exit;

class Option_C_Controller extends InitController{

	public function index(){
		Core_Extend::getSeo($this,array('title'=>Q::L('设置中心','Controller')));
		$this->display('public+option');
	}

}
