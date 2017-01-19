<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   访问推广($$)*/

!defined('Q_PATH') && exit;

/** 导入积分相关函数 */
require_once(Core_Extend::includeFile('function/Credit_Extend'));

class Index_C_Controller extends InitController{

	public function index(){
		$arrData=array();

		// 启用的积分类型
		$arrAvailableExtendCredits=Credit_Extend::getAvailableExtendCredits();
		
		// 赠送积分规则
		$arrCreditrules=Model::F_('creditrule')->where(array('creditrule_action'=>array('in','promotion_register,promotion_visit')))->order('creditrule_id DESC')->getAll();
		foreach($arrCreditrules as $arrCreditrule){
			foreach($arrAvailableExtendCredits as $nKey=>$arrAvailableExtendCredit){
				$arrData[$arrCreditrule['creditrule_action']][]=array('title'=>$arrAvailableExtendCredit['title'],'data'=>$arrCreditrule['creditrule_extendcredit'.$nKey]);
			}
		}

		$this->assign('arrCreditdata',$arrData);

		Core_Extend::getSeo($this,array('title'=>Q::L('访问推广','Controller')));
		
		// URL链接信息
		$this->assign('nUserId',Core_Extend::aidencode(intval($GLOBALS['___login___']['user_id'])));
		$this->assign('sUserName',rawurlencode(trim($GLOBALS['___login___']['user_name'])));
		$this->assign('sSiteName',$GLOBALS['_option_']['site_name']);
		$this->assign('sSiteUrl',Core_Extend::getSiteurl());
		$this->display('spaceadmin+promotion');
	}

	public function promotion_title_(){
		return Q::L('访问推广','Controller');
	}

	public function promotion_keywords_(){
		return $this->promotion_title_();
	}

	public function promotion_description_(){
		return $this->promotion_title_();
	}

}
