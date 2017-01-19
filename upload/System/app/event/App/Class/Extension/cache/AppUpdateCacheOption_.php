<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   活动App配置缓存($$)*/

!defined('Q_PATH') && exit;

class AppUpdateCacheOption{

	public static function cache(){
		$arrData=array();

		$arrOptionData=EventoptionModel::F()->asArray()->all()->query();
		if(is_array($arrOptionData)){
			foreach($arrOptionData as $nKey=>$arrValue){
				$arrData[$arrValue['eventoption_name']]=$arrValue['eventoption_value'];
			}
		}

		Core_Extend::saveSyscache('event_option',$arrData);
	}

}
