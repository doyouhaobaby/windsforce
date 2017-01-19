<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   邮件验证($$)*/

!defined('Q_PATH') && exit;

class Index_C_Controller extends InitController{

	public function index(){
		// 判断是否已经发送了验证邮件
		$arrUser=Model::F_('user','user_id=?',$GLOBALS['___login___']['user_id'])->setColumns('user_id,user_verifycode,user_isverify,user_email')->getOne();
		if(!empty($arrUser['user_id'])){
			$this->assign('bSendemail',$arrUser['user_verifycode']?true:false);
		}

		Core_Extend::getSeo($this,array('title'=>Q::L('Email验证','Controller')));
		
		$this->assign('arrUserlogin',$arrUser);
		$this->display('spaceadmin+verifyemail');
	}

}
