<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   短消息检测($$)*/

!defined('Q_PATH') && exit;

class Checkpm_C_Controller extends InitController{

	public function index(){
		if($GLOBALS['_option_']['pm_status']==0){
			$this->E(Q::L('短消息功能尚未开启','Controller'));
		}

		if(Core_Extend::isAdmin()){
			return;
		}

		$arrUser=Model::F_('user')->where('user_id=?',$GLOBALS['___login___']['user_id'])
			->setColumns('create_dateline')
			->getOne();

		if($GLOBALS['_option_']['pmsend_regdays']>0){
			if(CURRENT_TIMESTAMP-$arrUser['create_dateline']<86400*$GLOBALS['_option_']['pmsend_regdays']){
				$this->E(Q::L('只有注册时间超过 %d 天的用户才能够发送短消息','Controller',null,$GLOBALS['_option_']['pmsend_regdays']));
			}
		}
		
		if($GLOBALS['_option_']['pmflood_ctrl']>0){
			$arrPm=PmModel::F("pm_msgfromid=? AND ".CURRENT_TIMESTAMP."-create_dateline<".$GLOBALS['_option_']['pmflood_ctrl'],$GLOBALS['___login___']['user_id'])
			->setColumns('pm_id')
			->query();
			if(!empty($arrPm['pm_id'])){
				$this->E(Q::L('每 %d 秒你才能发送一次短消息','Controller',null,$GLOBALS['_option_']['pmflood_ctrl']));
			}
		}
		
		if($GLOBALS['_option_']['pmlimit_oneday']>0){
			$arrNowDate=Core_Extend::getBeginEndDay();
			
			$nPms=Model::F_('pm',"create_dateline<{$arrNowDate[1]} AND create_dateline>{$arrNowDate[0]} AND pm_msgfromid=?",$GLOBALS['___login___']['user_id'])
				->all()
				->getCounts();
			if($nPms>$GLOBALS['_option_']['pmlimit_oneday']){
				$this->E(Q::L('一个用户每天最多只能发送 %d 条消息','Controller',null,$GLOBALS['_option_']['pmlimit_oneday']));
			}
		}
	}

}
