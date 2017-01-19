<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   头像上传界面($$)*/

!defined('Q_PATH') && exit;

class Index_C_Controller extends InitController{

	public function index(){
		$arrAvatarInfo=Core_Extend::avatars($GLOBALS['___login___']['user_id']);
		Core_Extend::getSeo($this,array('title'=>Q::L('修改头像','Controller')));
		
		$this->assign('arrAvatarInfo',$arrAvatarInfo);
		$this->assign('nUploadSize',Core_Extend::getUploadSize($GLOBALS['_option_']['avatar_uploadfile_maxsize']));
		$this->display('spaceadmin+avatar');
	}

}
