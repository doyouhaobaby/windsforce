<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   创建群组控制器($$)*/

!defined('Q_PATH') && exit;

class CreateController extends InitController{

	public function init__(){
		parent::init__();
		$this->is_login();

		if($GLOBALS['_cache_']['group_option']['allowed_creategroup']==0){
			$this->E(Q::L('系统关闭了申请创建小组功能','Controller'));
		}
	}
	
	public function index(){
		$this->child('Create@Index','index');
	}

	public function add(){
		$this->child('Create@Add','index');
	}

}
