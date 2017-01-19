<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   小组管理员更新控制器($$)*/

!defined('Q_PATH') && exit;

class Adminsupdate_C_Controller extends InitController{

	public function index(){
		$nGroupid=intval(Q::G('gid'));

		// 判断小组是否存在
		$arrGroup=Group_Extend::getGroup($nGroupid);
		if(empty($arrGroup['group_id'])){
			$this->E(Q::L('小组不存在或在审核中','Controller'));
		}
		
		// 设置完毕后系统统一进行清理
		$arrMaychangeuserid=array();
		
		// 保存管理员
		$sAdminUid=trim(Q::G('admin_userid','P'));
		$arrAdminUserid=explode(',',$sAdminUid);
		$arrAdminUserid=Q::normalize($arrAdminUserid,',',false);

		// 保存前清除旧的用户 && 清除前取得可能变更权限的用户id
		$arrGroupusers=GroupuserModel::F()->where(array('group_id'=>$nGroupid,'groupuser_isadmin'=>3))->getAll();
		if(is_array($arrGroupusers)){
			foreach($arrGroupusers as $oGroupuser){
				$arrMaychangeuserid[]=$oGroupuser['user_id'];
			}
		}

		$oGroupuserMeta=GroupuserModel::M();
		$oGroupuserMeta->deleteWhere(array('group_id'=>$nGroupid,'groupuser_isadmin'=>1));
		if($oGroupuserMeta->isError()){
			$this->E($oGroupuserMeta->getErrorMessage());
		}
		
		if(!empty($arrAdminUserid)){
			foreach($arrAdminUserid as $nAdminUserid){
				$oUser=UserModel::F('user_id=? AND user_status=1',$nAdminUserid)->getOne();
				if(empty($oUser['user_id'])){
					$this->E(Q::L('用户不存在或者被禁用','Controller'));
				}

				$arrMaychangeuserid[]=$nAdminUserid;
				
				$oGroupuser=new GroupuserModel();
				$oGroupuser->user_id=$nAdminUserid;
				$oGroupuser->group_id=$nGroupid;
				$oGroupuser->groupuser_isadmin=1;
				$oGroupuser->save();
				if($oGroupuser->isError()){
					$this->E($oGroupuser->getErrorMessage());
				}

				// 用户被设置为小组管理员后，判断是否拥有管理员角色
				$oUserrole=UserroleModel::F('user_id=? AND role_id=3',$nAdminUserid)->getOne();
				if(empty($oUserrole['user_id'])){
					Q::instance('RoleModel')->setGroupUsers(3,array($nAdminUserid));
				}

				// 更新小组中的用户数量
				Q::instance('GroupModel')->resetUser($nGroupid);
			}
		}

		// 设置成功最后一步进行数据清理
		if($arrMaychangeuserid){
			foreach($arrMaychangeuserid as $nMaychangeuserid){
				Group_Extend::chearGroupuserrole($nMaychangeuserid);
			}
		}

		$this->S(Q::L('小组管理员设置成功','Controller'));
	}

}
