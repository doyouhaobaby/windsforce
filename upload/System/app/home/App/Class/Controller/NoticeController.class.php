<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   提醒控制器($$)*/

!defined('Q_PATH') && exit;

class NoticeController extends InitController{

	public function init__(){
		parent::init__();
		$this->is_login();
	}

	public function index(){
		$this->child('Notice@Index','index');
	}

}
