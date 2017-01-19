<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   更新小组分类设置控制器($$)*/

!defined('Q_PATH') && exit;

class Categoryupdate_C_Controller extends InitController{

	public function index(){
		// 获取参数
		$nId=trim(Q::G('gid'));
		$nGroupcategoryId=intval(Q::G('groupcategory_id'));

		// 判断小组是否存在
		$oGroup=Group_Extend::getGroup($nId,true,true);
		if(empty($oGroup['group_id'])){
			$this->E(Q::L('小组不存在或在审核中','Controller'));
		}

		// 更新对应分类
		$arrNeedUpdate=array();
		if($oGroup['groupcategory_id']>0){
			$arrNeedUpdate[]=$oGroup['groupcategory_id'];
		}

		if($nGroupcategoryId>0){
			$arrNeedUpdate[]=$nGroupcategoryId;
		}

		$oGroup->save('update');
		if($oGroup->isError()){
			$this->E($oGroup->getErrorMessage());
		}

		// 更新分类统计
		foreach($arrNeedUpdate as $nId){
			$oGroup->afterUpdate($nId);
		}

		$this->S(Q::L('小组设置已经更新','Controller'));
	}

}
