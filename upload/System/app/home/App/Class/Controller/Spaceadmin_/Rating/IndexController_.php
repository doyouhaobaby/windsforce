<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   积分管理信息($$)*/

!defined('Q_PATH') && exit;

/** 导入积分相关函数 */
require_once(Core_Extend::includeFile('function/Credit_Extend'));

class Index_C_Controller extends InitController{

	public function index(){
		$arrUserInfo=Model::F_('user','@A')
			->setColumns('A.user_id,A.user_name,A.create_dateline,A.user_lastlogintime,A.user_sign,A.user_nikename')
			->join(Q::C('DB_PREFIX').'userprofile AS B','B.*','A.user_id=B.user_id')
			->join(Q::C('DB_PREFIX').'usercount AS C','C.*','A.user_id=C.user_id')
			->where(array('A.user_status'=>1,'A.user_id'=>$GLOBALS['___login___']['user_id']))
			->getOne();

		// 用户等级名字
		$this->assign('arrRatinginfo',Core_Extend::getUserrating($arrUserInfo['usercount_extendcredit1'],false));
		$this->assign('nUserscore',$arrUserInfo['usercount_extendcredit1']);

		// 所有可用积分
		$arrAvailableExtendCredits=Credit_Extend::getAvailableExtendCredits();
		$this->assign('arrAvailableExtendCredits',$arrAvailableExtendCredits);

		// 最近积分记录
		$arrCreditlogs=Model::F_('creditlog','@A','A.user_id=?',$GLOBALS['___login___']['user_id'])
			->setColumns('A.creditlog_relatedid,A.creditlog_extcredits2,A.create_dateline')
			->join(Q::C('DB_PREFIX').'creditoperation AS B','B.creditoperation_title ','A.creditlog_operation=B.creditoperation_name')
			->join(Q::C('DB_PREFIX').'user AS C','C.user_name','A.creditlog_relatedid=C.user_id')
			->order('A.create_dateline DESC')
			->limit(0,10)
			->getAll();

		Core_Extend::getSeo($this,array('title'=>Q::L('积分','Controller')));

		$this->assign('arrCreditlogs',$arrCreditlogs);
		$this->assign('arrUserInfo',$arrUserInfo);
		$this->display('spaceadmin+rating');
	}

}
