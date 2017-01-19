<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   头像上传后裁剪页面($$)*/

!defined('Q_PATH') && exit;

class Upload_C_Controller extends InitController{

	public function index(){
		if(!isset($_FILES['image'])){
			$this->assign('__JumpUrl__',Q::U('spaceadmin/avatar'));
			$this->E(Q::L('你不能直接进入裁减界面，请先上传','Controller'));
			return;
		}

		if($_FILES['image']['error']==4){
			$this->E(Q::L('你没有选择任何文件','Controller'));
			return;
		}

		if(!is_dir(dirname(WINDSFORCE_PATH.'/user/avatar/temp')) && !C::makeDir(WINDSFORCE_PATH.'/data/avatar/temp')){
			$this->E(Q::L('上传目录 %s 不可写','Controller',null,WINDSFORCE_PATH.'/user/avatar/temp'));
		}

		require_once(Core_Extend::includeFile('function/Avatar_Extend'));
		$oUploadfile=Avatar_Extend::avatarTemp();
		if(!$oUploadfile->upload()){
			$this->E($oUploadfile->getErrorMessage());
		}else{
			$arrPhotoInfo=$oUploadfile->getUploadFileInfo();
		}

		Core_Extend::getSeo($this,array('title'=>Q::L('裁剪头像','Controller')));

		$this->assign('arrPhotoInfo',reset($arrPhotoInfo));
		$this->display('spaceadmin+avatarupload');
	}

}
