<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   离开小组控制器($$)*/

!defined('Q_PATH') && exit;

class Leavegroup_C_Controller extends InitController{

	public function index(){
		// 获取参数
		$nGid=intval(Q::G('gid','G'));
		if($GLOBALS['___login___']===false){
			$this->E(Q::L('退出小组需登录后才能进行','Controller'));
		}
		
		// 判断小组是否存在
		$arrGroup=Group_Extend::getGroup($nGid,true);
		if(empty($arrGroup['group_id'])){
			$this->E(Q::L('小组不存在或在审核中','Controller'));
		}

		// 查询用户是否为小组用户
		$arrCondition=array('group_id'=>$nGid,'user_id'=>$GLOBALS['___login___']['user_id']);
		$oGroupuser=GroupuserModel::F($arrCondition)->getOne();
		if(empty($oGroupuser['user_id'])){
			$this->E(Q::L('你尚未加入该小组','Controller'));
		}

		GroupuserModel::M()->deleteWhere($arrCondition);

		// 更新小组中的用户数量
		Q::instance('GroupModel')->resetUser($nGid);
		Group_Extend::chearGroupuserrole($GLOBALS['___login___']['user_id']);

		$this->S(Q::L('成功退出 %s 小组','Controller',null,$arrGroup['group_nikename']));
	}

}