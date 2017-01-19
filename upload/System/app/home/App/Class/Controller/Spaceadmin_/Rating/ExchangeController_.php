<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   积分兑换界面($$)*/

!defined('Q_PATH') && exit;

/** 导入积分相关函数 */
require_once(Core_Extend::includeFile('function/Credit_Extend'));

class Exchange_C_Controller extends InitController{

	public function index(){
		$arrUserInfo=Model::F_('user','@A')
			->setColumns('A.user_id,A.user_name,A.create_dateline,A.user_lastlogintime,A.user_sign,A.user_nikename')
			->join(Q::C('DB_PREFIX').'usercount AS C','C.*','A.user_id=C.user_id')
			->where(array('A.user_status'=>1,'A.user_id'=>$GLOBALS['___login___']['user_id']))
			->getOne();

		// 可用积分
		$arrAvailableExtendCredits=Credit_Extend::getAvailableExtendCredits();
		$this->assign('arrAvailableExtendCredits',$arrAvailableExtendCredits);

		// 提示性数据
		$this->assign('nExchangeMincredits',$GLOBALS['_option_']['exchange_mincredits']);

		Core_Extend::getSeo($this,array('title'=>Q::L('积分兑换','Controller')));
		
		$this->assign('arrUserInfo',$arrUserInfo);
		$this->display('spaceadmin+exchange');
	}

}
