<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   Helloworld入口控制器($$)*/

!defined('Q_PATH') && exit;

class HelloworldmainController extends AController{

	public function index($sModel=null,$bDisplay=true){
		$this->display(Admin_Extend::template('helloworld','helloworldmain/index'));
	}

}
