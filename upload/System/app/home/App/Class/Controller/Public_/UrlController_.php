<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   外部Url跳转($$)*/

!defined('Q_PATH') && exit;

class Url_C_Controller extends InitController{

	public function index(){
		/// 处理URL
		$sUrl=isset($_GET['go'])?$_GET['go']:'';
		$sUrl=urldecode(trim($sUrl));
		$sUrl=str_replace(array("%2F","%3D","%3F","&amp;"),array('/','=','?','&'),$sUrl);

		// 跳转
		if(!empty($sUrl)){
			header("Location:{$sUrl}");
		}else{
			echo 'Url is empty!';
		}

		exit();
	}

}
