<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   对话框发送短消息($$)*/

!defined('Q_PATH') && exit;

class Truncatepm_C_Controller extends InitController{

	public function index(){
		$nUserId=Q::G('uid','G');
		
		$sDate=Q::G('date','G');
		if(empty($sDate)){
			$sDate=3;
		}
		
		$oDb=Db::RUN();
		$sSql="UPDATE ".$GLOBALS['_commonConfig_']['DB_PREFIX'].
			"pm SET pm_status=0 WHERE `pm_msgfromid`={$nUserId} AND `pm_status`=1 AND `pm_msgtoid`=".
			$GLOBALS['___login___']['user_id'].
			($sDate!='all'?" AND `create_dateline`>=".(CURRENT_TIMESTAMP-$sDate*86400):'');
		$oDb->query($sSql);
		
		$this->assign('__JumpUrl__',Q::U('home://pm/index?type=user'));
		$this->S(Q::L('短消息清空成功','Controller'));
	}

}
