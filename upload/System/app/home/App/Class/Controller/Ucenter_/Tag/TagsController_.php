<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   新鲜事话题排行列表($$)*/

!defined('Q_PATH') && exit;

class Tags_C_Controller extends InitController{

	public function index(){
		// 获取排行数据
		Core_Extend::loadCache('hottagtop');
		Core_Extend::getSeo($this,array('title'=>Q::L('新鲜事话题排行榜','Controller')));

		$this->assign('arrHottags',$GLOBALS['_cache_']['hottagtop']);
		$this->display('homefreshtag+tags');
	}

}
