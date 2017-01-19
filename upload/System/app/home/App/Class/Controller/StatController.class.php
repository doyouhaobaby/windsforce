<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   站点统计显示($$)*/

!defined('Q_PATH') && exit;

class StatController extends InitController{

	public function index(){
		$this->child('Stat@Base','index');
	}

	public function usertop(){
		$this->child('Stat@Usertop','index');
	}
	
}
