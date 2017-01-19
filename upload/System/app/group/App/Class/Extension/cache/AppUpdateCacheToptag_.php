<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   小组排行榜热门标签缓存($$)*/

!defined('Q_PATH') && exit;

class AppUpdateCacheToptag{

	public static function cache(){
		$arrData=array();
		
		// 获取排行数据
		$arrData['hour']=self::hottag_(0,'3600');// 一小时排行
		$arrData['today']=self::hottag_(0,'86400');// 今日排行
		$arrData['week']=self::hottag_(0,'604800');// 本周排行
		$arrData['month']=self::hottag_(0,'2592000');// 当月排行
		$arrData['year']=self::hottag_(0,'31536000');// 年度排行
		$arrData['total']=Model::F_('grouptopictag')// 总排行
			->order('grouptopictag_count DESC')
			->limit(0,$GLOBALS['_cache_']['group_option']['group_hottag_date'])
			->getAll();

		Core_Extend::saveSyscache('group_toptag',$arrData);
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
