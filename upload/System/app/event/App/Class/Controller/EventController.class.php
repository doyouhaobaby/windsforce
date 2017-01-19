<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   活动相关($$)*/

!defined('Q_PATH') && exit;

class EventController extends InitEventController{

	public function init__(){
		parent::init__();

		if(!in_array(ACTION_NAME,array('show'))){
			$this->is_login();
		}
	}
	
	public function add(){
		$this->child('Event@Add','index');
	}
	
	public function add_in(){
		$this->child('Event@Addin','index');
	}

	public function show(){
		$this->child('Event@Show','index');
	}

	public function join(){
		$this->child('Event@Join','index');
	}

	public function join_in(){
		$this->child('Event@Joinin','index');
	}

	public function attention(){
		$this->child('Event@Attention','index');
	}

	public function add_eventcomment(){
		$this->child('Event@Addeventcomment','index');
	}

	public function audit(){
		$this->child('Event@Audit','index');
	}

	public function edit(){
		$this->child('Event@Edit','index');
	}

	public function update(){
		$this->child('Event@Update','index');
	}

	public function delete(){
		$this->child('Event@Delete','index');
	}

	public function end(){
		$this->child('Event@End','index');
	}

}
