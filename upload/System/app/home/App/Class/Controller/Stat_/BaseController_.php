<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   站点基本信息($$)*/

!defined('Q_PATH') && exit;

class Base_C_Controller extends InitController{

	public function index(){
		Core_Extend::loadCache('site');
		Core_Extend::getSeo($this,array('title'=>Q::L('基本概况','Controller')));

		$this->assign('arrSite',$GLOBALS['_cache_']['site']);
		$this->display('stat+base');
	}
	
}
