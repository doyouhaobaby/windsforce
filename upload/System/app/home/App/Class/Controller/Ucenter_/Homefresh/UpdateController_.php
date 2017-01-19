<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   更新新鲜事($$)*/

!defined('Q_PATH') && exit;

class Update_C_Controller extends InitController{

	public function index(){
		$nId=intval(Q::G('homefresh_id'));
		if(empty($nId)){
			$this->E(Q::L('未指定新鲜事ID','Controller'));
		}

		$oHomefresh=HomefreshModel::F('homefresh_id=? AND homefresh_status=1',$nId)->getOne();
		if(empty($oHomefresh['homefresh_id'])){
			$this->E(Q::L('编辑的新鲜事不存在','Controller'));
		}

		if(!Home_Extend::checkHomefreshedit($oHomefresh)){
			$this->E(Q::L('你没有权限编辑新鲜事','Controller'));
		}

		if($GLOBALS['_option_']['seccode_publish_status']==1){
			$this->_oParent->check_seccode(true);
		}

		// 新鲜事需要审核
		if($GLOBALS['_cache_']['home_option']['homefresh_audit']==1 && !Core_Extend::isAdmin()){
			$oHomefresh->homefresh_status=0;
		}else{
			$oHomefresh->homefresh_status=1;
		}

		$oHomefresh->save('update');
		if($oHomefresh->isError()){
			$this->E($oHomefresh->getErrorMessage());
		}
		
		if($GLOBALS['_cache_']['home_option']['homefresh_audit']==1 && !Core_Extend::isAdmin()){
			$arrHomefreshData['url']=Q::U('home://public/index');
			$sSuccessMessage=Q::L('你更新的新鲜事需要通过审核才能够显示','Controller');
		}else{
			$arrHomefreshData['url']=Q::U('home://fresh@?id='.$oHomefresh['homefresh_id']);
			$sSuccessMessage=Q::L('更新新鲜事成功','Controller');
		}

		$this->A($arrHomefreshData,$sSuccessMessage,1);
	}

}
