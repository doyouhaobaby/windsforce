<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   完成密码修改($$)*/

!defined('Q_PATH') && exit;

class Changepass_C_Controller extends InitController{

	public function index(){
		$this->_oParent->check_seccode(true);

		$sPassword=trim(Q::G('user_password','P'));
		$sNewPassword=trim(Q::G('new_password','P'));
		$sEmail=trim(Q::G('user_email','P'));
		$nAppeal=intval(Q::G('appeal','P'));
		$nUserId=intval(Q::G('user_id','P'));
		
		if(!empty($nUserId) && $nAppeal==1){
			$oUser=UserModel::F('user_id=?',$nUserId)->getOne();
		}else{
			$oUser=UserModel::F('user_email=?',$sEmail)->getOne();
		}

		if(empty($oUser->user_id)){
			$this->E(Q::L('Email账号不存在','Controller'));
		}
		if($oUser->user_status==0){
			$this->E(Q::L('该账户已经被禁止','Controller'));
		}

		$oUserModel=Q::instance('UserModel');
		$oUserModel->changePassword($sPassword,$sNewPassword,'',true,$oUser->toArray(),true);
		if($oUserModel->isError()){
			$this->E($oUserModel->getErrorMessage());
		}else{
			$oUser->user_temppassword='';
			$oUser->setAutofill(false);
			$oUser->save('update');
			if($oUser->isError()){
				$this->E($oUser->getErrorMessage());
			}

			$this->S(Q::L('密码修改成功，你需要重新登录','Controller'));
		}
	}

}
