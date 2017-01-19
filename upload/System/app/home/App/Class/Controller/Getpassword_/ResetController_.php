<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   重置密码页面($$)*/

!defined('Q_PATH') && exit;

class Reset_C_Controller extends InitController{

	public function index(){
		if($GLOBALS['___login___']!==false){
			$this->U('home://spaceadmin/password');
		}

		$sEmail=trim(Q::G('email','G'));
		$sHash=trim(Q::G('hash','G'));
		$nAppeal=intval(Q::G('appeal','G'));
		if(empty($sHash)){
			$this->U('home://getpassword/index');
		}

		$sHash=C::authcode($sHash);
		if(empty($sHash)){
			$this->assign('__JumpUrl__',Q::U('home://getpassword/index'));
			$this->E(Q::L('找回密码链接已过期','Controller'));
		}
		
		if($nAppeal==1){
			$arrUser=Model::F_('user','user_temppassword=?',$sHash)->getOne();
		}else{
			$arrUser=Model::F_('user','user_email=? AND user_temppassword=?',$sEmail,$sHash)->getOne();
		}
		
		if(empty($arrUser['user_id'])){
			$this->assign('__JumpUrl__',Q::U('home://getpassword/index'));
			$this->E(Q::L('找回密码链接已过期','Controller'));
		}

		Core_Extend::getSeo($this,array('title'=>Q::L('密码重置','Controller')));

		$this->assign('sEmail',$sEmail);
		$this->assign('nAppeal',$nAppeal);
		$this->assign('user_id',$arrUser['user_id']);
		$this->display('getpassword+reset');
	}

}
