<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   站点统计缓存($$)*/

!defined('Q_PATH') && exit;

class AppUpdateCacheSite{

	public static function cache(){
		$arrData=array();
		$arrData['group']=Model::F_('group','group_status=?',1)->all()->getCounts();
		$arrData['grouptopic']=Model::F_('grouptopic','grouptopic_status=?',1)->all()->getCounts();
		$arrData['grouptopiccomment']=Model::F_('grouptopiccomment','grouptopiccomment_status=?',1)->all()->getCounts();

		Core_Extend::saveSyscache('group_site',$arrData);
	}

}
