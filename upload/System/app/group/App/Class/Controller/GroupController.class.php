<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   群组控制器($$)*/

!defined('Q_PATH') && exit;

class GroupController extends InitController{

	public function show(){
		$this->child('Group@Show','index');
	}

	public function joingroup(){
		$this->child('Group@Joingroup','index');
	}

	public function leavegroup(){
		$this->child('Group@Leavegroup','index');
	}

	public function getcategory(){
		$this->child('Group@Getcategory','index');
	}

	public function user(){
		$this->child('Group@User','index');
	}

}
