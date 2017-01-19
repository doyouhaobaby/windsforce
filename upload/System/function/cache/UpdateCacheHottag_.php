<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   热门话题缓存($$)*/

!defined('Q_PATH') && exit;

class UpdateCacheHottag{

	public static function cache(){
		$arrData=array();

		$nHomefreshucenterhottagnum=intval($GLOBALS['_cache_']['home_option']['homefresh_ucenterhottagnum']);
		$nDate=intval($GLOBALS['_cache_']['home_option']['home_hothomefreshtag_date']);
		if($nHomefreshucenterhottagnum<2){
			$nHomefreshucenterhottagnum=2;
		}
		if($nDate<3600){
			$nDate=3600;
		}

		$arrData=Model::F_('homefreshtag','homefreshtag_status=? AND create_dateline>?',1,(CURRENT_TIMESTAMP-$nDate))
			->setColumns('homefreshtag_id,homefreshtag_name,homefreshtag_totalcount')
			->order('homefreshtag_totalcount DESC')
			->limit(0,$nHomefreshucenterhottagnum)
			->getAll();

		Core_Extend::saveSyscache('hottag',$arrData);
	}

}
