<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   帖子分类编辑控制器($$)*/

!defined('Q_PATH') && exit;

class Updatetopiccategory_C_Controller extends InitController{

	public function index(){
		// 获取参数
		$sId=trim(Q::G('gid'));
		$nGrouptopiccategoryid=intval(Q::G('cid'));

		// 判断小组是否存在
		$arrGroup=Group_Extend::getGroup($sId);
		if(empty($arrGroup['group_id'])){
			$this->E(Q::L('小组不存在或在审核中','Controller'));
		}
		
		$arrGrouptopiccategory=Model::F_('grouptopiccategory','grouptopiccategory_id=? AND group_id=?',$nGrouptopiccategoryid,$arrGroup['group_id'])->query();
		if(empty($arrGrouptopiccategory['grouptopiccategory_id'])){
			$this->E(Q::L('你编辑的帖子分类不存在','Controller'));
		}

		// 取得用户是否加入了小组
		$arrGroupuser=Group_Extend::getGroupuser($arrGroup['group_id']);

		Core_Extend::getSeo($this,array('title'=>Q::L('小组分类编辑','Controller').' - '.$arrGrouptopiccategory['grouptopiccategory_name']));

		$this->assign('arrGroup',$arrGroup);
		$this->assign('arrGrouptopiccategory',$arrGrouptopiccategory);
		$this->assign('arrGroupuser',$arrGroupuser);
		$this->display('groupadmin+updatetopiccategory');
	}

}