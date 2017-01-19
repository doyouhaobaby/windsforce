<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   短消息配置控制器($$)*/

!defined('Q_PATH') && exit;

class PmoptionController extends OptionController{

	public function index($sModel=null,$bDisplay=true){
		$this->assign('arrOptions',$GLOBALS['_option_']);
		$this->display();
	}

	public function sound(){
		$this->index();
	}

}
