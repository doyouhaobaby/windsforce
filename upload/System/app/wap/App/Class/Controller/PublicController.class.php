<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   Wap首页显示($$)*/

!defined('Q_PATH') && exit;

class PublicController extends WInitController{

	public function index(){
		$this->child('Public@Index','index');
	}

	public function login(){
		$this->child('Public@Login','index');
	}
	
	public function check_login(){
		$this->child('Public@Login','check');
	}
	
	public function logout(){
		$this->child('Public@Login','out');
	}

}
