<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   积分变更记录($$)*/

!defined('Q_PATH') && exit;

/** 导入积分相关函数 */
require_once(Core_Extend::includeFile('function/Credit_Extend'));

class Creditlog_C_Controller extends InitController{

	public function index(){
		// 可用积分
		$arrAvailableExtendCredits=Credit_Extend::getAvailableExtendCredits();
		$this->assign('arrAvailableExtendCredits',$arrAvailableExtendCredits);

		// 列表数据
		$nTotalRecord=Model::F_('creditlog','user_id=?',$GLOBALS['___login___']['user_id'])->all()->getCounts();
		$oPage=Page::RUN($nTotalRecord,$GLOBALS['_option_']['baselistnum']);
		$arrCreditlogs=Model::F_('creditlog','@A','A.user_id=?',$GLOBALS['___login___']['user_id'])
			->setColumns('A.*')
			->join(Q::C('DB_PREFIX').'creditoperation AS B','B.creditoperation_title ','A.creditlog_operation=B.creditoperation_name')
			->join(Q::C('DB_PREFIX').'user AS C','C.user_name','A.creditlog_relatedid=C.user_id')
			->order('A.create_dateline DESC')
			->limit($oPage->S(),$oPage->N())
			->getAll();

		Core_Extend::getSeo($this,array('title'=>Q::L('积分记录','Controller')));

		$this->assign('arrCreditlogs',$arrCreditlogs);
		$this->assign('sPageNavbar',$oPage->P(array('id'=>'pagination','style'=>'li','current'=>'active')));
		$this->display('spaceadmin+creditlog');
	}

}
