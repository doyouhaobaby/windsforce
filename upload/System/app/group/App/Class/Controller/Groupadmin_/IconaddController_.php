<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   保存小组图标设置控制器($$)*/

!defined('Q_PATH') && exit;

class Iconadd_C_Controller extends InitController{

	public function index(){
		// 获取参数
		$nId=intval(Q::G('gid'));
		$sGroupname=trim(Q::G('group_name'));

		// 判断小组是否存在
		$oGroup=Group_Extend::getGroup($nId,true,true);
		if(empty($oGroup['group_id'])){
			$this->E(Q::L('小组不存在或在审核中','Controller'));
		}

		require_once(Core_Extend::includeFile('function/Upload_Extend'));
		try{
			// 上传前删除早前的icon
			if(!empty($oGroup['group_icon'])){
				require_once(Core_Extend::includeFile('function/Upload_Extend'));
				Upload_Extend::deleteIcon('group',$oGroup['group_icon']);
		
				$oGroup->group_icon='';
				$oGroup->save('update');
				if($oGroup->isError()){
					$this->E($oGroup->getErrorMessage());
				}
			}

			// 执行上传
			$sPhotoDir=Upload_Extend::uploadIcon('group');
		
			$oGroup->group_icon=$sPhotoDir;
			$oGroup->save('update');
			if($oGroup->isError()){
				$this->E($oGroup->getErrorMessage());
			}
		
			$this->S(Q::L('图标设置成功','Controller'));
		}catch(Exception $e){
			$this->E($e->getMessage());
		}
	}

}
