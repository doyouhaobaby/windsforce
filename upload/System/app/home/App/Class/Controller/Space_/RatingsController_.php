<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   等级规则($$)*/

!defined('Q_PATH') && exit;

class Ratings_C_Controller extends InitController{

	public function index(){
		Core_Extend::loadCache('rating');
		Core_Extend::loadCache('ratinggroup');

		// 等级分组
		$nCId=intval(Q::G('cid','G'));
		$arrRatinggroups=$GLOBALS['_cache_']['ratinggroup'];

		$arrRatinggroupIds=array();
		foreach($arrRatinggroups as $oRatinggroup){
			$arrRatinggroupIds[]=$oRatinggroup['ratinggroup_id'];
		}

		if(!empty($nCId) && in_array($nCId,$arrRatinggroupIds)){
			$arrRatings=array();
			foreach($GLOBALS['_cache_']['rating'] as $arrRating){
				if($arrRating['ratinggroup_id']==$nCId){
					$arrRatings[]=$arrRating;
				}
			}
		}else{
			$arrRatings=$GLOBALS['_cache_']['rating'];
		}

		Core_Extend::getSeo($this,array('title'=>Q::L('系统等级','Controller')));

		$this->assign('nCId',$nCId);
		$this->assign('arrRatings',$arrRatings);
		$this->assign('arrRatinggroups',$arrRatinggroups);
		$this->display('space+ratings');
	}

}
