<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   最新小组缓存($$)*/

!defined('Q_PATH') && exit;

class AppUpdateCacheNewgroup{

	public static function cache(){
		$arrData=array();
		$arrData=Model::F_('group','group_status=?',1)
			->setColumns('group_id,group_name,group_nikename,group_listdescription,group_icon,group_totaltodaynum,group_usernum')
			->order('create_dateline DESC')
			->limit(0,$GLOBALS['_cache_']['group_option']['index_newgroupnum'])
			->getAll();

		Core_Extend::saveSyscache('group_newgroup',$arrData);
	}

}
