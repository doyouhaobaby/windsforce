<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   删除我参加的活动($$)*/

!defined('Q_PATH') && exit;

class Joindelete_C_Controller extends InitController{

	public function index(){
		$arrEventid=Q::G('key');

		if($arrEventid){
			foreach($arrEventid as $nEventid){
				$oEventuser=EventuserModel::F('event_id=? AND user_id=?',$nEventid,$GLOBALS['___login___']['user_id'])->getOne();
				if(!empty($oEventuser['user_id'])){
					$oDb=Db::RUN();
					$sSql="DELETE FROM ".$GLOBALS['_commonConfig_']['DB_PREFIX']."eventuser WHERE event_id={$nEventid} AND user_id={$GLOBALS['___login___']['user_id']}";
					$oDb->query($sSql);

					// 整理活动参加的数量
					$oEvent=EventModel::F('event_id=?',$nEventid)->getOne();
					if(!empty($oEvent['event_id'])){
						$oEvent->updateEventjoinnum($oEvent['event_id']);
					}
				}
			}
		}else{
			$this->E(Q::L('你没有选择待删除的活动','Controller'));
		}

		$this->S(Q::L('删除我参加的成功','Controller'));
	}

}
