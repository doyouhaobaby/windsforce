<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   最新注册用户缓存($$)*/

!defined('Q_PATH') && exit;

class UpdateCacheNewuser{

	public static function cache(){
		$nHomenewusernum=intval($GLOBALS['_option_']['home_newuser_num']);
		if($nHomenewusernum<2){
			$nHomenewusernum=2;
		}
		
		$arrSaveData=Model::F_('user')->where('user_status=?',1)
			->setColumns('user_id,user_name,create_dateline')
			->order('user_id DESC')
			->limit(0,$nHomenewusernum)
			->getAll();

		Core_Extend::saveSyscache('newuser',$arrSaveData);
	}

}
