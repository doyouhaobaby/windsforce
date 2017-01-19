<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   删除小组头部背景设置控制器($$)*/

!defined('Q_PATH') && exit;

class Headerbgdelete_C_Controller extends InitController{

	public function index(){
		// 获取参数
		$nId=intval(Q::G('gid'));
		$sGroupname=trim(Q::G('group_name'));

		// 判断小组是否存在
		$oGroup=Group_Extend::getGroup($nId,true,true);
		if(empty($oGroup['group_id'])){
			$this->E(Q::L('小组不存在或在审核中','Controller'));
		}

		if(!empty($oGroup['group_headerbg'])){
			if(!Core_Extend::isPostInt($oGroup['group_headerbg'])){
				require_once(Core_Extend::includeFile('function/Upload_Extend'));
				Upload_Extend::deleteIcon('group',$oGroup['group_headerbg']);
			}
		
			$oGroup->group_headerbg='';
			$oGroup->save('update');
			if($oGroup->isError()){
				$this->E($oGroup->getErrorMessage());
			}
			
			$this->S(Q::L('小组背景删除成功','Controller'));
		}else{
			$this->E(Q::L('小组背景不存在','Controller'));
		}
	}

}
