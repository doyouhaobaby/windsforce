<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   个人空间等级显示($$)*/

!defined('Q_PATH') && exit;

/** 导入积分相关函数 */
require_once(Core_Extend::includeFile('function/Credit_Extend'));

class Rating_C_Controller extends InitController{
	
	public function index(){
		$nId=intval(Q::G('id','G'));

		$arrUserInfo=Model::F_('user','@A')
			->setColumns('A.user_id,A.user_name,A.create_dateline,A.user_lastlogintime,A.user_sign,A.user_nikename')
			->join(Q::C('DB_PREFIX').'userprofile AS B','B.*','A.user_id=B.user_id')
			->join(Q::C('DB_PREFIX').'usercount AS C','C.*','A.user_id=C.user_id')
			->where(array('A.user_status'=>1,'A.user_id'=>$nId))
			->getOne();

		if(empty($arrUserInfo['user_id'])){
			$this->E(Q::L('你指定的用户不存在','Controller'));
		}else{
			$this->assign('arrUserInfo',$arrUserInfo);
		}

		$this->_arrUserInfo=$arrUserInfo;

		Core_Extend::loadCache('rating');
		Core_Extend::loadCache('ratinggroup');

		// 用户等级名字
		$this->assign('arrRatinginfo',Core_Extend::getUserrating($arrUserInfo['usercount_extendcredit1'],false));
		$this->assign('nUserscore',$arrUserInfo['usercount_extendcredit1']);

		// 所有可用积分
		$arrAvailableExtendCredits=Credit_Extend::getAvailableExtendCredits();
		$this->assign('arrAvailableExtendCredits',$arrAvailableExtendCredits);

		Core_Extend::getSeo($this,array('title'=>$arrUserInfo['user_name'].' - '.Q::L('积分','Controller')));
		
		$this->assign('nId',$nId);
		$this->assign('arrUserInfo',$arrUserInfo);
		$this->display('space+rating');
	}

}
