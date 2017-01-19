<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   加入小组控制器($$)*/

!defined('Q_PATH') && exit;

class Joingroup_C_Controller extends InitController{

	public function index(){
		// 获取参数
		$nGid=intval(Q::G('gid','G'));
		if($GLOBALS['___login___']===false){
			$this->E(Q::L('加入小组需登录后才能进行','Controller'));
		}

		// 判断小组是否存在
		$arrGroup=Group_Extend::getGroup($nGid,true);
		if(empty($arrGroup['group_id'])){
			$this->E(Q::L('小组不存在或在审核中','Controller'));
		}

		// 判断是否已经加入了小组
		$arrCondition=array('group_id'=>$nGid,'user_id'=>$GLOBALS['___login___']['user_id']);
		$oGroupuser=GroupuserModel::F($arrCondition)->getOne();
		if(!empty($oGroupuser['user_id'])){
			$this->E(Q::L('你已是该小组成员','Controller'));
		}

		// 判断小组是否关闭了加入会员
		if($arrGroup['group_joinway']==1){
			$this->E(Q::L('该小组目前禁止任何人加入','Controller'));
		}

		// 保存数据
		$oGroupuser=new GroupuserModel();
		$oGroupuser->user_id=$GLOBALS['___login___']['user_id'];
		$oGroupuser->group_id=$nGid;
		$oGroupuser->save();
		if($oGroupuser->isError()){
			$this->E($oGroupuser->getErrorMessage());
		}

		// 更新小组中的用户数量
		Q::instance('GroupModel')->resetUser($nGid);

		$this->S(Q::L('恭喜你，成功加入 %s 小组','Controller',null,$arrGroup['group_nikename']));
	}
}