<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   删除活动控制器($$)*/

!defined('Q_PATH') && exit;

class Delete_C_Controller extends InitController{

	public function index(){
		$nEventid=intval(Q::G('id','G'));
		if(empty($nEventid)){
			$this->E(Q::L('你没有指定活动ID','Controller'));
		}

		$oEvent=EventModel::F('event_status=1 AND event_id=?',$nEventid)->getOne();
		if(empty($oEvent['event_id'])){
			$this->E(Q::L('你要删除的活动不存在','Controller'));
		}

		// 判断权限
		if(!Eventadmin_Extend::checkEvent($oEvent)){
			$this->E(Q::L('你没有权限删除活动','Controller'));
		}

		$oEvent->event_status=CommonModel::STATUS_RECYLE;
		$oEvent->save('update');
		if($oEvent->isError()){
			$this->E($oEvent->getErrorMessage());
		}

		// 发送提醒
		if($GLOBALS['___login___']['user_id']!=$oEvent['user_id']){
			$sNoticetemplate='<div class="notice_deleteevent"><span class="notice_title"><a href="{@space_link}">{user_name}</a>&nbsp;'.Q::L('删除了你的活动','Controller').'&nbsp;<a href="{@event_link}">'.$oEvent['event_title'].'</a></span><div class="notice_content"><div class="notice_quote"><span class="notice_quoteinfo">{content_message}</span></div>&nbsp;'.Q::L('如果你对该操作有任何疑问，可以联系相关人员咨询','Controller').'</div><div class="notice_action"><a href="{@event_link}">'.Q::L('查看','Controller').'</a></div></div>';

			$arrNoticedata=array(
				'@space_link'=>'home://space@?id='.$GLOBALS['___login___']['user_id'],
				'user_name'=>$GLOBALS['___login___']['user_name'],
				'@event_link'=>'event://event/show?id='.$oEvent['event_id'],
				'content_message'=>Core_Extend::subString($oEvent['event_content'],100,false,1,false),
			);

			try{
				Core_Extend::addNotice($sNoticetemplate,$arrNoticedata,$oEvent['user_id'],'deleteevent',$oEvent['event_id']);
			}catch(Exception $e){
				$this->E($e->getMessage());
			}
		}

		$this->S(Q::L('活动删除成功','Controller'));
	}

}
