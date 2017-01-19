<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   系统积分记录($$)*/

!defined('Q_PATH') && exit;

/** 导入积分相关函数 */
require_once(Core_Extend::includeFile('function/Credit_Extend'));

class Creditrulelog_C_Controller extends InitController{

	public function index(){
		// 系统积分记录
		$arrCreditrulelogs=Model::F_('creditrulelog','@A','user_id=?',$GLOBALS['___login___']['user_id'])
			->join(Q::C('DB_PREFIX').'creditrule AS B','B.creditrule_name ','A.creditrule_id=B.creditrule_id')
			->order('A.creditrulelog_id DESC')
			->getAll();
		$this->assign('arrCreditrulelogs',$arrCreditrulelogs);

		Core_Extend::getSeo($this,array('title'=>Q::L('系统积分奖励','Controller')));
		
		// 可用积分
		$arrAvailableExtendCredits=Credit_Extend::getAvailableExtendCredits();
		$this->assign('arrAvailableExtendCredits',$arrAvailableExtendCredits);
		$this->display('spaceadmin+creditrulelog');
	}

}
