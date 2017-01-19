<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   帖子控制器($$)*/

!defined('Q_PATH') && exit;

class GrouptopicController extends InitController{

	public function init__(){
		parent::init__();

		if(!in_array(ACTION_NAME,array('view','set_grouptopicstyle','printtable','next','prev','readtopic'))){
			$this->is_login();
		}
	}
	
	public function add(){
		$this->child('Grouptopic@Add','index');
	}
	
	public function add_topic(){
		$this->child('Grouptopic@Addtopic','index');
	}

	public function view(){
		$this->child('Grouptopic@View','index');
	}

	public function edit(){
		$this->child('Grouptopic@Edit','index');
	}

	public function submit_edit(){
		$this->child('Grouptopic@Submitedit','index');
	}

	public function reply(){
		$this->child('Grouptopic@Reply','index');
	}
	
	public function add_reply(){
		$this->child('Grouptopic@Addreply','index');
	}

	public function set_grouptopicstyle(){
		$this->child('Grouptopic@Setgrouptopicstyle','index');
	}

	public function commenttopic_dialog(){
		$this->child('Grouptopic@Commenttopicdialog','index');
	}
	
	public function editcommenttopic_dialog(){
		$this->child('Grouptopic@Editcommenttopicdialog','index');
	}

	public function submit_reply(){
		$this->child('Grouptopic@Submitreply','index');
	}

	public function printtable(){
		$this->child('Grouptopic@Printtable','index');
	}
	
	public function next(){
		$this->child('Grouptopic@Next','index');
	}
	
	public function prev(){
		$this->child('Grouptopic@Prev','index');
	}
	
	public function readtopic(){
		$this->child('Grouptopic@Readtopic','index');
	}

	public function love(){
		$this->child('Grouptopic@Love','index');
	}

	public function love_add(){
		$this->child('Grouptopic@Loveadd','index');
	}

}
