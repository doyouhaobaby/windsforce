<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   头像控制器($$)*/

!defined('Q_PATH') && exit;

/** 导入个人信息函数库 */
require(Core_Extend::includeFile('function/Profile_Extend'));

class Avatar_C_Controller extends InitController{

	public function index(){
		$nUserid=intval(Q::G('id','G'));
		if(empty($nUserid)){
			return '';
		}

		$arrUserInfo=Model::F_('user','@A')
			->setColumns('A.user_id,A.user_name,A.user_sign')
			->joinLeft(Q::C('DB_PREFIX').'userprofile AS B','B.userprofile_resideprovince,B.userprofile_residecity,B.userprofile_gender','A.user_id=B.user_id')
			->joinLeft(Q::C('DB_PREFIX').'usercount AS C','C.usercount_friends,C.usercount_fans,C.usercount_extendcredit1,C.usercount_extendcredit2','A.user_id=C.user_id')
			->where(array('A.user_status'=>1,'A.user_id'=>$nUserid))
			->getOne();

		if(empty($arrUserInfo['user_id'])){
			return '';
		}

		// 取得性别图标
		$sUsergender=Profile_Extend::getUserprofilegender($arrUserInfo['userprofile_gender']);

		$this->assign('arrUserInfo',$arrUserInfo);
		$this->assign('sUsergender',$sUsergender);
		$this->display('misc+avatar');
	}

}
