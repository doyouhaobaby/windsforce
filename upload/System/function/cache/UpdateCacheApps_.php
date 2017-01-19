<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   应用列表缓存($$)*/

!defined('Q_PATH') && exit;

class UpdateCacheApps{

	public static function cache(){
		$arrData=array();

		$arrApps=Model::F_('app','app_status=?',1)
			->setColumns('app_identifier,app_name,app_description')
			->order('app_id DESC')
			->getAll();
		foreach($arrApps as $arrApp){
			if(is_file(WINDSFORCE_PATH.'/System/app/'.$arrApp['app_identifier'].'/App/Class/Controller/UcenterController.class.php')){
				$arrData[$arrApp['app_identifier']]=$arrApp;
				$arrData[$arrApp['app_identifier']]['logo']=is_file(WINDSFORCE_PATH.'/System/app/'.$arrApp['app_identifier'].'/logo.png')?$arrApp['app_identifier'].'/logo.png':'logo.png';
			}
		}

		Core_Extend::saveSyscache('apps',$arrData);
	}

}
