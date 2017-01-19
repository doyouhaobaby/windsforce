<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   头像保存($$)*/

!defined('Q_PATH') && exit;

class Savecrop_C_Controller extends InitController{

	public function index(){
		require_once(Core_Extend::includeFile('function/Avatar_Extend'));

		try{
			$bResult=Avatar_Extend::saveCrop();
			if($bResult===false){
				$this->E(Q::L('你的PHP 版本或者配置中不支持如下的函数 “imagecreatetruecolor”、“imagecopyresampled”等图像函数，所以创建不了头像','Controller'));
			}
		}catch(Exception $e){
			$this->E($e->getMessage());
		}

		// 更新是否上传头像
		$oUser=UserModel::F('user_id=?',$GLOBALS['___login___']['user_id'])->getOne();
		if(!empty($oUser['user_id'])){
			$oUser->user_avatar='1';
			$oUser->setAutofill(false);
			$oUser->save('update');
			if($oUser->isError()){
				$this->E($oUser->getErrorMessage());
			}
		}

		Core_Extend::updateCreditByAction('setavatar',$GLOBALS['___login___']['user_id']);

		$this->assign('__JumpUrl__',Q::U('spaceadmin/avatar'));
		$this->S(Q::L('头像上传成功','Controller'));
	}

}
