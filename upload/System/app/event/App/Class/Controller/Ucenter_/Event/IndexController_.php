<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   我发起的活动列表($$)*/

!defined('Q_PATH') && exit;

class Index_C_Controller extends InitController{

	public function index(){
		// 活动
		$arrWhere=array();
		$arrWhere['A.event_status']=1;
		$arrWhere['A.user_id']=$GLOBALS['___login___']['user_id'];

		$nTotaleventnum=Model::F_('event','@A')->where($arrWhere)
			->all()
			->getCounts();
		$oPage=Page::RUN($nTotaleventnum,16);
		$arrEvents=Model::F_('event','@A')->where($arrWhere)
			->setColumns('A.*')
			->joinLeft(Q::C('DB_PREFIX').'eventcategory AS B','B.eventcategory_name','A.eventcategory_id=B.eventcategory_id')
			->order("A.event_id DESC")
			->limit($oPage->S(),$oPage->N())
			->getAll();

		Core_Extend::getSeo($this,array('title'=>Q::L('活动用户中心','Controller')));

		$this->assign('arrEvents',$arrEvents);
		$this->assign('sPageNavbar',$oPage->P(array('id'=>'pagination','style'=>'li','current'=>'active')));
		$this->display('ucenterevent+index');
	}

}
