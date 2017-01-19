<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   上传配置处理控制器($$)*/

!defined('Q_PATH') && exit;

class UploadoptionController extends OptionController{

	public function index($sModel=null,$bDisplay=true){
		$this->assign('arrOptions',$GLOBALS['_option_']);
		$this->assign('nUploadMaxFilesize',ini_get('upload_max_filesize'));
		$this->assign('nUploadSize',Core_Extend::getUploadSize());
		$this->display();
	}

	public function avatar(){
		$this->assign('arrOptions',$GLOBALS['_option_']);
		$this->assign('nUploadMaxFilesize',ini_get('upload_max_filesize'));
		$this->assign('nUploadSize',Core_Extend::getUploadSize($GLOBALS['_option_']['avatar_uploadfile_maxsize']));
		$this->display();
	}

	public function show(){
		$this->assign('arrOptions',$GLOBALS['_option_']);
		$this->display();
	}

	public function ubb(){
		$this->show();
	}

	public function attachment(){
		$this->show();
	}

}
