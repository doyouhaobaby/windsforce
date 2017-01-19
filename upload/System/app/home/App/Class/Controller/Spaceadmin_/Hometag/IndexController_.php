<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   标签首页($$)*/

!defined('Q_PATH') && exit;

class Index_C_Controller extends InitController{

	public function index(){
		$oTag=Q::instance('HometagModel');
		$arrHometags=$oTag->getTagsByUserid($GLOBALS['___login___']['user_id']);

		Core_Extend::getSeo($this,array('title'=>Q::L('用户标签','Controller')));
		
		$this->assign('arrHometags',$arrHometags);
		$this->display('spaceadmin+tag');
	}

}
