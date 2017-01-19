<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   小组用户删除控制器($$)*/

!defined('Q_PATH') && exit;

class Userdelete_C_Controller extends InitController{

	public function index(){
		// 获取参数
		$nGroupid=intval(Q::G('gid'));
		$nUserid=intval(Q::G('uid'));

		// 判断小组是否存在
		$arrGroup=Group_Extend::getGroup($nGroupid,true);
		if(empty($arrGroup['group_id'])){
			$this->E(Q::L('小组不存在','Controller'));
		}

		$arrUser=Model::F_('user','user_id=?',$nUserid)->setColumns('user_id')->getOne();
		if(empty($arrUser['user_id'])){
			$this->E(Q::L('用户不存在','Controller'));
		}

		// 删除前判断被删除用户是否为小组长，如果为小组长则不能够被删除
		$oTrygropuuser=GroupuserModel::F('user_id=? AND group_id=? AND groupuser_isadmin=2',$nUserid,$nGroupid)->getOne();
		if(!empty($oTrygropuuser['groupuser_id'])){
			$this->E(Q::L('你不能够删除小组长','Controller'));
		}
		
		// 执行删除
		$oGroupuserMeta=GroupuserModel::M();
		$oGroupuserMeta->deleteWhere(array('group_id'=>$nGroupid,'user_id'=>$nUserid));
		if($oGroupuserMeta->isError()){
			$this->E($oGroupuserMeta->getErrorMessage());
		}

		Group_Extend::chearGroupuserrole($nUserid);

		// 更新小组中的用户数量
		Q::instance('GroupModel')->resetUser($nGroupid);

		$this->S(Q::L('用户删除成功','Controller'));
	}

}