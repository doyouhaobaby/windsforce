<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   新鲜事话题列表($$)*/

!defined('Q_PATH') && exit;

class Index_C_Controller extends InitController{

	public function index(){
		$arrWhere=array();

		// 类型
		$sType=trim(Q::G('type','G'));
		if(empty($sType)){
			$sType='';
		}
		if(!$sType){
			$arrWhere['user_id']=$GLOBALS['___login___']['user_id'];
		}

		// 动态列表
		$arrWhere['homefreshtag_status']=1;
		$nTotalRecord=Model::F_('homefreshtag')->where($arrWhere)
			->all()
			->getCounts();
		$oPage=Page::RUN($nTotalRecord,$GLOBALS['_cache_']['home_option']['homefreshtag_list_num']);
		$arrHomefreshtags=HomefreshtagModel::F()->where($arrWhere)
			->setColumns('homefreshtag_id,homefreshtag_name,homefreshtag_usercount,homefreshtag_homefreshcount,homefreshtag_totalcount,create_dateline,homefreshtag_username,user_id')
			->order('homefreshtag_id DESC')
			->limit($oPage->S(),$oPage->N())
			->getAll();

		Core_Extend::getSeo($this,array('title'=>Q::L('新鲜事话题','Controller')));

		$this->assign('arrHomefreshtags',$arrHomefreshtags);
		$this->assign('nTotalHomefreshtagnum',$nTotalRecord);
		$this->assign('sPageNavbar',$oPage->P(array('id'=>'pagination','style'=>'li','current'=>'active')));
		$this->assign('sType',$sType);
		$this->display('homefreshtag+index');
	}

}
