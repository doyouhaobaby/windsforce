<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   密码修改确认($$)*/

!defined('Q_PATH') && exit;

class Change_C_Controller extends InitController{

	public function index(){
		if($GLOBALS['_option_']['seccode_changepassword_status']==1){
			$this->_oParent->check_seccode(true);
		}

		$sPassword=Q::G('user_password','P');
		$sNewPassword=Q::G('new_password','P');
		$sOldPassword=Q::G('old_password','P');

		$oUserModel=Q::instance('UserModel');
		$oUserModel->changePassword($sPassword,$sNewPassword,$sOldPassword);
		if($oUserModel->isError()){
			$this->E($oUserModel->getErrorMessage());
		}else{
			$this->S(Q::L('密码修改成功，你需要重新登录','Controller'));
		}
	}

}
