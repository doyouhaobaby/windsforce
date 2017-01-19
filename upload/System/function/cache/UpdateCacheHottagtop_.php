<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   话题排行榜缓存($$)*/

!defined('Q_PATH') && exit;

/** 载入home应用配置信息 */
if(!isset($GLOBALS['_cache_']['home_option'])){
	if(!Q::classExists('HomeoptionModel')){
		require_once(WINDSFORCE_PATH.'/System/app/home/App/Class/Model/HomeoptionModel.class.php');
	}

	Core_Extend::loadCache('home_option');
}

class UpdateCacheHottagtop{

	public static function cache(){
		$arrData=array();

		$nHomefreshtagHotnum=$GLOBALS['_cache_']['home_option']['homefreshtag_hot_num'];
		if($nHomefreshtagHotnum<2){
			$nHomefreshtagHotnum=2;
		}
		
		$arrData['hour']=self::cache_(3600,$nHomefreshtagHotnum);// 一小时排行
		$arrData['today']=self::cache_(86400,$nHomefreshtagHotnum);// 今日排行
		$arrData['week']=self::cache_(604800,$nHomefreshtagHotnum);// 本周排行
		$arrData['month']=self::cache_(2592000,$nHomefreshtagHotnum);// 当月排行
		$arrData['year']=self::cache_(31536000,$nHomefreshtagHotnum);// 年度排行
		$arrData['total']=Model::F_('homefreshtag','homefreshtag_status=?',1)// 总排行
			->setColumns('homefreshtag_id,homefreshtag_name,homefreshtag_usercount,homefreshtag_homefreshcount,homefreshtag_totalcount,create_dateline,homefreshtag_username,user_id')
			->order('homefreshtag_totalcount DESC')
			->limit(0,$nHomefreshtagHotnum)
			->getAll();

		Core_Extend::saveSyscache('hottagtop',$arrData);
	}

	protected static function cache_($nDate,$nHomefreshtagHotnum){
		return Model::F_('homefreshtag','homefreshtag_status=? AND create_dateline>?',1,(CURRENT_TIMESTAMP-$nDate))
			->setColumns('homefreshtag_id,homefreshtag_name,homefreshtag_usercount,homefreshtag_homefreshcount,homefreshtag_totalcount,create_dateline,homefreshtag_username,user_id')
			->order('homefreshtag_totalcount DESC')
			->limit(0,$nHomefreshtagHotnum)
			->getAll();
	}

}
