<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   保存小组设置控制器($$)*/

!defined('Q_PATH') && exit;

class Save_C_Controller extends InitController{

	public function index(){
		// 获取参数
		$nId=intval(Q::G('gid'));
		$sGroupname=trim(Q::G('group_name'));

		// 判断小组是否存在
		$oGroup=Group_Extend::getGroup($nId,true,true);
		if(empty($oGroup['group_id'])){
			$this->E(Q::L('小组不存在或在审核中','Controller'));
		}
		if($oGroup['group_name'] && $sGroupname){
			$this->E(Q::L('小组已经设置过了，你无法修改','Controller'));
		}
		
		$oGroup->save('update');
		if($oGroup->isError()){
			$this->E($oGroup->getErrorMessage());
		}

		$this->S(Q::L('小组设置已经更新','Controller'));
	}

}
