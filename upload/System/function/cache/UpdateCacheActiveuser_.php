<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   最新活跃用户缓存($$)*/

!defined('Q_PATH') && exit;

class UpdateCacheActiveuser{

	public static function cache(){
		$nHomeactiveusernum=intval($GLOBALS['_option_']['home_activeuser_num']);
		if($nHomeactiveusernum<2){
			$nHomeactiveusernum=2;
		}

		$arrSaveData=Model::F_('user')->where('user_status=?',1)
			->setColumns('user_id,user_name,user_lastlogintime')
			->order('user_lastlogintime DESC')
			->limit(0,$nHomeactiveusernum)
			->getAll();

		Core_Extend::saveSyscache('activeuser',$arrSaveData);
	}

}
