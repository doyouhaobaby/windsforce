<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   小组帖子分类设置控制器($$)*/

!defined('Q_PATH') && exit;

class Topiccategory_C_Controller extends InitController{

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

		// 取得小组帖子分类
		$arrGrouptopiccategorys=Model::F_('grouptopiccategory','group_id=?',$arrGroup['group_id'])
			->setColumns('grouptopiccategory_id,grouptopiccategory_name,grouptopiccategory_topicnum,group_id,grouptopiccategory_sort')
			->order('grouptopiccategory_sort ASC')
			->getAll();

		Core_Extend::getSeo($this,array('title'=>Q::L('小组帖子分类设置','Controller').' - '.$arrGroup['group_nikename']));

		$this->assign('arrGrouptopiccategorys',$arrGrouptopiccategorys);
		$this->assign('arrGroup',$arrGroup);
		$this->assign('arrGroupuser',$arrGroupuser);
		$this->display('groupadmin+topiccategory');
	}

}
