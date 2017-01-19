<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   程序版权配置处理控制器($$)*/

!defined('Q_PATH') && exit;

class ProgramoptionController extends OptionController{

	public function index($sModel=null,$bDisplay=true){
		$this->assign('arrOptions',$GLOBALS['_option_']);
		$this->display();
	}

}
