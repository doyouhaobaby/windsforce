<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   公告控制器($$)*/

!defined('Q_PATH') && exit;

class AnnouncementController extends InitController{

	public function index(){
		$this->child('Announcement@Index','index');
	}

	public function show(){
		$this->child('Announcement@Show','index');
	}

}
