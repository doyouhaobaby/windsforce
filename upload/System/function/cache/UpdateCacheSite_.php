<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   站点统计缓存($$)*/

!defined('Q_PATH') && exit;

class UpdateCacheSite{

	public static function cache(){
		$arrData=array();

		$arrData['homefresh']=Model::F_('homefresh','homefresh_status=1')->all()->getCounts();
		$arrData['app']=Model::F_('app','app_status=1')->all()->getCounts();
		$arrData['user']=Model::F_('user','user_status=1')->all()->getCounts();

		Core_Extend::saveSyscache('site',$arrData);
	}

}
