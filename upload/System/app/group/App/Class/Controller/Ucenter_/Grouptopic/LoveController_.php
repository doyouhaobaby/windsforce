<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   我喜欢的话题列表($$)*/

!defined('Q_PATH') && exit;

class Love_C_Controller extends InitController{

	public function index(){
		// 喜欢的帖子
		$arrWhere=array();
		$arrWhere['A.user_id']=$GLOBALS['___login___']['user_id'];
		$arrWhere['B.grouptopic_status']=1;

		$nTotalGrouptopicnum=Model::F_('grouptopiclove','@A')->where($arrWhere)
			->join(Q::C('DB_PREFIX').'grouptopic AS B','B.*','A.grouptopic_id=B.grouptopic_id')
			->all()
			->getCounts();
		$oPage=Page::RUN($nTotalGrouptopicnum,$GLOBALS['_cache_']['group_option']['group_ucenter_listtopicnum']);
		$arrGrouptopicloves=Model::F_('grouptopiclove','@A')->where($arrWhere)
			->join(Q::C('DB_PREFIX').'grouptopic AS B','B.*','A.grouptopic_id=B.grouptopic_id')
			->join(Q::C('DB_PREFIX').'group AS C','C.group_name,C.group_nikename','C.group_id=B.group_id')
			->joinLeft(Q::C('DB_PREFIX').'grouptopiccategory AS D','D.grouptopiccategory_name','D.grouptopiccategory_id=B.grouptopiccategory_id')
			->limit($oPage->S(),$oPage->N())
			->order('A.create_dateline DESC')
			->getAll();

		Core_Extend::getSeo($this,array('title'=>Q::L('我喜欢的话题','Controller')));

		$this->assign('arrGrouptopicloves',$arrGrouptopicloves);
		$this->assign('sPageNavbar',$oPage->P(array('id'=>'pagination','style'=>'li','current'=>'active')));
		$this->display('ucentergrouptopic+love');
	}

}
