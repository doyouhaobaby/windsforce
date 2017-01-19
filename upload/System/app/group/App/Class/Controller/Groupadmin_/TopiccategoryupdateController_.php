<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   帖子分类编辑保存控制器($$)*/

!defined('Q_PATH') && exit;

class Topiccategoryupdate_C_Controller extends InitController{

	public function index(){
		// 获取参数
		$sId=trim(Q::G('gid'));
		$nGrouptopiccategoryid=intval(Q::G('cid'));

		// 判断小组是否存在
		$arrGroup=Group_Extend::getGroup($sId);
		if(empty($arrGroup['group_id'])){
			$this->E(Q::L('小组不存在或在审核中','Controller'));
		}
		
		$oGrouptopiccategory=GrouptopiccategoryModel::F('grouptopiccategory_id=? AND group_id=?',$nGrouptopiccategoryid,$arrGroup['group_id'])->query();
		if(empty($oGrouptopiccategory['grouptopiccategory_id'])){
			$this->E(Q::L('你编辑的帖子分类不存在','Controller'));
		}

		$oGrouptopiccategory->save('update');
		if($oGrouptopiccategory->isError()){
			$this->E($oGrouptopiccategory->getErrorMessage());
		}else{
			$this->assign('__JumpUrl__',Q::U('group://groupadmin/topiccategory?gid='.$arrGroup['group_id']));
			$this->S(Q::L('帖子分类更新成功','Controller'));
		}
	}

}