<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   删除验证信息($$)*/

!defined('Q_PATH') && exit;

class Un_C_Controller extends InitController{

	public function index(){
		$oUser=Model::F_('user','user_id=?',$GLOBALS['___login___']['user_id'])->getOne();
		if($oUser['user_isverify']==0){
			$this->E(Q::L('Email验证信息不存在，无需删除','Controller'));
		}

		if(empty($oUser['user_email'])){
			$this->E(Q::L('Email地址不能为空','Controller'));
		}

		Check::RUN();
		if(!Check::C($oUser['user_email'],'email')){
			$this->E(Q::L('Email格式不正确','Controller'));
		}

		if($oUser['user_status']==0){
			$this->E(Q::L('该账户已经被禁止','Controller'));
		}

		// 删除验证状态
		$oUser->user_verifycode='';
		$oUser->user_isverify='0';
		$oUser->setAutofill(false);
		$oUser->save('update');
		if($oUser->isError()){
			$this->E($oUser->getErrorMessage());
		}

		$this->assign('__JumpUrl__',Q::U('home://spaceadmin/verifyemail'));
		$this->S(Q::L('成功删除Email验证信息','Controller'));
	}

}
