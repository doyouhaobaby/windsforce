<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   注册与访问控制配置处理控制器($$)*/

!defined('Q_PATH') && exit;

class RegisteroptionController extends OptionController{

	public function index($sModel=null,$bDisplay=true){
		$this->assign('arrOptions',$GLOBALS['_option_']);
		$this->display();
	}

	public function login(){
		$this->index();
	}

}
