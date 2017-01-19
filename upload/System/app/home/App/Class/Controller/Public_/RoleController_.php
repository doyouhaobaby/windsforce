<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   系统角色($$)*/

!defined('Q_PATH') && exit;

class Role_C_Controller extends InitController{

	public function index(){
		$nGId=intval(Q::G('gid','G'));
		
		// 角色分组&&角色
		Core_Extend::loadCache('rolegroup');
		Core_Extend::loadCache('role');

		$arrRoles=array();
		if(!$nGId){
			$arrRoles=$GLOBALS['_cache_']['role'];
		}else{
			foreach($GLOBALS['_cache_']['role'] as $arrVal){
				if($arrVal['rolegroup_id']==$nGId){
					$arrRoles[]=$arrVal;
				}
			}
		}

		Core_Extend::getSeo($this,array('title'=>Q::L('系统角色','Controller')));

		$this->assign('arrRoles',$arrRoles);
		$this->assign('nGId',$nGId);
		$this->assign('arrRolegroups',$GLOBALS['_cache_']['rolegroup']);
		$this->display('public+role');
	}

}
