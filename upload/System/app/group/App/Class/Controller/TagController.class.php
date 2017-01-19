<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   小组标签控制器($$)*/

!defined('Q_PATH') && exit;

class TagController extends InitController{

	public function index(){
		$this->child('Tag@Index','index');
	}

	public function show(){
		$this->child('Tag@Show','index');
	}

	public function top(){
		$this->child('Tag@Top','index');
	}

}
