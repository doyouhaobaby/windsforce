<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   主页接口控制器($$)*/

!defined('Q_PATH') && exit;
!defined('IN_API') && exit();

class ApiController extends InitController{

	public function newuser(){
		$this->child('Api@Newuser','index');
	}

}
