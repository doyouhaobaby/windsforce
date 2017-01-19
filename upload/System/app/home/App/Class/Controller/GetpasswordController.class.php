<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   前台找回密码控制器($$)*/

!defined('Q_PATH') && exit;

class GetpasswordController extends InitController{

	public function index(){
		$this->child('Getpassword@Index','index');
	}

	public function email(){
		$this->child('Getpassword@Email','index');
	}

	public function reset(){
		$this->child('Getpassword@Reset','index');
	}

	public function change_pass(){
		$this->child('Getpassword@Changepass','index');
	}

}
