<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   查询申诉页面($$)*/

!defined('Q_PATH') && exit;

class Schedule_C_Controller extends InitController{

	public function index(){
		if($GLOBALS['___login___']!==false){
			$this->U('home://ucenter/index');
		}

		Core_Extend::getSeo($this,array('title'=>Q::L('查询申诉进度','Controller')));

		$this->display('userappeal+schedule');
	}

}
