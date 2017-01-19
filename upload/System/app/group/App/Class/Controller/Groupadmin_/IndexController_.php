<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   小组设置控制器($$)*/

!defined('Q_PATH') && exit;

class Index_C_Controller extends InitController{

	public function index(){
		// 获取参数
		$sId=trim(Q::G('gid','G'));

		// 判断小组是否存在
		$arrGroup=Group_Extend::getGroup($sId);
		if(empty($arrGroup['group_id'])){
			$this->E(Q::L('小组不存在或在审核中','Controller'));
		}

		// 取得用户是否加入了小组
		$arrGroupuser=Group_Extend::getGroupuser($arrGroup['group_id']);

		Core_Extend::getSeo($this,array('title'=>Q::L('基本设置','Controller').' - '.$arrGroup['group_nikename']));
		
		$this->assign('arrGroup',$arrGroup);
		$this->assign('arrGroupuser',$arrGroupuser);
		$this->display('groupadmin+index');
	}

}
