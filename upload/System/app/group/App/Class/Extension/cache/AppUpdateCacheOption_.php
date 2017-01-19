<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   group应用配置缓存($$)*/

!defined('Q_PATH') && exit;

class AppUpdateCacheOption{

	public static function cache(){
		$arrData=array();

		$arrOptionData=GroupoptionModel::F()->asArray()->all()->query();
		if(is_array($arrOptionData)){
			foreach($arrOptionData as $nKey=>$arrValue){
				$arrData[$arrValue['groupoption_name']]=$arrValue['groupoption_value'];
			}
		}

		Core_Extend::saveSyscache('group_option',$arrData);
	}

}
