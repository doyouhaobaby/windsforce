<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   保存小组头部背景设置控制器($$)*/

!defined('Q_PATH') && exit;

class Headerbgadd_C_Controller extends InitController{

	public function index(){
		// 获取参数
		$nId=intval(Q::G('gid'));
		$sGroupname=trim(Q::G('group_name'));

		// 判断小组是否存在
		$oGroup=Group_Extend::getGroup($nId,true,true);
		if(empty($oGroup['group_id'])){
			$this->E(Q::L('小组不存在或在审核中','Controller'));
		}

		if($_FILES['headerbg']['error'][0]=='4'){
			if(isset($_POST['group_headerbg'])){
				$oGroup->group_headerbg=intval($_POST['group_headerbg']);
				$oGroup->save('update');
				if($oGroup->isError()){
					$this->E($oGroup->getErrorMessage());
				}
			}
		}else{
			require_once(Core_Extend::includeFile('function/Upload_Extend'));

			try{
				// 上传前删除早前的icon
				if(!empty($oGroup['group_headerbg']) && !Core_Extend::isPostInt($oGroup['group_headerbg'])){
					require_once(Core_Extend::includeFile('function/Upload_Extend'));
					Upload_Extend::deleteIcon('group',$oGroup['group_headerbg']);
			
					$oGroup->group_headerbg='';
					$oGroup->save('update');
					if($oGroup->isError()){
						$this->E($oGroup->getErrorMessage());
					}
				}

				// 执行上传
				$sPhotoDir=Upload_Extend::uploadIcon('group',array('width'=>940,'height'=>150,'uploadfile_maxsize'=>$GLOBALS['_cache_']['group_option']['group_headbg_uploadfile_maxsize'],'upload_saverule'=>array('Group_Extend','getHeaderbgName')));
			
				$oGroup->group_headerbg=$sPhotoDir;
				$oGroup->save('update');
				if($oGroup->isError()){
					$this->E($oGroup->getErrorMessage());
				}
			}catch(Exception $e){
				$this->E($e->getMessage());
			}
		}

		$this->S(Q::L('群组背景设置成功','Controller'));
	}

}
