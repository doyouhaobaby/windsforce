<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   小组个人空间基本资料($$)*/

!defined('Q_PATH') && exit;

/** 导入个人信息处理函数 */
require_once(Core_Extend::includeFile('function/Profile_Extend'));

class Base_C_Controller extends InitController{

	public function index(){
		$nId=intval(Q::G('id','G'));

		$arrUserInfo=Model::F_('user','@A')
			->setColumns('A.user_id,A.user_name,A.create_dateline,A.user_lastlogintime,A.user_sign,A.user_nikename')
			->join(Q::C('DB_PREFIX').'userprofile AS B','B.userprofile_gender','A.user_id=B.user_id')
			->where(array('A.user_status'=>1,'A.user_id'=>$nId))
			->getOne();
		if(empty($arrUserInfo['user_id'])){
			$this->_oParent->wap_mes(Q::L('你指定的用户不存在','Controller'),'',0);
		}else{
			$this->assign('arrUserInfo',$arrUserInfo);
			$this->_arrUserInfo=$arrUserInfo;
		}

		Core_Extend::getSeo($this,array('title'=>$arrUserInfo['user_name'].' - '.Q::L('Wap个人空间','Controller')));
		
		$this->assign('nId',$nId);
		$this->display('space+index');
	}

}
