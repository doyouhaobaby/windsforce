<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   短消息控制器($$)*/

!defined('Q_PATH') && exit;

require_once(Core_Extend::includeFile('function/Pm_Extend'));

class PmController extends InitController{

	public function init__(){
		parent::init__();
		$this->is_login();
	}

	public function dialog_add(){
		$this->child('Pm@Dialogadd','index');
	}

	public function pmnew(){
		$this->child('Pm@Pmnew','index');
	}

	public function sendpm(){
		$this->child('Pm@Sendpm','index');
	}
	
	public function index(){
		$this->child('Pm@Index','index');
	}

	public function del_one_pm($nId='',$nUserId='',$nFromId=''){
		$this->child('Pm@Delonepm','index');
	}

	public function delselect(){
		$this->child('Pm@Delonepm','select');
	}

	public function del_my_one_pm($nId='',$nUserId=''){
		$this->child('Pm@Delmyonepm','index');
	}

	public function delmyselect(){
		$this->child('Pm@Delmyonepm','myselect');
	}

	public function show(){
		$this->child('Pm@Show','index');
	}

	public function truncatepm(){
		$this->child('Pm@Truncatepm','index');
	}
	
	public function readselect(){
		$this->child('Pm@Readselect','index');
	}

	public function delete_systempm(){
		$this->child('Pm@Deletesystempm','index');
	}

	public function check_pm(){
		$this->child('Pm@Checkpm','index');
	}

}
