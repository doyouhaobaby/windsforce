<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   角色分组控制器($$)*/

!defined('Q_PATH') && exit;

class RolegroupController extends AController{

	public function init__(){
		parent::init__();

		if($GLOBALS['___login___']['user_id']!=1){
			$this->E(Q::L('只有用户ID为1的超级管理员才能够访问本页','Controller'));
		}
	}
	
	public function filter_(&$arrMap){
		$arrMap['A.rolegroup_name']=array('like',"%".Q::G('rolegroup_name')."%");
		$arrMap['A.rolegroup_title']=array('like',"%".Q::G('rolegroup_title')."%");

		// 添加时间
		$this->getTime_('A.create_dateline',$arrMap);
	}

	public function bEdit_(){
		$this->check_appdevelop();
		$nId=intval(Q::G('id','G'));

		if($this->is_system_rolegroup($nId)){
			$this->E(Q::L('系统角色分类无法编辑','Controller'));
		}
	}

	public function check_rolegroupname(){
		$sRolegroupName=trim(Q::G('rolegroup_name'));
		$nId=intval(Q::G('id'));

		if(!$sRolegroupName){
			exit('false');
		}

		// 查询条件
		$arrWhere=array();
		$arrWhere['rolegroup_name']=$sRolegroupName;
		if($nId){
			$arrWhere['rolegroup_id']=array('neq',$nId);
		}

		$oRolegroup=RolegroupModel::F()->where($arrWhere)->setColumns('rolegroup_id')->getOne();
		if(empty($oRolegroup['rolegroup_id'])){
			exit('true');
		}else{
			exit('false');
		}
	}

	public function bForbid_(){
		$this->check_appdevelop();
		$nId=intval(Q::G('id','G'));
		if($this->is_system_rolegroup($nId)){
			$this->E(Q::L('系统角色分组无法禁用','Controller'));
		}
	}

	public function bForeverdelete_deep_(){
		$this->bForeverdelete_();
	}

	public function bForeverdelete_(){
		$this->check_appdevelop();
		$sId=Q::G('id','G');

		$arrIds=explode(',',$sId);
		foreach($arrIds as $nId){
			if($this->is_system_rolegroup($nId)){
				$this->E(Q::L('系统角色分组无法删除','Controller'));
			}
		}
	}

	public function is_system_rolegroup($nId){
		$nId=intval($nId);

		$oRolegroup=RolegroupModel::F('rolegroup_id=?',$nId)->setColumns('rolegroup_id,rolegroup_issystem')->getOne();
		if(empty($oRolegroup['rolegroup_id'])){
			return false;
		}

		if($oRolegroup['rolegroup_issystem']==1){
			return true;
		}

		return false;
	}

}
