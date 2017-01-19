<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   标签列表($$)*/

!defined('Q_PATH') && exit;

class Index_C_Controller extends InitController{

	public function index(){
		// 读取标签列表
		$nTotalGrouptopictagnum=Model::F_('grouptopictag')
			->all()
			->getCounts();
		$oPage=Page::RUN($nTotalGrouptopictagnum,$GLOBALS['_cache_']['group_option']['group_tag_listnum']);
		$arrGrouptopictags=Model::F_('grouptopictag')
			->order("grouptopictag_id DESC")
			->limit($oPage->S(),$oPage->N())
			->getAll();

		Core_Extend::getSeo($this,array('title'=>Q::L('标签列表','Controller')));
		
		$this->assign('arrGrouptopictags',$arrGrouptopictags);
		$this->assign('nEverynum',$GLOBALS['_cache_']['group_option']['group_tag_listnum']);
		$this->assign('sPageNavbar',$oPage->P(array('id'=>'pagination','style'=>'li','current'=>'active')));
		$this->display('tag+index');
	}

}
