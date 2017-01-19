<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   感兴趣活动处理控制器($$)*/

!defined('Q_PATH') && exit;

class Attention_C_Controller extends InitController{

	public function index(){
		$nEventid=intval(Q::G('id'));
		if(empty($nEventid)){
			$this->E(Q::L('你没有指定活动ID','Controller'));
		}

		$oEvent=EventModel::F('event_status=1 AND event_id=?',$nEventid)->getOne();
		if(empty($oEvent['event_id'])){
			$this->E(Q::L('你要浏览的活动不存在','Controller'));
		}

		// 判断是否已经参加过活动了
		$oEventattentionuser=EventattentionuserModel::F('event_id=? AND user_id=?',$nEventid,$GLOBALS['___login___']['user_id'])->getOne();
		if(!empty($oEventattentionuser['event_id'])){
			$this->E(Q::L('你已经对这个应用感兴趣过了','Controller'));
		}

		// 判断活动是否已经结束
		if($oEvent['event_endtime']<CURRENT_TIMESTAMP){
			$this->E(Q::L('对不起，活动已经结束','Controller'));
		}

		// 写入活动
		$oEventattentionuser=new EventattentionuserModel();
		$oEventattentionuser->event_id=$nEventid;
		$oEventattentionuser->save();
		if($oEventattentionuser->isError()){
			$this->E($oEventattentionuser->getErrorMessage());
		}

		// 更新活动感兴趣人数
		$oEventTemp=Q::instance('EventModel');
		$oEventTemp->updateEventattentionnum($nEventid);
		if($oEventTemp->isError()){
			$oEventTemp->getErrorMessage();
		}

		$this->S(Q::L('添加感兴趣活动成功','Controller'));
	}

}
