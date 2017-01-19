<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   置顶或者取消置顶帖子控制器($$)*/

!defined('Q_PATH') && exit;

class Sticktopic_C_Controller extends InitController{

	public function index(){
		$sGrouptopics=trim(Q::G('grouptopics'));
		$nGroupid=intval(Q::G('groupid'));
		$nStatus=intval(Q::G('status'));
		$sReason=trim(Q::G('reason'));

		if(!Groupadmin_Extend::checkTopicadminRbac($nGroupid,array('group@grouptopicadmin@sticktopic'))){
			$this->E(Q::L('你没有置顶或者取消置顶帖子的权限','Controller'));
		}

		$arrGrouptopics=explode(',',$sGrouptopics);
		$bAdmincredit=false;
		if(!$sReason){
			$sReason=Q::L('该管理人员没有填写操作原因','Controller');
		}
		
		foreach($arrGrouptopics as $nGrouptopic){
			$oGrouptopic=GrouptopicModel::F('grouptopic_id=?',$nGrouptopic)->getOne();
			if(!empty($oGrouptopic['grouptopic_id']) && $oGrouptopic->grouptopic_sticktopic!=$nStatus){
				$bNeedcredit=false;
				if($oGrouptopic->grouptopic_sticktopic<$nStatus && $nStatus>0){
					$bNeedcredit=true;
				}
				
				$oGrouptopic->grouptopic_sticktopic=$nStatus;
				$oGrouptopic->setAutofill(false);
				$oGrouptopic->save('update');
				if($oGrouptopic->isError()){
					$this->E($oGrouptopic->getErrorMessage());
				}

				if($bNeedcredit===true){
					Core_Extend::updateCreditByAction('group_topicstick'.$nStatus,$oGrouptopic['user_id']);
				}

				// 发送提醒
				if($GLOBALS['___login___']['user_id']!=$oGrouptopic['user_id']){
					$sNoticetemplate='<div class="notice_'.($nStatus==1?'stickgrouptopic':'unstickgrouptopic').'"><span class="notice_title"><a href="{@space_link}">{user_name}</a>&nbsp;'.($nStatus==1?Q::L('对你的主题执行了置顶','Controller'):Q::L('对你的主题执行了取消置顶','Controller')).'&nbsp;<a href="{@grouptopic_link}">'.$oGrouptopic['grouptopic_title'].'</a></span><div class="notice_content"><div class="notice_quote"><span class="notice_quoteinfo">{admin_reason}</span></div>&nbsp;'.Q::L('如果你对该操作有任何疑问，可以联系相关人员咨询','Controller').'</div><div class="notice_action"><a href="{@grouptopic_link}">'.Q::L('查看','Controller').'</a></div></div>';

					$arrNoticedata=array(
						'@space_link'=>'group://space@?id='.$GLOBALS['___login___']['user_id'],
						'user_name'=>$GLOBALS['___login___']['user_name'],
						'@grouptopic_link'=>'group://grouptopic/view?id='.$oGrouptopic['grouptopic_id'],
						'admin_reason'=>$sReason,
					);

					try{
						Core_Extend::addNotice($sNoticetemplate,$arrNoticedata,$oGrouptopic['user_id'],($nStatus==1?'stickgrouptopic':'unstickgrouptopic'),$oGrouptopic['grouptopic_id']);
					}catch(Exception $e){
						$this->E($e->getMessage());
					}
				}

				$bAdmincredit=true;
			}
		}

		// 管理积分
		if($bAdmincredit===true){
			Core_Extend::updateCreditByAction('group_topicadmin',$GLOBALS['___login___']['user_id']);
		}

		$this->A(array('group_id'=>$nGroupid),$nStatus==0?Q::L('取消置顶主题成功','Controller'):Q::L('置顶主题成功','Controller'));
	}

}
