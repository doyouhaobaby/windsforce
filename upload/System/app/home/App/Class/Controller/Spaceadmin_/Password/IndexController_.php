<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   用户密码安全($$)*/

!defined('Q_PATH') && exit;

class Index_C_Controller extends InitController{

	public function index(){
		Core_Extend::getSeo($this,array('title'=>Q::L('修改密码','Controller')));
		
		$this->assign('nUserId',$GLOBALS['___login___']['user_id']);
		$this->assign('nDisplaySeccode',$GLOBALS['_option_']['seccode_changepassword_status']);
		$this->display('spaceadmin+password');
	}

}
