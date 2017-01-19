<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   头像上传控制器($$)*/

!defined('Q_PATH') && exit;

class AvatarController extends AController{

	public function index($sModel=null,$bDisplay=true){
		$arrAvatarInfo=array();
		$arrAvatarInfo=Core_Extend::avatars($GLOBALS['___login___']['user_id']);
		
		$arrOptionData=$GLOBALS['_option_'];

		$this->assign('arrAvatarInfo',$arrAvatarInfo);
		$this->assign('nUploadSize',Core_Extend::getUploadSize($arrOptionData['avatar_uploadfile_maxsize']));
		$this->display();
	}

	public function upload(){
		if($_FILES['image']['error']==4){
			$this->E(Q::L('你没有选择任何文件','Controller'));
			return;
		}

		if(!is_dir(dirname(WINDSFORCE_PATH.'/user/avatar/temp')) && !C::makeDir(WINDSFORCE_PATH.'/user/avatar/temp')){
			$this->E(Q::L('上传目录%s不可写','Controller',null,WINDSFORCE_PATH.'/user/avatar/temp'));
		}

		require_once(Core_Extend::includeFile('function/Avatar_Extend'));
		$oUploadfile=Avatar_Extend::avatarTemp();
		if(!$oUploadfile->upload()){
			$this->E($oUploadfile->getErrorMessage());
		}else{
			$arrPhotoInfo=$oUploadfile->getUploadFileInfo();
		}

		$this->assign('arrPhotoInfo',reset($arrPhotoInfo));
		$this->display();
	}

	public function save_crop(){
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

		$this->assign('__JumpUrl__',Q::U('avatar/index'));
		$this->S(Q::L('头像上传成功','Controller'));
	}

	public function un(){
		require_once(Core_Extend::includeFile('function/Avatar_Extend'));
		
		try{
			Avatar_Extend::deleteAvatar();
		}catch(Exception $e){
			$this->E($e->getMessage());
		}

		$this->S(Q::L('删除头像成功了','Controller'));
	}

}
