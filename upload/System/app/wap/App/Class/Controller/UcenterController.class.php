<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   Wap个人中心($$)*/

!defined('Q_PATH') && exit;

class UcenterController extends WInitController{
	
	public function init__(){
		parent::init__();

		if(ACTION_NAME!=='view'){
			$this->is_login();
		}
	}
	
	public function index(){
		$this->child('Ucenter@Homefresh/Index','index');
	}

	public function add_homefresh(){
		$this->child('Ucenter@Homefresh/Add','index');
	}

	public function view(){
		$this->child('Ucenter@Homefresh/View','index');
	}

}
