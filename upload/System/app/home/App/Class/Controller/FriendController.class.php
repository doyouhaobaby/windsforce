<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   好友系统显示($$)*/

!defined('Q_PATH') && exit;

class FriendController extends InitController{

	public function init__(){
		parent::init__();
		$this->is_login();
	}
	
	public function index(){
		$this->child('Friend@Index','index');
	}

	public function add(){
		$this->child('Friend@Add','index');
	}
	
	public function delete(){
		$this->child('Friend@Delete','index');
	}
	
	public function edit(){
		$this->child('Friend@Edit','index');
	}

	public function search(){
		$this->child('Friend@Search','index');
	}

	public function searchresult(){
		$this->child('Friend@Searchresult','index');
	}

	public function mayknow(){
		$this->child('Friend@Mayknow','index');
	}
	
}
