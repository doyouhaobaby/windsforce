<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   标签排行($$)*/

!defined('Q_PATH') && exit;

class Top_C_Controller extends InitController{

	public function index(){
		// 载入缓存数据
		Core_Extend::loadCache('group_toptag');
		
		Core_Extend::getSeo($this,array('title'=>Q::L('标签排行榜','Controller')));
		
		// 赋值
		$this->assign('arrHourHotgrouptopictags',$GLOBALS['_cache_']['group_toptag']['hour']);
		$this->assign('arrTodayHotgrouptopictags',$GLOBALS['_cache_']['group_toptag']['today']);
		$this->assign('arrWeekHotgrouptopictags',$GLOBALS['_cache_']['group_toptag']['week']);
		$this->assign('arrMonthHotgrouptopictags',$GLOBALS['_cache_']['group_toptag']['month']);
		$this->assign('arrYearHotgrouptopictags',$GLOBALS['_cache_']['group_toptag']['year']);
		$this->assign('arrTotalHotgrouptopictags',$GLOBALS['_cache_']['group_toptag']['total']);
		$this->display('tag+top');
	}

}
