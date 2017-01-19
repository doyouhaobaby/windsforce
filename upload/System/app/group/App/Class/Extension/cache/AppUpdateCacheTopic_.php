<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   首页帖子缓存($$)*/

!defined('Q_PATH') && exit;

class AppUpdateCacheTopic{

	public static function cache(){
		$arrData=array();

		// 组长推荐帖子 && 系统推荐
		$arrData['recommend']=self::recommend_();
		$arrData['recommend_system']=self::recommend_system_();
		
		// 热门帖子 && 热门缩略图帖子
		$arrData['hot']=self::hot_();
		$arrData['hot_thumb']=self::hot_thumb_();

		Core_Extend::saveSyscache('group_topic',$arrData);
	}

	protected static function recommend_($sSqlMore=''){
		return Model::F_('grouptopic','@A','A.grouptopic_status=1 AND A.grouptopic_isrecommend=?',1)
			->setColumns('A.grouptopic_id,A.grouptopic_title,A.user_id,A.grouptopic_username,A.grouptopic_comments,A.grouptopic_views,A.grouptopic_loves,A.grouptopic_color,A.create_dateline,A.grouptopic_thumb')
			->joinLeft(Q::C('DB_PREFIX').'grouptopiccategory AS B','B.grouptopiccategory_name','A.grouptopiccategory_id=B.grouptopiccategory_id')
			->order('A.grouptopic_id DESC')
			->limit(0,$GLOBALS['_cache_']['group_option']['index_groupadminretopic_num'])
			->getAll();
	}
	
	protected static function recommend_system_(){
		return Model::F_('grouptopic','@A','A.grouptopic_status=1 AND A.grouptopic_isrecommend=?',2)
			->setColumns('A.grouptopic_id,A.grouptopic_title,A.user_id,A.grouptopic_username,A.grouptopic_comments,A.grouptopic_views,A.grouptopic_loves,A.grouptopic_color,A.create_dateline,A.grouptopic_thumb')
			->joinLeft(Q::C('DB_PREFIX').'grouptopiccategory AS B','B.grouptopiccategory_name','A.grouptopiccategory_id=B.grouptopiccategory_id')
			->order('A.grouptopic_id DESC')
			->limit(0,$GLOBALS['_cache_']['group_option']['index_systemrecommendtopic_num'])
			->getAll();
	}

	protected static function hot_($nNum=0,$nDate=0){
		// 热门帖子时间
		if($nDate==0){
			$nDate=$GLOBALS['_cache_']['group_option']['group_hottopic_date'];
			if($nDate<3600){
				$nDate=3600;
			}
		}

		// 热门帖子数量
		if($nNum==0){
			$nNum=$GLOBALS['_cache_']['group_option']['group_hottopic_num'];
			if($nNum<2){
				$nNum=2;
			}
		}
		
		$arrGrouphottopics=Model::F_('grouptopic','@A','A.create_dateline>? AND A.grouptopic_status=1',CURRENT_TIMESTAMP-$nDate)
			->setColumns('A.grouptopic_id,A.grouptopic_title,A.user_id,A.grouptopic_username,A.grouptopic_comments,A.grouptopic_views,A.grouptopic_loves,A.grouptopic_color,A.create_dateline,A.grouptopic_thumb')
			->joinLeft(Q::C('DB_PREFIX').'grouptopiccategory AS B','B.grouptopiccategory_name','A.grouptopiccategory_id=B.grouptopiccategory_id')
			//->order('A.grouptopic_comments DESC')
			->order('A.grouptopic_id DESC')
			->limit(0,$nNum)
			->getAll();

		return $arrGrouphottopics;
	}

	protected static function hot_thumb_($nNum=0,$nDate=0){
		// 幻灯片帖子时间
		if($nDate==0){
			$nDate=$GLOBALS['_cache_']['group_option']['group_thumbtopic_date'];
			if($nDate<3600){
				$nDate=3600;
			}
		}
		
		// 首页幻灯片帖子数量
		if($nNum==0){
			$nNum=$GLOBALS['_cache_']['group_option']['group_thumbtopic_num'];
			if($nNum<1){
				$nNum=2;
			}
		}

		$arrGroupthumbtopics=Model::F_('grouptopic','@A','A.grouptopic_status=1 AND A.grouptopic_thumb!=\'\' AND A.create_dateline>?',CURRENT_TIMESTAMP-$nDate)
			->setColumns('A.grouptopic_id,A.grouptopic_title,A.user_id,A.grouptopic_username,A.grouptopic_comments,A.grouptopic_views,A.grouptopic_loves,A.grouptopic_color,A.create_dateline,A.grouptopic_thumb')
			->joinLeft(Q::C('DB_PREFIX').'grouptopiccategory AS B','B.grouptopiccategory_name','A.grouptopiccategory_id=B.grouptopiccategory_id')
			->order('A.grouptopic_id DESC')
			->limit(0,$nNum)
			->getAll();

		return $arrGroupthumbtopics;
	}

}
