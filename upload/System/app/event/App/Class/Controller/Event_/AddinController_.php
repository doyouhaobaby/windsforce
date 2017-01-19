<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   添加活动入库控制器($$)*/

!defined('Q_PATH') && exit;

class Addin_C_Controller extends InitController{

	public function index(){
		if($GLOBALS['_cache_']['event_option']['front_add']==0 && !Core_Extend::isAdmin()){
			$this->E(Q::L('系统关闭了创建活动功能','Controller'));
		}

		if($GLOBALS['_option_']['seccode_publish_status']==1){
			$this->_oParent->check_seccode(true);
		}
		
		// 活动时间先验证
		$nEventstarttime=strtotime(trim(Q::G('event_starttime','P')));
		$nEventendtime=strtotime(trim(Q::G('event_endtime','P')));
		$nEventdeadline=strtotime(trim(Q::G('event_deadline','P')));
		if(!$nEventstarttime){
			$this->E(Q::L('活动开始时间不能为空','__APPEVENT_COMMON_LANG__@Model'));
		}

		if(!$nEventendtime){
			$this->E(Q::L('活动结束时间不能为空','__APPEVENT_COMMON_LANG__@Model'));
		}

		if(!$nEventdeadline){
			$this->E(Q::L('活动报名截止时间不能为空','__APPEVENT_COMMON_LANG__@Model'));
		}

		if($nEventstarttime>$nEventendtime){
			$this->E(Q::L('活动结束时间不能早于活动开始时间','__APPEVENT_COMMON_LANG__@Model'));
		}
		
		if($nEventdeadline<CURRENT_TIMESTAMP){
			//$this->E(Q::L('活动报名时间不能早于当前时间','__APPEVENT_COMMON_LANG__@Model'));
		}
		
		if($nEventdeadline>$nEventendtime){
			//$this->E(Q::L('活动报名时间不能晚于活动结束时间','__APPEVENT_COMMON_LANG__@Model'));
		}
		
		// 保存活动
		$oEvent=new EventModel();
		$oEvent->formatTime();
		if($GLOBALS['_cache_']['event_option']['event_audit']==1){
			$oEvent->event_status='0';
		}
		$oEvent->save();
		if($oEvent->isError()){
			$this->E($oEvent->getErrorMessage());
		}

		$sFeedLink='event://event/show?id='.$oEvent['event_id'];

		// 发送feed
		$sFeedtemplate='<div class="feed_addevent"><span class="feed_title">'.Q::L('发布了一个活动','Controller').'&nbsp;<a href="{@event_link}">'.$oEvent['event_title'].'</a></span><div class="feed_content">{event_content}</div><div class="feed_action"><a href="{@event_link}#comments">'.Q::L('回复','Controller').'</a></div></div>';

		$arrFeeddata=array(
			'@event_link'=>$sFeedLink,
			'event_content'=>Core_Extend::subString($oEvent['event_content'],100,false,1,false),
		);

		try{
			Core_Extend::addFeed($sFeedtemplate,$arrFeeddata);
		}catch(Exception $e){
			$this->E($e->getMessage());
		}

		$arrData=$oEvent->toArray();

		if($oEvent['event_status']==1){
			$arrData['url']=Q::U('event://e@?id='.$oEvent['event_id']);
		}else{
			$arrData['url']=Q::U('event://ucenter/index');
		}

		$this->A($arrData,Q::L('活动添加成功','Controller'));
	}

}
