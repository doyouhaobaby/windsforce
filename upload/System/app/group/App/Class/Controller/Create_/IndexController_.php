<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   创建小组界面控制器($$)*/

!defined('Q_PATH') && exit;

class Index_C_Controller extends InitController{

	public function index(){
		// 小组分类
		Core_Extend::loadCache('group_category');

		Core_Extend::getSeo($this,array('title'=>Q::L('申请创建小组','Controller')));

		$this->assign('arrGroupCategorys',$GLOBALS['_cache_']['group_category']);
		$this->display('create+index');
	}

}