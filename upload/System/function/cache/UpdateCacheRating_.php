<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   等级缓存($$)*/

!defined('Q_PATH') && exit;

class UpdateCacheRating{

	public static function cache(){
		$arrData=array();

		$arrRatingDatas=Model::F_('rating')
			->order('rating_id ASC')
			->getAll();
		if(is_array($arrRatingDatas)){
			foreach($arrRatingDatas as &$arrRating){
				$arrRating['rating_originalname']=$arrRating['rating_name'];
				$arrRating['rating_name']=$arrRating['rating_nikename']?$arrRating['rating_nikename']:$arrRating['rating_name'];
				$arrRating['rating_icon']=__PUBLIC__.'/images/rating/'.$GLOBALS['_option_']['rating_icontype'].'/'.$arrRating['rating_icon'];
				$arrData[$arrRating['rating_id']]=$arrRating;
			}
		}
		
		Core_Extend::saveSyscache('rating',$arrData);
	}

}
