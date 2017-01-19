<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   应用列表($$)*/

!defined('Q_PATH') && exit;

class AppsController extends InitController{
	
	public function index(){
		// 获取应用缓存
		Core_Extend::loadCache('apps');

		Core_Extend::getSeo($this,array('title'=>Q::L('应用列表','Controller')));

		$this->assign('nApps',count($GLOBALS['_cache_']['apps']));
		$this->assign('arrAppinfos',$GLOBALS['_cache_']['apps']);
		$this->display('apps+index');
	}

}
