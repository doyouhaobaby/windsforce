<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   添加活动控制器($$)*/

!defined('Q_PATH') && exit;

class Add_C_Controller extends InitController{

	public function index(){
		if($GLOBALS['_cache_']['event_option']['front_add']==0 && !Core_Extend::isAdmin()){
			$this->E(Q::L('系统关闭了创建活动功能','Controller'));
		}
		
		// 活动类型
		Core_Extend::loadCache('event_category');
		$this->assign('arrEventcategorys',$GLOBALS['_cache_']['event_category']);

		Core_Extend::getSeo($this,array('title'=>Q::L('发起活动','Controller')));

		$this->assign('nDisplaySeccode',$GLOBALS['_option_']['seccode_publish_status']);
		$this->display('event+add');
	}

}
