<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   编辑新鲜事($$)*/

!defined('Q_PATH') && exit;

class Edit_C_Controller extends InitController{

	public function index(){
		$nId=intval(Q::G('id','G'));
		if(empty($nId)){
			$this->E(Q::L('未指定新鲜事ID','Controller'));
		}

		$arrHomefresh=Model::F_('homefresh','homefresh_id=? AND homefresh_status=1',$nId)->getOne();
		if(empty($arrHomefresh['homefresh_id'])){
			$this->E(Q::L('编辑的新鲜事不存在','Controller'));
		}

		if(!Home_Extend::checkHomefreshedit($arrHomefresh)){
			$this->E(Q::L('你没有权限编辑新鲜事','Controller'));
		}

		// 载入文件分类
		Core_Extend::loadCache('category');

		Core_Extend::getSeo($this,array('title'=>Q::L('编辑新鲜事','Controller').' - '.$arrHomefresh['homefresh_title']));

		$this->assign('nDisplaySeccode',$GLOBALS['_option_']['seccode_publish_status']);
		$this->assign('arrCategorys',$GLOBALS['_cache_']['category']);
		$this->assign('arrHomefresh',$arrHomefresh);
		$this->display('homefresh+add1');
	}

}
