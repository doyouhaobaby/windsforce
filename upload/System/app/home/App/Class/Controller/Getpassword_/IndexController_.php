<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   找回密码首页($$)*/

!defined('Q_PATH') && exit;

class Index_C_Controller extends InitController{

	public function index(){
		if($GLOBALS['___login___']!==false){
			$this->U('home://spaceadmin/password');
		}

		Core_Extend::getSeo($this,array('title'=>Q::L('找回密码','Controller')));
	
		$this->display('getpassword+index');
	}

}
