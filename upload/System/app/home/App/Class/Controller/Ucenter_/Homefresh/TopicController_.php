<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   热门话题($$)*/

!defined('Q_PATH') && exit;

class Topic_C_Controller extends InitController{

	public function index(){
		// 读取热门话题
		Core_Extend::loadCache('hottag');

		$this->assign('arrHothomefreshtags',$GLOBALS['_cache_']['hottag']);
		$this->display('homefresh+topic');
	}

}
