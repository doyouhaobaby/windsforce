<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   对话框发送短消息($$)*/

!defined('Q_PATH') && exit;

class Dialogadd_C_Controller extends InitController{

	public function index(){
		try{
			Core_Extend::checkSpam();
		}catch(Exception $e){
			exit($e->getMessage());
		}
		
		$this->_oParent->check_pm();
		
		$nUserid=intval(Q::G('uid','G'));
		$sUserName='';
		if(!empty($nUserid)){
			$arrUser=Model::F_('user','user_id=?',$nUserid)->setColumns('user_id,user_name')->getOne();
			if(!empty($arrUser['user_id'])){
				$sUserName=$arrUser['user_name'];
			}
		}

		$this->assign('sUserName',$sUserName);
		$this->assign('nDisplaySeccode',$GLOBALS['_option_']['pmsend_seccode']);
		$this->assign('sContent','');
		$this->display('pm+dialogadd');
	}

}
