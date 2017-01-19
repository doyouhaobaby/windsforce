<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   群组入口控制器($$)*/

!defined('Q_PATH') && exit;

class GroupmainController extends AController{

	public function index($sModel=null,$bDisplay=true){
		$sType=trim(Q::G('type','G'));
		$nId=intval(Q::G('id','G'));

		$this->assign('nUploadMaxFilesize',ini_get('upload_max_filesize'));
		$this->assign('nUploadSize',Core_Extend::getUploadSize($GLOBALS['_cache_']['group_option']['group_icon_uploadfile_maxsize']));
		$this->assign('nUploadSizeTwo',Core_Extend::getUploadSize($GLOBALS['_cache_']['group_option']['group_headbg_uploadfile_maxsize']));
		$this->assign('nId',$nId);
		$this->assign('arrOptions',$GLOBALS['_cache_']['group_option']);
		$this->display(Admin_Extend::template('group','groupoption/'.($sType?$sType:'index')));
	}

	public function update_option(){
		$arrOptions=Q::G('options','P');

		if(isset($arrOptions['group_hottopic2_views'])){
			// 对火帖配置进行判断一下
			if($arrOptions['group_hottopic2_views']>$arrOptions['group_hottopic3_views'] || $arrOptions['group_hottopic2_views']<$arrOptions['group_hottopic1_views']){
				$this->E(Q::L('火帖浏览次数配置参数没有依次递增','__APPGROUP_COMMON_LANG__@Controller'));
			}

			if($arrOptions['group_hottopic2_comments']>$arrOptions['group_hottopic3_comments'] || $arrOptions['group_hottopic2_comments']<$arrOptions['group_hottopic1_comments']){
				$this->E(Q::L('火帖回帖次数配置参数没有依次递增','__APPGROUP_COMMON_LANG__@Controller'));
			}
		}

		foreach($arrOptions as $sKey=>$val){
			$val=trim($val);
			$oOptionModel=GroupoptionModel::F('groupoption_name=?',$sKey)->getOne();
			$oOptionModel->groupoption_value=C::html($val);
			$oOptionModel->save('update');
			if($oOptionModel->isError()){
				$this->E($oOptionModel->getErrorMessage());
			}
		}

		if(!Q::classExists('Cache_Extend')){
			require_once(Core_Extend::includeFile('function/Cache_Extend'));
		}
		Cache_Extend::updateCache('group_option');

		$this->S(Q::L('配置更新成功','__APPGROUP_COMMON_LANG__@Controller'));
	}

}
