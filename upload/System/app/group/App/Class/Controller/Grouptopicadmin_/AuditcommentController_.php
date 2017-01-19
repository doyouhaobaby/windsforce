<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   审核多个回帖控制器($$)*/

!defined('Q_PATH') && exit;

class Auditcomment_C_Controller extends InitController{

	public function index(){
		$nGrouptopics=intval(Q::G('grouptopics'));
		$sGrouptopiccomments=trim(Q::G('grouptopiccomments'));
		$nGroupid=intval(Q::G('groupid'));
		$sReason=trim(Q::G('reason'));

		$oGrouptopic=GrouptopicModel::F('grouptopic_id=?',$nGrouptopics)->getOne();
		if(empty($oGrouptopic['grouptopic_id'])){
			$this->E(Q::L('你操作回帖的主题不存在','Controller'));
		}

		$oGroup=Group_Extend::getGroup($nGroupid,true,true);
		if(empty($oGroup['group_id'])){
			$this->E(Q::L('小组不存在或在审核中','Controller'));
		}

		if(!Groupadmin_Extend::checkCommentadminRbac($oGrouptopic,array('group@grouptopicadmin@auditcomment'))){
			$this->E(Q::L('你没有权限审核回帖','Controller'));
		}
		
		$arrGrouptopiccomments=explode(',',$sGrouptopiccomments);
		$bAdmincredit=false;
		if(!$sReason){
			$sReason=Q::L('该管理人员没有填写操作原因','Controller');
		}

		foreach($arrGrouptopiccomments as $nGrouptopiccomment){
			$oGrouptopiccomment=GrouptopiccommentModel::F('grouptopiccomment_id=?',$nGrouptopiccomment)->getOne();
			if(!empty($oGrouptopiccomment['grouptopiccomment_id']) && $oGrouptopiccomment->grouptopiccomment_status!=1){
				$oGrouptopiccomment->grouptopiccomment_status=1;
				$oGrouptopiccomment->setAutofill(false);
				$oGrouptopiccomment->save('update');
				if($oGrouptopiccomment->isError()){
					$this->E($oGrouptopiccomment->getErrorMessage());
				}

				// 更新积分
				Q::instance('UsercountModel')->increase(array($oGrouptopiccomment->user_id),array('usercount_grouptopiccomment'=>-1));

				// 帖子回帖数量
				$oGrouptopic->grouptopic_comments=$oGrouptopic->grouptopic_comments+1;
				$oGrouptopic->setAutofill(false);
				$oGrouptopic->save('update');
				if($oGrouptopic->isError()){
					$this->E($oGrouptopic->getErrorMessage());
				}

				// 小组回帖数量
				$oGroup->group_topiccomment=$oGroup->group_topiccomment+1;
				$oGroup->save('update');
				if($oGroup->isError()){
					$this->E($oGroup->getErrorMessage());
				}

				// 发送提醒
				if($GLOBALS['___login___']['user_id']!=$oGrouptopiccomment['user_id']){
					$sNoticetemplate='<div class="notice_auditgrouptopiccomment"><span class="notice_title"><a href="{@space_link}">{user_name}</a>&nbsp;'.Q::L('对你的回帖执行了审核通过','Controller').'</span><div class="notice_content"><div class="notice_quote"><span class="notice_quoteinfo">{admin_reason}</span></div>&nbsp;'.Q::L('如果你对该操作有任何疑问，可以联系相关人员咨询','Controller').'</div><div class="notice_action"><a href="{@grouptopiccomment_link}">'.Q::L('查看','Controller').'</a></div></div>';

					$arrNoticedata=array(
						'@space_link'=>'group://space@?id='.$GLOBALS['___login___']['user_id'],
						'user_name'=>$GLOBALS['___login___']['user_name'],
						'@grouptopiccomment_link'=>'group://grouptopic/view?id='.$oGrouptopiccomment['grouptopic_id'].'&isolation_commentid='.$oGrouptopiccomment['grouptopiccomment_id'],
						'admin_reason'=>$sReason,
					);

					try{
						Core_Extend::addNotice($sNoticetemplate,$arrNoticedata,$oGrouptopiccomment['user_id'],'notice_auditgrouptopiccomment',$oGrouptopiccomment['grouptopiccomment_id']);
					}catch(Exception $e){
						$this->E($e->getMessage());
					}
				}

				$bAdmincredit=true;
			}
		}

		// 管理积分
		if($bAdmincredit===true){
			Core_Extend::updateCreditByAction('group_commentadmin',$GLOBALS['___login___']['user_id']);
		}

		$this->A(array('group_id'=>$nGroupid),Q::L('审核回帖成功','Controller'));
	}

}
