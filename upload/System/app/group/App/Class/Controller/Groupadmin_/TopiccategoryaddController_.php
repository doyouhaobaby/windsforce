<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   添加小组帖子分类设置控制器($$)*/

!defined('Q_PATH') && exit;

class Topiccategoryadd_C_Controller extends InitController{

	public function index(){
		// 获取参数
		$sId=trim(Q::G('gid'));

		// 判断小组是否存在
		$arrGroup=Group_Extend::getGroup($sId);
		if(empty($arrGroup['group_id'])){
			$this->E(Q::L('小组不存在或在审核中','Controller'));
		}

		// 保存分类
		$oGrouptopiccategory=Q::instance('GrouptopiccategoryModel');
		$oGrouptopiccategory->insertGroupcategory($arrGroup['group_id']);
		if($oGrouptopiccategory->isError()){
			$this->E($oGrouptopiccategory->getErrorMessage());
		}else{
			$this->S(Q::L('添加帖子分类成功','Controller'));
		}
	}

}
