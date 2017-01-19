<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   参加活动处理控制器($$)*/

!defined('Q_PATH') && exit;

class Joinin_C_Controller extends InitController{

	public function index(){
		$nEventid=intval(Q::G('event_id'));
		$sEventusercontact=C::text(trim(Q::G('eventuser_contact')));
		if(empty($nEventid)){
			$this->E(Q::L('你没有指定活动ID','Controller'));
		}

		$oEvent=EventModel::F('event_status=1 AND event_id=?',$nEventid)->getOne();
		if(empty($oEvent['event_id'])){
			$this->E(Q::L('你要浏览的活动不存在','Controller'));
		}

		// 判断是否已经参加活动
		$oEventuser=EventuserModel::F('event_id=? AND user_id=?',$nEventid,$GLOBALS['___login___']['user_id'])->getOne();
		if($GLOBALS['___login___']['user_id']==$oEvent['user_id'] || !empty($oEventuser['user_id'])){
			$this->E(Q::L('你已经参加过该活动或者你是活动发起人','Controller'));
		}

		// 判断活动是否已经结束
		if($oEvent['event_endtime']<CURRENT_TIMESTAMP){
			$this->E(Q::L('对不起，活动已经结束','Controller'));
		}

		// 判断是否有剩余名额
		if($oEvent['event_limitcount']){
			if($oEvent['event_limitcount']-$oEvent['event_joincount']<=0){
				$this->E(Q::L('活动参加人数已满','Controller'));
			}
		}

		// 写入参加活动
		$oEventuser=new EventuserModel();
		$oEventuser->event_id=$nEventid;
		$oEventuser->eventuser_contact=$sEventusercontact;
		if($oEvent['event_isaudit']==1){
			$oEventuser->eventuser_status='0';
		}
		$oEventuser->save();
		if($oEventuser->isError()){
			$this->E($oEventuser->getErrorMessage());
		}

		// 更新活动参加人数
		$oEventTemp=Q::instance('EventModel');
		$oEventTemp->updateEventjoinnum($nEventid);
		if($oEventTemp->isError()){
			$oEventTemp->getErrorMessage();
		}

		$this->S(Q::L('参加活动成功','Controller'));
	}

}
