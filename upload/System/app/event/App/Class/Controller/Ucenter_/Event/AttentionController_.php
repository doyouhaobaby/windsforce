<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   我感兴趣的活动列表($$)*/

!defined('Q_PATH') && exit;

class Attention_C_Controller extends InitController{

	public function index(){
		// 活动
		$nTotalEventnum=Model::F_('eventattentionuser','@A','A.user_id=?',$GLOBALS['___login___']['user_id'])
			->all()
			->getCounts();
		$oPage=Page::RUN($nTotalEventnum,16);
		$arrEventattentionusers=Model::F_('eventattentionuser','@A','A.user_id=?',$GLOBALS['___login___']['user_id'])
			->join(Q::C('DB_PREFIX').'event AS B','B.*','A.event_id=B.event_id')
			->limit($oPage->S(),$oPage->N())
			->order('A.create_dateline DESC')
			->getAll();

		Core_Extend::getSeo($this,array('title'=>Q::L('我感兴趣的活动','Controller')));

		$this->assign('arrEventattentionusers',$arrEventattentionusers);
		$this->assign('sPageNavbar',$oPage->P(array('id'=>'pagination','style'=>'li','current'=>'active')));
		$this->display('ucenterevent+attention');
	}

}
