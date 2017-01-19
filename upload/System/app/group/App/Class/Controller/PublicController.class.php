<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   群组首页控制器($$)*/

!defined('Q_PATH') && exit;

class PublicController extends InitController{

	public function index(){
		if($GLOBALS['_cache_']['group_option']['newtopic_default']==1){
			$this->_arrSeo['title']=Q::L('新帖','Controller');
			$this->child('Public@Newtopic','index');
		}else{
			$this->_arrSeo['title']=Q::L('小组','Controller');
			$this->child('Public@Index','index');
		}
	}

	public function newtopic(){
		if($GLOBALS['_cache_']['group_option']['newtopic_default']==1){
			$this->_arrSeo['title']=Q::L('小组','Controller');
			$this->child('Public@Index','index');
		}else{
			$this->_arrSeo['title']=Q::L('新帖','Controller');
			$this->child('Public@Newtopic','index');
		}
	}

	public function getSeo($oChild){
		Core_Extend::getSeo($oChild,array('title'=>$this->_arrSeo['title']),true);
	}

	public function getOption_(){
		$arrGroupOption=array(
			$GLOBALS['_cache_']['group_option']['group_totaltodaynum'],
			$GLOBALS['_cache_']['group_option']['group_topictodaynum'],
			$GLOBALS['_cache_']['group_option']['group_topiccommenttodaynum']
		);

		$this->assign('arrGroupOption',$arrGroupOption);
	}

}
