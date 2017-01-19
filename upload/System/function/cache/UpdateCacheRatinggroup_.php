<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   等级分组缓存($$)*/

!defined('Q_PATH') && exit;

class UpdateCacheRatinggroup{

	public static function cache(){
		$arrData=array();
		
		$arrRatinggroupDatas=Model::F_('ratinggroup','ratinggroup_status=?',1)
			->order('ratinggroup_id ASC')
			->getAll();
		if(is_array($arrRatinggroupDatas)){
			foreach($arrRatinggroupDatas as $arrRatinggroup){
				$arrData[$arrRatinggroup['ratinggroup_id']]=$arrRatinggroup;
			}
		}
		
		Core_Extend::saveSyscache('ratinggroup',$arrData);
	}

}
