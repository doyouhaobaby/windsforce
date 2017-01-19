<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   公告首页($$)*/

!defined('Q_PATH') && exit;

class Index_C_Controller extends InitController{

	public function index(){
		$arrWhere=array();
		$arrWhere['announcement_status']=1;
		$arrWhere['create_dateline']=array('elt',CURRENT_TIMESTAMP);
		$arrWhere['announcement_endtime']=array('egt',CURRENT_TIMESTAMP);
		
		$nTotalRecord=Model::F_('announcement')->where($arrWhere)
			->all()
			->getCounts();
		$oPage=Page::RUN($nTotalRecord,$GLOBALS['_option_']['baselistnum']);
		$arrAnnouncements=Model::F_('announcement')->where($arrWhere)
			->order('announcement_sort ASC,create_dateline DESC')
			->limit($oPage->S(),$oPage->N())
			->getAll();

		Core_Extend::getSeo($this,array('title'=>Q::L('公告中心','Controller')));

		$this->assign('arrAnnouncements',$arrAnnouncements);
		$this->assign('sPageNavbar',$oPage->P(array('id'=>'pagination','style'=>'li','current'=>'active')));
		$this->display('announcement+index');
	}

}
