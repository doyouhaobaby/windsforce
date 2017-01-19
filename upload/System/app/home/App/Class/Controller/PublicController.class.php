<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   前台首页显示($$)*/

!defined('Q_PATH') && exit;

class PublicController extends InitController{

	public function index(){
		$this->child('Public@Index','index');
	}

	public function login(){
		$this->child('Public@Login','index');
	}

	public function socia_login(){
		$this->child('Public@Login','socia');
	}

	public function socia_callback(){
		$this->child('Public@Login','callback');
	}

	public function socia_bind(){
		$this->child('Public@Login','bind');
	}

	public function socia_unbind(){
		$this->child('Public@Login','unbind');
	}

	public function sociabind_again(){
		$this->child('Public@Login','bind_again');
	}

	public function check_login(){
		$this->child('Public@Login','check_login');
	}

	public function logout(){
		$this->child('Public@Login','logout');
	}

	public function clear(){
		$this->child('Public@Login','clear');
	}

	public function register(){
		$this->child('Public@Register','index');
	}
	
	public function check_user(){
		$this->child('Public@Register','check_user');
	}
	
	public function check_email(){
		$this->child('Public@Register','check_email');
	}
	
	public function register_user(){
		$this->child('Public@Register','register_user');
	}

	public function rbacerror(){
		$this->child('Public@Rbacerror','index');
	}

	public function myrbac(){
		$this->child('Public@Myrbac','index');
	}

	public function role(){
		$this->child('Public@Role','index');
	}

	public function option(){
		$this->child('Public@Option','index');
	}
	
	public function gethomefresh(){
		$this->child('Public@Gethomefresh','index');
	}
	
	public function url(){
		$this->child('Public@Url','index');
	}

	public function validate_seccode(){
		$this->child('Public@Validateseccode','index');
	}

}
