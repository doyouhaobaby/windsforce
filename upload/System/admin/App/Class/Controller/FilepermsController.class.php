<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   文件权限控制器($$)*/

!defined('Q_PATH') && exit;

class FilepermsController extends AController{

	public function init__(){
		parent::init__();

		if($GLOBALS['___login___']['user_id']!=1){
			$this->E(Q::L('只有用户ID为1的超级管理员才能够访问本页','Controller'));
		}
	}

	public function index($sName=null,$bDisplay=true){
		$this->display();
	}

	public function step2(){
		$this->assign('__WaitSecond__',2);
		$this->assign('__JumpUrl__',Q::U('fileperms/step3'));
		$this->S(Q::L('正在进行文件权限检查，请稍候','Controller').'...');
	}

	public function step3(){
		$arrTestDirs=(array)(include WINDSFORCE_PATH.'/System/common/Cache.php');
		$this->assign('arrTestDirs',$arrTestDirs);
		$this->display();
	}

}
