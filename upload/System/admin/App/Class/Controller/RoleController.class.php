<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   角色控制器($$)*/

!defined('Q_PATH') && exit;

class RoleController extends AController{

	public function init__(){
		parent::init__();

		if($GLOBALS['___login___']['user_id']!=1){
			$this->E(Q::L('只有用户ID为1的超级管理员才能够访问本页','Controller'));
		}
	}
	
	public function filter_(&$arrMap){
		$arrMap['A.role_name']=array('like',"%".Q::G('role_name')."%");
		
		$nRolegroupId=Q::G('rolegroup_id');
		if($nRolegroupId!==null && $nRolegroupId!=''){
			$arrMap['A.rolegroup_id']=$nRolegroupId;
		}
	}

	protected function sqljoin_(){
		return "->joinLeft('".Q::C('DB_PREFIX')."rolegroup AS B','B.*','A.rolegroup_id=B.rolegroup_id')";
	}

	public function bIndex_(){
		$this->getRolegroup();
	}
	
	public function bEdit_(){
		$this->check_appdevelop();
		
		$nId=intval(Q::G('id','G'));
		if($this->is_system_role($nId)){
			$this->E(Q::L('系统角色无法编辑','Controller'));
		}

		$this->getRolegroup();
	}

	public function bAdd_(){
		$this->check_appdevelop();
		$this->getRolegroup();
	}

	public function bForbid_(){
		$nId=intval(Q::G('id','G'));
		if($this->is_system_role($nId)){
			$this->E(Q::L('系统角色无法禁用','Controller'));
		}
	}

	public function check_rolename(){
		$sRoleName=trim(Q::G('role_name'));
		$nId=intval(Q::G('id'));

		if(!$sRoleName){
			exit('false');
		}

		// 查询条件
		$arrWhere=array();
		$arrWhere['role_name']=$sRoleName;
		if($nId){
			$arrWhere['role_id']=array('neq',$nId);
		}

		$oRole=RoleModel::F()->where($arrWhere)->setColumns('role_id')->getOne();
		if(empty($oRole['role_id'])){
			exit('true');
		}else{
			exit('false');
		}
	}
	
	public function getRolegroup(){
		$arrRolegroup=array_merge(array(array('rolegroup_id'=>0,'rolegroup_title'=>Q::L('未分组','Controller'))),
				RolegroupModel::F()->setColumns('rolegroup_id,rolegroup_title')->asArray()->all()->query()
		);
		$this->assign('arrRolegroup',$arrRolegroup);
	}

	public function bForeverdelete_deep_(){
		$this->bForeverdelete_();
	}

	public function bForeverdelete_(){
		$this->check_appdevelop();
		
		$sId=Q::G('id','G');
		$arrIds=explode(',',$sId);
		foreach($arrIds as $nId){
			if($this->is_system_role($nId)){
				$this->E(Q::L('系统角色无法删除','Controller'));
			}
			
			$nRoles=RoleModel::F('role_parentid=?',$nId)->all()->getCounts();
			$oRole=RoleModel::F('role_id=?',$nId)->query();
			if($nRoles>0){
				$this->E(Q::L('用户角色%s存在子分类，你无法删除','Controller',null,$oRole->role_name));
			}
		}
	}

	public function quickuserrole(){
		$nRoleid=intval(Q::G('rid'));

		// 取得系统所有角色信息
		$arrRoles=RoleModel::F()->order('create_dateline DESC')->getAll();

		$this->assign('arrRoles',$arrRoles);
		$this->assign('nRoleid',$nRoleid);
		$this->display();
	}

	public function do_quickuserrole(){
		$nRoleid=intval(Q::G('role_id'));
		$sUser=trim(Q::G('users'));

		$arrUsers=Q::normalize($sUser);
		if(empty($arrUsers)){
			$this->E(Q::L('你没有填写待授权的用户','Controller'));
		}

		// 取得待设置权限的用户
		$arrUserdata=array();
		if(is_array($arrUsers)){
			foreach($arrUsers as $value){
				$oUser=UserModel::F('user_name=? OR user_id=?',$value)->setColumns('user_id')->getOne();
				if(!empty($oUser['user_id'])){
					$arrUserdata[]=$oUser['user_id'];
				}
			}
		}

		$arrUserdata=array_unique($arrUserdata);

		// 开始授权
		if(is_array($arrUserdata)){
			foreach($arrUserdata as $nUserid){
				// 判断用户是否存在授权
				$oUserrole=UserroleModel::F('user_id=? AND role_id=?',$nUserid,$nRoleid)->getOne();
				if(empty($oUserrole['user_id'])){
					Q::instance('RoleModel')->setGroupUsers($nRoleid,array($nUserid));
				}
			}
		}

		$this->S(Q::L('用户授权成功','Controller'));
	}

	public function select(){
		$arrMap=array();
		$nParentid=Q::G('role_parentid');

		if($nParentid!=''){
			$arrMap['role_parentid']=$nParentid;
		}

		$arrList=RoleModel::F($arrMap)->setColumns('role_id,role_name')->all()->asArray()->query();
		$this->assign('arrList',$arrList);
		$this->display();
	}

	public function bSet_app_(){
		$nGroupId=Q::G('group_id');
		if(empty($nGroupId)){
			$this->E(Q::L('你没有选择分组','Controller'));
		}
	}

	public function set_app(){
		$nId=Q::G('groupAppId');

		$nGroupId=Q::G('group_id');
		$oRoleModel=RoleModel::F()->query();
		$oRoleModel->delGroupApp($nGroupId);
		$bResult=$oRoleModel->setGroupApps($nGroupId,$nId);
		if($bResult===false){
			$this->E(Q::L('项目授权失败','Controller'));
		}else{
			$this->S(Q::L('项目授权成功','Controller'));
		}
	}

	public function app(){
		$arrAppList=array();
		$arrList=NodeModel::F('node_level=?',1)->setColumns('node_id,node_title')->asArray()->all()->query();
		if(is_array($arrList)){
			foreach($arrList as $arrVo){
				if($arrVo['node_id']!=1){
					$arrAppList[$arrVo['node_id']]=$arrVo['node_title'];
				}
			}
		}

		$arrGroupList=array();
		$arrList=RoleModel::F()->setColumns('role_id,role_name')->asArray()->all()->query();
		if($arrList){
			foreach($arrList as $arrVo){
				$arrGroupList[$arrVo['role_id']]=$arrVo['role_name'];
			}
		}
		$this->assign("arrGroupList",$arrGroupList);

		$nGroupId=Q::G('group_id');
		if($nGroupId===null){
			$nGroupId=0;
		}
		$this->assign('nGroupId',$nGroupId);

		$arrGroupAppList=array();
		$this->assign("nSelectGroupId",$nGroupId);
		if(!empty($nGroupId)){
			$arrList=RoleModel::F()->query()->getGroupAppList($nGroupId);
			if(is_array($arrList)){
				foreach($arrList as $arrVo){
					$arrGroupAppList[]=	$arrVo['node_id'];
				}
			}
		}
		$this->assign('arrGroupAppList',$arrGroupAppList);
		$this->assign('arrAppList',$arrAppList);
		$this->display();
	}

	public function module(){
		$nGroupId=Q::G('group_id');
		$nAppId=Q::G('app_id');

		if($nGroupId===null){
			$nGroupId=0;
		}
		$this->assign('nGroupId',$nGroupId);

		$arrGroupList=array();
		$arrList=RoleModel::F()->setColumns('role_id,role_name')->all()->asArray()->query();
		if(is_array($arrList)){
			foreach($arrList as $arrVo){
				$arrGroupList[$arrVo['role_id']]=$arrVo['role_name'];
			}
		}
		$this->assign("arrGroupList",$arrGroupList);

		$arrAppList=array();
		$this->assign("nSelectGroupId",$nGroupId);
		if(!empty($nGroupId)){
			$arrList=RoleModel::F()->query()->getGroupAppList($nGroupId);
			if(is_array($arrList)){
				foreach($arrList as $arrVo){
					$arrAppList[$arrVo['node_id']]=	$arrVo['node_title'];
				}
			}
		}
		$this->assign("arrAppList",$arrAppList);

		$arrModuleList=array();
		$this->assign("nSelectAppId",$nAppId);
		if(!empty($nAppId)){
			$arrWhere['node_level']=2;
			$arrWhere['node_parentid']=$nAppId;
			$arrNodelist=NodeModel::F()->setColumns('node_id,node_title')->where($arrWhere)->asArray()->all()->query();
			if(is_array($arrNodelist)){
				foreach($arrNodelist as $arrVo){
					$arrModuleList[$arrVo['node_id']]=$arrVo['node_title'];
				}
			}
		}

		$arrGroupModuleList=array();
		if(!empty($nGroupId)&& !empty($nAppId)){
			$arrGrouplist=RoleModel::F()->query()->getGroupModuleList($nGroupId,$nAppId);
			if(is_array($arrGrouplist)){
				foreach($arrGrouplist as $arrVo){
					$arrGroupModuleList[]=$arrVo['node_id'];
				}
			}
		}
		$this->assign('arrGroupModuleList',$arrGroupModuleList);
		$this->assign('arrModuleList',$arrModuleList);
		$this->display();
	}

	public function bSet_module_(){
		$nGroupId=Q::G('group_id');
		$nAppId=Q::G('appId');

		if(empty($nGroupId)){
			$this->E(Q::L('你没有选择分组','Controller'));
		}

		if(empty($nAppId)){
			$this->E(Q::L('你没有选择APP','Controller'));
		}
	}

	public function set_module(){
		$nId=Q::G('groupModuleId');
		$nGroupId=Q::G('group_id');
		$nAppId=Q::G('appId');

		RoleModel::F()->query()->delGroupModule($nGroupId,$nAppId);
		$bResult=RoleModel::F()->query()->setGroupModules($nGroupId,$nId);
		if($bResult===false){
			$this->E(Q::L('模块授权失败','Controller'));
		}else{
			$this->S(Q::L('模块授权成功','Controller'));
		}
	}

	public function action(){
		$nGroupId=Q::G('group_id','G');
		$nAppId=Q::G('app_id','G');
		$nModuleId=Q::G('module_id','G');

		if($nGroupId===null){
			$nGroupId=0;
		}
		$this->assign('nGroupId',$nGroupId);

		if($nAppId===null){
			$nAppId=0;
		}
		$this->assign('nAppId',$nAppId);

		$arrGrouplist=RoleModel::F()->setColumns('role_id,role_name')->asArray()->all()->query();
		if(is_array($arrGrouplist)){
			foreach($arrGrouplist as $arrVo){
				$arrGroupList[$arrVo['role_id']]=$arrVo['role_name'];
			}
		}
		$this->assign("arrGroupList",$arrGroupList);
		$this->assign("nSelectGroupId",$nGroupId);

		$arrAppList=array();
		if(!empty($nGroupId)){
			$arrList=RoleModel::F()->query()->getGroupAppList($nGroupId);
			if($arrList){
				foreach($arrList as $arrVo){
					$arrAppList[$arrVo['node_id']]=	$arrVo['node_title'];
				}
			}
		}
		$this->assign("arrAppList",$arrAppList);
		$this->assign("nSelectAppId",$nAppId);

		$arrModuleList=array();
		if(!empty($nAppId)){
			$arrList=RoleModel::F()->query()->getGroupModuleList($nGroupId,$nAppId);
			if(is_array($arrList)){
				foreach($arrList as $arrVo){
					$arrModuleList[$arrVo['node_id']]=$arrVo['node_title'];
				}
			}
		}
		$this->assign("arrModuleList",$arrModuleList);
		$this->assign("nSelectModuleId",$nModuleId);

		$arrActionList=array();
		if(!empty($nModuleId)){
			$arrMap['node_level']=3;
			$arrMap['node_parentid']=$nModuleId;
			$arrList=NodeModel::F()->setColumns('node_id,node_title')->where($arrMap)->asArray()->all()->query();
			if($arrList){
				foreach($arrList as $arrVo){
					$arrActionList[$arrVo['node_id']]=$arrVo['node_title'];
				}
			}
		}
		$this->assign('arrActionList',$arrActionList);

		$arrGroupActionList=array();
		if(!empty($nModuleId) && !empty($nGroupId)){
			$arrGroupAction=RoleModel::F()->query()->getGroupActionList($nGroupId,$nModuleId);
			if($arrGroupAction){
				foreach($arrGroupAction as $arrVo){
					$arrGroupActionList[]=$arrVo['node_id'];
				}
			}
		}
		$this->assign('arrGroupActionList',$arrGroupActionList);
		$this->display();
	}

	public function bSet_action_(){
		$nGroupId=Q::G('group_id','P');
		$nAppId=Q::G('appId','P');

		if(empty($nGroupId)){
			$this->E(Q::L('你没有选择分组','Controller'));
		}

		if(empty($nAppId)){
			$this->E(Q::L('你没有选择APP','Controller'));
		}
	}

	public function set_action(){
		$nId=Q::G('groupActionId','P');
		$nModuleId=Q::G('moduleId','P');
		$nGroupId=Q::G('group_id','P');

		RoleModel::F()->query()->delGroupAction($nGroupId,$nModuleId);
		$bResult=RoleModel::F()->query()->setGroupActions($nGroupId,$nId);
		if($bResult===false){
			$this->E(Q::L('操作授权失败','Controller'));
		}else{
			$this->S(Q::L('操作授权成功','Controller'));
		}
	}

	public function user(){
		$nGroupId=Q::G('id');
		if($nGroupId==4){
			$this->E(Q::L('游客用户ID为-1，无法进行授权','Controller'));
		}
		
		$arrWhere=array();
		$arrWhere['user_name']=array('like','%'.Q::G('user_name').'%');

		$nTotalRecord=UserModel::F()->where($arrWhere)->all()->getCounts();
		$oPage=Page::RUN($nTotalRecord,$GLOBALS['_option_']['admin_list_num']);
		$arrList=UserModel::F()->setColumns('user_id,user_name,user_nikename')->order('user_id DESC')->asArray()->where($arrWhere)->all()->limit($oPage->S(),$oPage->N())->query();
		
		$arrUserList=array();
		foreach($arrList as $arrVo){
			$arrUserList[$arrVo['user_id']]=$arrVo['user_name'].' '.$arrVo['user_nikename'];
		}

		$arrList=RoleModel::F()->setColumns('role_id,role_name')->asArray()->all()->query();
		if(is_array($arrList)){
			foreach($arrList as $arrVo){
				$arrGroupList[$arrVo['role_id']]=$arrVo['role_name'];
			}
		}

		$arrGroupUserList=array();
		if(!empty($nGroupId)){
			$arrList=RoleModel::F()->query()->getGroupUserList($nGroupId);
			if(is_array($arrList)){
				foreach($arrList as $arrVo){
					$arrGroupUserList[]=$arrVo['user_id'];
				}
			}
		}
		$this->assign('arrGroupUserList',$arrGroupUserList);
		$this->assign('arrUserList',$arrUserList);
		$this->assign('sPageNavbar',$oPage->P());
		$this->assign("arrGroupList",$arrGroupList);
		$this->assign('nId',$nGroupId);
		$this->assign("nSelectGroupId",$nGroupId);
		$this->display();
	}

	public function bSet_user_(){
		$nGroupId=Q::G('group_id','P');
		if(empty($nGroupId)){
			$this->E(Q::L('授权失败','Controller'));
		}
	}

	public function set_user(){
		$arrId=Q::G('groupUserId','P');
		$nGroupId=Q::G('group_id','P');
		$arrThispageuser=Q::G('thispageuser','P');

		RoleModel::F()->query()->delGroupUser($nGroupId,$arrThispageuser);
		$bResult=RoleModel::F()->query()->setGroupUsers($nGroupId,$arrId);
		if($bResult===false){
			$this->E(Q::L('授权失败','Controller'));
		}else{
			$this->S(Q::L('授权成功','Controller'));
		}
	}

	public function get_parent_role($nParentRoleId){
		$oRole=Q::instance('RoleModel');
		return $oRole->getParentRole($nParentRoleId);
	}
	
	public function change_rolegroup(){
		$this->check_appdevelop();
		
		$sId=trim(Q::G('id','G'));
		$nRolegroupId=intval(Q::G('rolegroup_id','G'));
		
		if(!empty($sId)){
			if($nRolegroupId){
				// 判断角色分组是否存在
				$oRolegroup=RolegroupModel::F('rolegroup_id=?',$nRolegroupId)->getOne();
				if(empty($oRolegroup['rolegroup_id'])){
					$this->E(Q::L('你要移动的角色分组不存在','Controller'));
				}
			}
			
			$arrIds=explode(',', $sId);
			foreach($arrIds as $nId){
				if($this->is_system_role($nId)){
					$this->E(Q::L('系统角色无法移动','Controller'));
				}

				$oRole=RoleModel::F('role_id=?',$nId)->getOne();
				$oRole->rolegroup_id=$nRolegroupId;
				$oRole->save('update');
				if($oRole->isError()){
					$this->E($oRole->getErrorMessage());
				}
			}

			$this->S(Q::L('移动角色分组成功','Controller'));
		}else{
			$this->E(Q::L('操作项不存在','Controller'));
		}
	}

	public function is_system_role($nId){
		$nId=intval($nId);

		$oRole=RoleModel::F('role_id=?',$nId)->setColumns('role_id,role_issystem')->getOne();
		if(empty($oRole['role_id'])){
			return false;
		}

		if($oRole['role_issystem']==1){
			return true;
		}

		return false;
	}

}
