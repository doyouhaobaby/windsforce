<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   用户申诉首页($$)*/

!defined('Q_PATH') && exit;

class Index_C_Controller extends InitController{

	public function index(){
		if($GLOBALS['___login___']!==false){
			$this->U('home://ucenter/index');
		}

		Core_Extend::getSeo($this,array('title'=>Q::L('用户申诉','Controller')));

		$this->display('userappeal+index');
	}

}
