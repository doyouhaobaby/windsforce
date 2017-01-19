<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   删除帖子控制器($$)*/

!defined('Q_PATH') && exit;

class Deletetopic_C_Controller extends InitController{

	public function index(){
		$sGrouptopics=trim(Q::G('grouptopics'));
		$nGroupid=intval(Q::G('groupid'));
		$sReason=trim(Q::G('reason'));

		$oGroup=Group_Extend::getGroup($nGroupid,true,true);
		if(empty($oGroup['group_id'])){
			$this->E(Q::L('小组不存在或在审核中','Controller'));
		}
		
		if(!Groupadmin_Extend::checkTopicadminRbac($nGroupid,array('group@grouptopicadmin@deletetopic'))){
			$this->E(Q::L('你没有删除帖子的权限','Controller'));
		}
		
		$arrGrouptopics=explode(',',$sGrouptopics);
		$bAdmincredit=false;
		if(!$sReason){
			$sReason=Q::L('该管理人员没有填写操作原因','Controller');
		}
		
		foreach($arrGrouptopics as $nGrouptopic){
			$oGrouptopic=GrouptopicModel::F('grouptopic_id=? AND grouptopic_status=1',$nGrouptopic)->getOne();
			if(!empty($oGrouptopic['grouptopic_id'])){
				$nUserid=$oGrouptopic['user_id'];
				$sGrouptopictitle=$oGrouptopic['grouptopic_title'];

				// 帖子回收站功能开启
				if($GLOBALS['_cache_']['group_option']['group_deletetopic_recyclebin']==1){
					$oGrouptopic->grouptopic_status='0';
				}else{
					$oGrouptopic->grouptopic_status=CommonModel::STATUS_RECYLE;
				}

				$oGrouptopic->save('update');
				if($oGrouptopic->isError()){
					$this->E($oGrouptopic->getErrorMessage());
				}

				// 更新积分
				Q::instance('UsercountModel')->increase(array($oGrouptopic->user_id),array('usercount_grouptopic'=>-1));

				// 更新小组帖子数量
				$oGroup->group_topicnum=$oGroup->group_topicnum-1;
				$oGroup->save('update');
				if($oGroup->isError()){
					$this->E($oGroup->getErrorMessage());
				}
				
				// 发送提醒
				if($GLOBALS['___login___']['user_id']!=$nUserid){
					$sNoticetemplate='<div class="notice_deletegrouptopic"><span class="notice_title"><a href="{@space_link}">{user_name}</a>&nbsp;'.Q::L('对你的主题执行了删除','Controller').'&nbsp;<a href="{@grouptopic_link}">'.$sGrouptopictitle.'</a></span><div class="notice_content"><div class="notice_quote"><span class="notice_quoteinfo">{admin_reason}</span></div>&nbsp;'.($GLOBALS['_cache_']['group_option']['group_deletetopic_recyclebin']==1?Q::L('注意，系统开启了主题回收站功能，该主题仍可以被恢复','Controller'):Q::L('注意，系统未开启主题回收站功能，该主题已被永久删除','Controller')).'&nbsp;&nbsp;'.Q::L('如果你对该操作有任何疑问，可以联系相关人员咨询','Controller').'</div><div class="notice_action"><a href="{@grouptopic_link}">'.Q::L('查看','Controller').'</a></div></div>';

					$arrNoticedata=array(
						'@space_link'=>'group://space@?id='.$GLOBALS['___login___']['user_id'],
						'user_name'=>$GLOBALS['___login___']['user_name'],
						'@grouptopic_link'=>'group://grouptopic/view?id='.$oGrouptopic['grouptopic_id'],
						'admin_reason'=>$sReason,
					);

					try{
						Core_Extend::addNotice($sNoticetemplate,$arrNoticedata,$nUserid,'deletegrouptopic',$oGrouptopic['grouptopic_id']);
					}catch(Exception $e){
						$this->E($e->getMessage());
					}
				}

				Core_Extend::updateCreditByAction('group_topicdelete',$nUserid);
				$bAdmincredit=true;
			}
		}

		// 管理积分
		if($bAdmincredit===true){
			Core_Extend::updateCreditByAction('group_topicadmin',$GLOBALS['___login___']['user_id']);
		}

		$sGroupurl=Group_Extend::getGroupurl($oGroup);
		$this->A(array('group_id'=>$nGroupid,'group_url'=>$sGroupurl),Q::L('删除主题成功','Controller'));
	}

}
