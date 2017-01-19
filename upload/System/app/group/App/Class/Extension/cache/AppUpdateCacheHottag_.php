<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   小组热门标签缓存($$)*/

!defined('Q_PATH') && exit;

class AppUpdateCacheHottag{

	public static function cache(){
		$arrData=self::hottag_();
		Core_Extend::saveSyscache('group_hottag',$arrData);
	}

	protected static function hottag_($nNum=0,$nDate=0){
		// 热门标签时间
		if($nDate==0){
			$nDate=$GLOBALS['_cache_']['group_option']['group_hottag_date'];
			if($nDate<3600){
				$nDate=3600;
			}
		}

		// 热门标签数量
		if($nNum==0){
			$nNum=$GLOBALS['_cache_']['group_option']['group_hottag_num'];
			if($nNum<2){
				$nNum=2;
			}
		}
		
		return Model::F_('grouptopictag','create_dateline>?',CURRENT_TIMESTAMP-$nDate)
			->order('grouptopictag_count DESC')
			->limit(0,$nNum)
			->getAll();
	}

}
