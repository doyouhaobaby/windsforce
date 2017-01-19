<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   会员排行($$)*/

!defined('Q_PATH') && exit;

class Usertop_C_Controller extends InitController{

	public function index(){
		// 读取统计缓存
		Core_Extend::loadCache('usertop');
		Core_Extend::getSeo($this,array('title'=>Q::L('会员排行','Controller')));

		// 缓存数据
		$this->assign('arrActiveusers',$GLOBALS['_cache_']['usertop']['active']);
		$this->assign('arrNewusers',$GLOBALS['_cache_']['usertop']['new']);
		$this->assign('arrCreditusers',$GLOBALS['_cache_']['usertop']['credit']);
		$this->assign('arrFanusers',$GLOBALS['_cache_']['usertop']['fan']);
		$this->assign('arrOltimeusers',$GLOBALS['_cache_']['usertop']['oltime']);
		$this->display('stat+usertop');
	}
	
}
