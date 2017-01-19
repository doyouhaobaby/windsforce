<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   小组分类设置控制器($$)*/

!defined('Q_PATH') && exit;

class Category_C_Controller extends InitController{

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
		
		// 小组分类
		Core_Extend::loadCache('group_category');

		Core_Extend::getSeo($this,array('title'=>Q::L('小组分类设置','Controller').' - '.$arrGroup['group_nikename']));

		$this->assign('arrGroup',$arrGroup);
		$this->assign('arrGroupuser',$arrGroupuser);
		$this->assign('arrGroupCategorys',$GLOBALS['_cache_']['group_category']);
		$this->display('groupadmin+category');
	}

}
