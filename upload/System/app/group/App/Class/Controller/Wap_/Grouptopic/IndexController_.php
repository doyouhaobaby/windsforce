<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   小组Wap首页控制器($$)*/

!defined('Q_PATH') && exit;

class Index_C_Controller extends WInitController{

	public function index(){
		// 读取帖子列表
		$arrWhere=array();
		$arrWhere['grouptopic_status']=1;

		$nTotalRecord=Model::F_('grouptopic')->where($arrWhere)
			->all()
			->getCounts();
		$oPage=Page::RUN($nTotalRecord,$GLOBALS['_option_']['wap_baselist_num']);
		$arrGrouptopics=Model::F_('grouptopic')->where($arrWhere)
			->setColumns('grouptopic_id,grouptopic_title,create_dateline')
			->order("grouptopic_update DESC,grouptopic_Id DESC")
			->limit($oPage->S(),$oPage->N())
			->getAll();

		Core_Extend::getSeo($this,array('title'=>Q::L('Wap小组','Controller')));

		$this->assign('arrGrouptopics',$arrGrouptopics);
		$this->assign('sPageNavbar',$oPage->P(array('id'=>'page')));
		$this->display('wap+index');
	}

}
