<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   积分规则($$)*/

!defined('Q_PATH') && exit;

/** 导入积分相关函数 */
require_once(Core_Extend::includeFile('function/Credit_Extend'));

class Creditrule_C_Controller extends InitController{

	public function index(){
		// 积分规则
		$arrCreditrules=Model::F_('creditrule')->order('creditrule_id ASC')->getAll();
		$this->assign('arrCreditrules',$arrCreditrules);

		Core_Extend::getSeo($this,array('title'=>Q::L('积分规则','Controller')));
		
		// 可用积分类型
		$arrAvailableExtendCredits=Credit_Extend::getAvailableExtendCredits();
		$this->assign('arrAvailableExtendCredits',$arrAvailableExtendCredits);
		$this->display('spaceadmin+creditrule');
	}

}
