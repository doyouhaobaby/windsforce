<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   更新活动控制器($$)*/

!defined('Q_PATH') && exit;

class Update_C_Controller extends InitController{

	public function index(){
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
		
		// 更新活动
		$nEventid=intval(Q::G('event_id'));

		if(empty($nEventid)){
			$this->E(Q::L('你没有指定活动ID','Controller'));
		}

		$oEvent=EventModel::F('event_status=1 AND event_id=?',$nEventid)->getOne();
		if(empty($oEvent['event_id'])){
			$this->E(Q::L('你要更新的活动不存在','Controller'));
		}

		// 判断权限
		if(!Eventadmin_Extend::checkEvent($oEvent)){
			$this->E(Q::L('你没有权限编辑活动','Controller'));
		}

		$_POST['event_starttime']=$nEventstarttime;
		$_POST['event_endtime']=$nEventendtime;
		$_POST['event_deadline']=$nEventdeadline;
		$oEvent->save('update');
		if($oEvent->isError()){
			$this->E($oEvent->getErrorMessage());
		}

		$sFeedLink='event://event/show?id='.$oEvent['event_id'];

		// 发送feed
		$sFeedtemplate='<div class="feed_addevent"><span class="feed_title">'.Q::L('编辑了活动','Controller').'&nbsp;<a href="{@event_link}">'.$oEvent['event_title'].'</a></span><div class="feed_content">{event_content}</div><div class="feed_action"><a href="{@event_link}#comments">'.Q::L('回复','Controller').'</a></div></div>';

		$arrFeeddata=array(
			'@event_link'=>$sFeedLink,
			'event_content'=>Core_Extend::subString($oEvent['event_content'],100,false,1,false),
		);

		try{
			Core_Extend::addFeed($sFeedtemplate,$arrFeeddata);
		}catch(Exception $e){
			$this->E($e->getMessage());
		}

		// 发送提醒
		if($GLOBALS['___login___']['user_id']!=$oEvent['user_id']){
			$sEventmessage=Core_Extend::subString($oEvent['event_content'],100,false,1,false);
			
			$sNoticetemplate='<div class="notice_editevent"><span class="notice_title"><a href="{@space_link}">{user_name}</a>&nbsp;'.Q::L('编辑了你的活动','Controller').'&nbsp;<a href="{@event_link}">'.$oEvent['event_title'].'</a></span><div class="notice_content"><div class="notice_quote"><span class="notice_quoteinfo">{content_message}</span></div>&nbsp;'.Q::L('如果你对该操作有任何疑问，可以联系相关人员咨询','Controller').'</div><div class="notice_action"><a href="{@event_link}">'.Q::L('查看','Controller').'</a></div></div>';

			$arrNoticedata=array(
				'@space_link'=>'home://space@?id='.$GLOBALS['___login___']['user_id'],
				'user_name'=>$GLOBALS['___login___']['user_name'],
				'@event_link'=>$sFeedLink,
				'content_message'=>$sEventmessage,
			);

			try{
				Core_Extend::addNotice($sNoticetemplate,$arrNoticedata,$oEvent['user_id'],'editevent',$oEvent['event_id']);
			}catch(Exception $e){
				$this->E($e->getMessage());
			}
		}

		$arrData=$oEvent->toArray();
		$arrData['url']=Q::U('event://e@?id='.$oEvent['event_id']);
		$this->A($arrData,Q::L('活动更新成功','Controller'));
	}

}
