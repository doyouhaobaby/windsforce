<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   个人信息修改保存($$)*/

!defined('Q_PATH') && exit;

class Change_C_Controller extends InitController{

	public function index(){
		if($GLOBALS['_option_']['seccode_changeinformation_status']==1){
			$this->_oParent->check_seccode(true);
		}

		// 查找用户
		$nUserId=Q::G('user_id','P');
		$oUser=UserModel::F('user_id=?',$nUserId)->query();
		$sOldemail=$oUser['user_email'];
		$oUser->save('update');
		if($oUser->isError()){
			$this->E($oUser->getErrorMessage());
		}else{
			// 保存扩展信息
			$oProfileset=UserprofileModel::F('user_id=?',$nUserId)->getOne();
			$oProfileset->save('update');
			if($oProfileset->isError()){
				$this->E($oProfileset->getErrorMessage());
			}
			
			// Email如果修改了，则删除其认证信息
			if($sOldemail!=$oUser['user_email']){
				$oUser->user_verifycode='';
				$oUser->user_isverify='0';
				$oUser->setAutofill(false);
				$oUser->save('update');
				if($oUser->isError()){
					$this->E($oUser->getErrorMessage());
				}
			}

			$this->S(Q::L('修改用户资料成功','Controller'));
		}
	}

}
