<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   活动用户中心显示($$)*/

!defined('Q_PATH') && exit;

class UcenterController extends InitEventController{
	
	public function init__(){
		parent::init__();
		$this->is_login();
	}
	
	public function index(){
		$this->child('Ucenter@Event/Index','index');
	}

	public function join(){
		$this->child('Ucenter@Event/Join','index');
	}
	
	public function join_delete(){
		$this->child('Ucenter@Event/Joindelete','index');
	}

	public function attention(){
		$this->child('Ucenter@Event/Attention','index');
	}
	
	public function attention_delete(){
		$this->child('Ucenter@Event/Attentiondelete','index');
	}
	
}
