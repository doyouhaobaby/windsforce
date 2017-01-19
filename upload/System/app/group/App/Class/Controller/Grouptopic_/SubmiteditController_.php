<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   处理帖子编辑控制器($$)*/

!defined('Q_PATH') && exit;

class Submitedit_C_Controller extends InitController{

	public function index(){
		$nGid=intval(Q::G('group_id'));
		$nTid=intval(Q::G('grouptopic_id'));

		$oGrouptopic=GrouptopicModel::F('group_id=? AND grouptopic_id=? AND grouptopic_status=1',$nGid,$nTid)->getOne();
		if(empty($oGrouptopic->group_id)){
			$this->E(Q::L('你访问的主题不存在或已删除','Controller'));
		}

		try{
			// 验证小组权限
			Groupadmin_Extend::checkGroup($oGrouptopic['group_id'],true);
		}catch(Exception $e){
			$this->E($e->getMessage());
		}

		if(!Groupadmin_Extend::checkTopicedit($oGrouptopic)){
			$this->E(Q::L('你没有权限编辑帖子','Controller'));
		}

		// 处理checkbox
		$arrCheckbox=array(
			'grouptopic_usesign','grouptopic_isanonymous','grouptopic_hiddenreplies',
			'grouptopic_ordertype','grouptopic_allownoticeauthor','grouptopic_iscomment',
			'grouptopic_sticktopic','grouptopic_addtodigest','grouptopic_isrecommend',
			'grouptopic_onlycommentview',
		);

		foreach($arrCheckbox as $sCheckbox){
			if(!isset($_POST[$sCheckbox])){
				$_POST[$sCheckbox]=0;
			}
		}

		$arrParsemessage=Core_Extend::contentParsetag(Core_Extend::replaceAttachment(trim($_POST['grouptopic_content'])));
		$_POST['grouptopic_content']=$arrParsemessage['content'];

		if($GLOBALS['_option_']['seccode_publish_status']==1){
			$this->_oParent->check_seccode(true);
		}

		$nIsrecommend=$oGrouptopic->grouptopic_isrecommend;
		$nAddtodigest=$oGrouptopic->grouptopic_addtodigest;
		$nSticktopic=$oGrouptopic->grouptopic_sticktopic;
		
		$oGrouptopic->grouptopic_updateusername=$GLOBALS['___login___']['user_name'];
		$oGrouptopic->updateData();
		if($oGrouptopic->isError()){
			$this->E($oGrouptopic->getErrorMessage());
		}

		// 更新积分
		if($oGrouptopic->grouptopic_addtodigest>0 && $nAddtodigest<$oGrouptopic->grouptopic_addtodigest){
			Core_Extend::updateCreditByAction('group_topicdigest'.$oGrouptopic['grouptopic_addtodigest'],$oGrouptopic['user_id']);
		}

		if($oGrouptopic->grouptopic_sticktopic>0 && $nSticktopic<$oGrouptopic->grouptopic_sticktopic){
			Core_Extend::updateCreditByAction('group_topicstick'.$oGrouptopic['grouptopic_sticktopic'],$oGrouptopic['user_id']);
		}

		if($oGrouptopic->grouptopic_isrecommend>0 && $nIsrecommend<$oGrouptopic->grouptopic_isrecommend){
			Core_Extend::updateCreditByAction('group_trecommend'.$oGrouptopic['grouptopic_isrecommend'],$oGrouptopic['user_id']);
		}

		// 保存帖子标签
		$sTags=trim(Q::G('tags','P'));
		$sOldTags=trim(Q::G('old_tags','P'));
		$oGrouptopictag=Q::instance('GrouptopictagModel');
		$oGrouptopictag->addTag($oGrouptopic->grouptopic_id,$sTags,$sOldTags);
		if($oGrouptopictag->isError()){
			$this->E($oGrouptopictag->getErrorMessage());
		}
		
		$sFeedUrl=Group_Extend::getTopicurl($oGrouptopic,false,false);

		// 发送feed
		$sFeedtemplate='<div class="feed_addgrouptopic"><span class="feed_title">'.Q::L('编辑了帖子','Controller').'&nbsp;<a href="{@grouptopic_link}">'.$oGrouptopic['grouptopic_title'].'</a></span><div class="feed_content">{grouptopic_message}</div><div class="feed_action"><a href="{@grouptopic_link}#comments">'.Q::L('回复','Controller').'</a></div></div>';

		$arrFeeddata=array(
			'@grouptopic_link'=>$sFeedUrl,
			'grouptopic_message'=>Core_Extend::subString($_POST['grouptopic_content'],100,false,1,false),
		);

		try{
			Core_Extend::addFeed($sFeedtemplate,$arrFeeddata);
		}catch(Exception $e){
			$this->E($e->getMessage());
		}

		// 发送提醒
		if($GLOBALS['___login___']['user_id']!=$oGrouptopic['user_id']){
			$sGrouptopicmessage=Core_Extend::subString($_POST['grouptopic_content'],100,false,1,false);
			
			$sNoticetemplate='<div class="notice_editgrouptopic"><span class="notice_title"><a href="{@space_link}">{user_name}</a>&nbsp;'.Q::L('编辑了你的主题','Controller').'&nbsp;<a href="{@grouptopic_link}">'.$oGrouptopic['grouptopic_title'].'</a></span><div class="notice_content"><div class="notice_quote"><span class="notice_quoteinfo">{content_message}</span></div>&nbsp;'.Q::L('如果你对该操作有任何疑问，可以联系相关人员咨询','Controller').'</div><div class="notice_action"><a href="{@grouptopic_link}">'.Q::L('查看','Controller').'</a></div></div>';

			$arrNoticedata=array(
				'@space_link'=>'group://space@?id='.$GLOBALS['___login___']['user_id'],
				'user_name'=>$GLOBALS['___login___']['user_name'],
				'@grouptopic_link'=>$sFeedUrl,
				'content_message'=>$sGrouptopicmessage,
			);

			try{
				Core_Extend::addNotice($sNoticetemplate,$arrNoticedata,$oGrouptopic['user_id'],'editgrouptopic',$oGrouptopic['grouptopic_id']);
			}catch(Exception $e){
				$this->E($e->getMessage());
			}
		}

		// 发送@提醒
		if($arrParsemessage['atuserids']){
			foreach($arrParsemessage['atuserids'] as $nAtuserid){
				if($nAtuserid!=$GLOBALS['___login___']['user_id']){
					$sGrouptopicmessage=Core_Extend::subString($_POST['grouptopic_content'],100,false,1,false);
					
					$sNoticetemplate='<div class="notice_atgrouptopic"><span class="notice_title"><a href="{@space_link}">{user_name}</a>&nbsp;'.Q::L('在主题中提到了你','Controller').'</span><div class="notice_content"><div class="notice_quote"><span class="notice_quoteinfo">{content_message}</span></div></div><div class="notice_action"><a href="{@grouptopic_link}">'.Q::L('查看','Controller').'</a></div></div>';

					$arrNoticedata=array(
						'@space_link'=>'group://space@?id='.$GLOBALS['___login___']['user_id'],
						'user_name'=>$GLOBALS['___login___']['user_name'],
						'@grouptopic_link'=>$sFeedUrl,
						'content_message'=>$sGrouptopicmessage,
					);

					try{
						Core_Extend::addNotice($sNoticetemplate,$arrNoticedata,$nAtuserid,'atgrouptopic',$oGrouptopic['grouptopic_id']);
					}catch(Exception $e){
						$this->E($e->getMessage());
					}
				}
			}
		}

		$sUrl=Group_Extend::getTopicurl($oGrouptopic);
		$this->A(array('url'=>$sUrl),Q::L('主题编辑成功','Controller'),1);
	}

}
