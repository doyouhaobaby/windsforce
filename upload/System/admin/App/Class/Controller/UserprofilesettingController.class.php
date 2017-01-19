<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   用户栏目控制器($$)*/

!defined('Q_PATH') && exit;

class UserprofilesettingController extends AController{

	public function init__(){
		parent::init__();

		if($GLOBALS['___login___']['user_id']!=1){
			$this->E(Q::L('只有用户ID为1的超级管理员才能够访问本页','Controller'));
		}
	}
	
	public function filter_(&$arrMap){
		$arrMap['A.userprofilesetting_id']=array('like',"%".Q::G('userprofilesetting_id')."%");
		$arrMap['A.userprofilesetting_title']=array('like',"%".Q::G('userprofilesetting_title')."%");
	}

	public function check_title(){
		$sUserprofilesettingTitle=trim(Q::G('userprofilesetting_title'));
		$nId=trim(Q::G('id'));

		if(!$sUserprofilesettingTitle){
			exit('false');
		}

		// 查询条件
		$arrWhere=array();
		$arrWhere['userprofilesetting_title']=$sUserprofilesettingTitle;
		if($nId){
			$arrWhere['userprofilesetting_id']=array('neq',$nId);
		}

		$oUserprofilesetting=UserprofilesettingModel::F()->where($arrWhere)->setColumns('userprofilesetting_id')->getOne();
		if(empty($oUserprofilesetting['userprofilesetting_id'])){
			exit('true');
		}else{
			exit('false');
		}
	}

	public function bForeverdelete_deep_(){
		$this->bForeverdelete_();
	}

	public function bForeverdelete_(){
		$sId=Q::G('id','G');

		$arrIds=explode(',',$sId);
		foreach($arrIds as $nId){
			if($this->is_system_userprofilesetting($nId)){
				$this->E(Q::L('系统用户栏目无法删除','Controller'));
			}
		}
	}
	
	protected function aInsert($nId=null){
		if(!Q::classExists('Cache_Extend')){
			require_once(Core_Extend::includeFile('function/Cache_Extend'));
		}
		Cache_Extend::updateCache('userprofilesetting');
	}

	protected function aUpdate($nId=null){
		$this->aInsert();
	}

	public function aForeverdelete($sId){
		$this->aInsert();
	}

	protected function aForbid(){
		$this->aInsert();
	}

	protected function aResume($nId=null){
		$this->aInsert();
	}

	public function is_system_userprofilesetting($nId){
		$nId=intval($nId);

		$oUserprofilesetting=UserprofilesettingModel::F('userprofilesetting_id=?',$nId)->setColumns('userprofilesetting_id,userprofilesetting_issystem')->getOne();
		if(empty($oUserprofilesetting['userprofilesetting_id'])){
			return false;
		}

		if($oUserprofilesetting['userprofilesetting_issystem']==1){
			return true;
		}

		return false;
	}

}
