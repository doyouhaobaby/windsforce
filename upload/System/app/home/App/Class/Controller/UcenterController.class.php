<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   用户中心显示($$)*/

!defined('Q_PATH') && exit;

class UcenterController extends InitController{

	public function init__(){
		parent::init__();

		if(ACTION_NAME!=='view'){
			$this->is_login();
		}
	}
	
	public function index(){
		$this->child('Ucenter@Homefresh/Index','index');
	}

	public function post(){
		$this->child('Ucenter@Homefresh/Post','index');
	}
	
	public function edit(){
		$this->child('Ucenter@Homefresh/Edit','index');
	}

	public function homefresh_parse(){
		$this->child('Ucenter@Homefresh/Parse','index');
	}

	public function add_homefresh(){
		$this->child('Ucenter@Homefresh/Add','index');
	}

	public function update_homefresh(){
		$this->child('Ucenter@Homefresh/Update','index');
	}

	public function view(){
		$this->child('Ucenter@Homefresh/View','index');
	}

	public function homefreshtopic(){
		$this->child('Ucenter@Homefresh/Topic','index');
	}

	public function add_homefreshcomment(){
		$this->child('Ucenter@Homefresh/Addcomment','index');
	}

	public function update_homefreshgoodnum(){
		$this->child('Ucenter@Homefresh/Updategoodnum','index');
	}

	public function feed(){
		$this->child('Ucenter@Feed/Index','index');
	}

	public function getmorefeed(){
		$this->child('Ucenter@Feed/Getmorefeed','index');
	}

	public function tag(){
		$this->child('Ucenter@Tag/Index','index');
	}	
	
	public function tags(){
		$this->child('Ucenter@Tag/Tags','index');
	}
	
}
