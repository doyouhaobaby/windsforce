<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   配置缓存($$)*/

!defined('Q_PATH') && exit;

class UpdateCacheOption{

	public static function cache(){
		$arrData=array();

		$arrOptionData=Model::F_('option')->getAll();
		if(is_array($arrOptionData)){
			foreach($arrOptionData as $nKey=>$arrValue){
				$arrData[$arrValue['option_name']]=$arrValue['option_value'];
			}
		}

		Core_Extend::saveSyscache('option',$arrData);
	}

}
