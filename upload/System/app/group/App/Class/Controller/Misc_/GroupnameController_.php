<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   小组名字验证控制器($$)*/

!defined('Q_PATH') && exit;

class Groupname_C_Controller extends InitController{

	public function index(){
		$nGid=intval(Q::G('gid'));
		$sGroupName=trim(Q::G('group_name'));

		if(empty($sGroupName) || empty($nGid)){
			exit('false');
		}

		// 条件
		$arrWhere=array();
		$arrWhere['group_id']=array('neq',$nGid);
		$arrWhere['group_name']=$sGroupName;

		$arrGroup=Model::F_('group')->where($arrWhere)->setColumns('group_id')->getOne();
		if(!empty($arrGroup['group_id'])){
			exit('false');
		}else{
			exit('true');
		}
	}

}
