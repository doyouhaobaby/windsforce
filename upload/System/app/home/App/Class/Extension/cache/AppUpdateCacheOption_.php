<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   home应用配置缓存($$)*/

!defined('Q_PATH') && exit;

class AppUpdateCacheOption{

	public static function cache(){
		$arrData=array();

		$arrOptionData=HomeoptionModel::F()->asArray()->all()->query();
		if(is_array($arrOptionData)){
			foreach($arrOptionData as $nKey=>$arrValue){
				$arrData[$arrValue['homeoption_name']]=$arrValue['homeoption_value'];
			}
		}

		Core_Extend::saveSyscache('home_option',$arrData);
	}

}
