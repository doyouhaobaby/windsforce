<?php
/* [WindsForce!] (C)WindsForce TEAM Since 2012.03.17.
   节点分组控制器($$)*/

!defined('Q_PATH') && exit;

class NodegroupController extends AController{

	public function init__(){
		parent::init__();

		if($GLOBALS['___login___']['user_id']!=1){
			$this->E(Q::L('只有用户ID为1的超级管理员才能够访问本页','Controller'));
		}
	}
	
	public function filter_(&$arrMap){
		$arrMap['A.nodegroup_name']=array('like',"%".Q::G('nodegroup_name')."%");
		$arrMap['A.nodegroup_title']=array('like',"%".Q::G('nodegroup_title')."%");

		// 添加时间
		$this->getTime_('A.create_dateline',$arrMap);
	}

	public function aInsert($nId=null){
		$this->clear_menu_cache();
	}

	public function aUpdate($nId=null){
		$this->clear_menu_cache();
	}

	public function bForeverdelete_deep_(){
		$this->bForeverdelete_();
	}

	public function bForeverdelete_(){
		$this->check_appdevelop();
		$sId=Q::G('id','G');
		$arrIds=explode(',',$sId);
		foreach($arrIds as $nId){
			if($this->is_system_nodegroup($nId)){
				$this->E(Q::L('系统节点分组无法删除','Controller'));
			}
		}
	}

	public function bEdit_(){
		$this->check_appdevelop();
		$nId=intval(Q::G('id','G'));
		if($this->is_system_nodegroup($nId)){
			$this->E(Q::L('系统节点分组无法编辑','Controller'));
		}
	}

	public function aForeverdelete_deep($sId){
		$this->aForeverdelete($sId);
	}

	public function aForeverdelete($sId){
		$this->clear_menu_cache();
	}

	public function afterInputChangeAjax($sName=null){
		$this->clear_menu_cache();
	}

	public function clear_menu_cache(){}

	public function sort(){
		$this->check_appdevelop();
		
		$nSortId=Q::G('sort_id','G');
		if(!empty($nSortId)){
			$arrMap['nodegroup_status']=1;
			$arrMap['nodegroup_id']=array('in',$nSortId);
			$arrSortList=NodegroupModel::F()->order('nodegroup_sort ASC')->where($arrMap)->all()->query();
		}else{
			$arrSortList=NodegroupModel::F()->order('nodegroup_sort ASC')->all()->query();
		}

		$this->assign("arrSortList",$arrSortList);
		$this->display();
	}

	public function check_nodegroupname(){
		$sNodegroupName=trim(Q::G('nodegroup_name'));
		$nId=intval(Q::G('id'));

		if(!$sNodegroupName){
			exit('false');
		}

		// 查询条件
		$arrWhere=array();
		$arrWhere['nodegroup_name']=$sNodegroupName;
		if($nId){
			$arrWhere['nodegroup_id']=array('neq',$nId);
		}

		$oNodegroup=NodegroupModel::F()->where($arrWhere)->setColumns('nodegroup_id')->getOne();
		if(empty($oNodegroup['nodegroup_id'])){
			exit('true');
		}else{
			exit('false');
		}
	}

	public function is_system_nodegroup($nId){
		$nId=intval($nId);
		$oNodegroup=NodegroupModel::F('nodegroup_id=?',$nId)->setColumns('nodegroup_id,nodegroup_issystem')->getOne();
		if(empty($oNodegroup['nodegroup_id'])){
			return false;
		}

		if($oNodegroup['nodegroup_issystem']==1){
			return true;
		}

		return false;
	}

}
