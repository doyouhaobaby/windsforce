<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   验证信息确认($$)*/

!defined('Q_PATH') && exit;

class Check_C_Controller extends InitController{

	public function index(){
		$sEmail=trim(Q::G('email','G'));
		$sHash=trim(Q::G('hash','G'));
		if(empty($sHash)){
			$this->U('home://spaceadmin/verifyemail');
		}

		$sHash=C::authcode($sHash);
		if(empty($sHash)){
			$this->assign('__JumpUrl__',Q::U('home://spaceadmin/verifyemail'));
			$this->E(Q::L('Email验证链接已过期','Controller'));
		}
		
		$oUser=UserModel::F('user_email=? AND user_verifycode=?',$sEmail,$sHash)->getOne();
		if(empty($oUser['user_id'])){
			$this->assign('__JumpUrl__',Q::U('home://spaceadmin/verifyemail'));
			$this->E(Q::L('Email验证链接已过期','Controller'));
		}

		// 确认验证状态
		$oUser->user_verifycode='';
		$oUser->user_isverify='1';
		$oUser->setAutofill(false);
		$oUser->save('update');
		if($oUser->isError()){
			$this->E($oUser->getErrorMessage());
		}

		// 更新积分
		Core_Extend::updateCreditByAction('verifyemail',$GLOBALS['___login___']['user_id']);

		$this->assign('__JumpUrl__',Q::U('home://spaceadmin/verifyemail'));
		$this->S(Q::L('恭喜Email验证通过','Controller'));
	}

}
