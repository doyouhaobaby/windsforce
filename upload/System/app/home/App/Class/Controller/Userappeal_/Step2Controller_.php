<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   用户申诉第二步($$)*/

!defined('Q_PATH') && exit;

class Step2_C_Controller extends InitController{

	public function index(){
		if($GLOBALS['___login___']!==false){
			$this->U('home://ucenter/index');
		}

		$this->_oParent->check_seccode(true);
	
		$sUsername=trim(Q::G('user_name','P'));
		if(Core_Extend::isPostInt($sUsername)){
			$oUser=UserModel::F('user_id=?',$sUsername)->getOne();
		}else{
			$oUser=UserModel::F('user_name=?',$sUsername)->getOne();
		}
		
		if(empty($oUser->user_id)){
			$this->E(Q::L('用户名或者用户ID不存在','Controller'));
		}

		if($oUser->user_status==0){
			$this->E(Q::L('该账户已经被禁止','Controller'));
		}

		Core_Extend::getSeo($this,array('title'=>Q::L('填写联系方式','Controller')));
		
		$sUserid=C::authcode($oUser['user_id'],false,null,$GLOBALS['_option_']['appeal_expired']);
		$this->assign('sUserid',$sUserid);
		$this->display('userappeal+step2');
	}

}
