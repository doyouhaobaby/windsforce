<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   活动App首页显示($$)*/

!defined('Q_PATH') && exit;

class PublicController extends InitEventController{

	public function index(){
		$this->child('Public@Index','index');
	}

}
