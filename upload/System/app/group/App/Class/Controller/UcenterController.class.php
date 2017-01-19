<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   小组用户中心显示($$)*/

!defined('Q_PATH') && exit;

class UcenterController extends InitController{
	
	public function init__(){
		parent::init__();
		$this->is_login();
	}
	
	public function index(){
		$this->child('Ucenter@Grouptopic/Index','index');
	}

	public function lovetopic(){
		$this->child('Ucenter@Grouptopic/Love','index');
	}
	
	public function lovetopic_delete(){
		$this->child('Ucenter@Grouptopic/Lovetopicdelete','index');
	}
	
}
