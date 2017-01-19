<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   添加帖子入库控制器($$)*/

!defined('Q_PATH') && exit;

class Addtopic_C_Controller extends InitController{

	public function index(){
		try{
			Core_Extend::checkSpam();
		}catch(Exception $e){
			$this->E($e->getMessage());
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

		// 小组相关检查
		$nGroupid=intval(Q::G('group_id','P'));

		// 访问权限
		$oGroup=GroupModel::F('group_id=? AND group_status=1',$nGroupid)->getOne();
		if(empty($oGroup['group_id'])){
			$this->E(Q::L('小组不存在或者还在审核中','Controller'));
		}

		try{
			// 验证小组权限
			Groupadmin_Extend::checkGroup($oGroup,true);
		}catch(Exception $e){
			$this->E($e->getMessage());
		}

		if($GLOBALS['_option_']['seccode_publish_status']==1){
			$this->_oParent->check_seccode(true);
		}
	
		// 保存帖子
		$oGrouptopic=new GrouptopicModel();
		$oGrouptopic->grouptopic_update=CURRENT_TIMESTAMP;
		if($oGroup->group_audittopic==1){
			$oGrouptopic->grouptopic_status='0';
		}
		if(!$oGrouptopic->saveData()){
			$this->E($oGrouptopic->getErrorMessage());
		}

		// 更新积分
		Core_Extend::updateCreditByAction('group_addtopic',$GLOBALS['___login___']['user_id']);

		if($oGrouptopic['grouptopic_addtodigest']>0){
			Core_Extend::updateCreditByAction('group_topicdigest'.$oGrouptopic['grouptopic_addtodigest'],$GLOBALS['___login___']['user_id']);
		}

		if($oGrouptopic['grouptopic_sticktopic']>0){
			Core_Extend::updateCreditByAction('group_topicstick'.$oGrouptopic['grouptopic_sticktopic'],$GLOBALS['___login___']['user_id']);
		}

		if($oGrouptopic['grouptopic_isrecommend']>0){
			Core_Extend::updateCreditByAction('group_trecommend'.$oGrouptopic['grouptopic_isrecommend'],$GLOBALS['___login___']['user_id']);
		}

		// 保存帖子标签
		$sTags=trim(Q::G('tags','P'));
		if($sTags){
			$oGrouptopictag=Q::instance('GrouptopictagModel');
			$oGrouptopictag->addTag($oGrouptopic->grouptopic_id,$sTags);
			if($oGrouptopictag->isError()){
				$this->E($oGrouptopictag->getErrorMessage());
			}
		}

		// 更新小组帖子数量和最后更新
		if($oGrouptopic->grouptopic_status){
			// 用户帖子
			Q::instance('UsercountModel')->increase(array($GLOBALS['___login___']['user_id']),array('usercount_grouptopic'=>1));
			$oGroup->group_topicnum=$oGroup->group_topicnum+1;
		}

		$arrLatestData=array(
			'topictime'=>$oGrouptopic->create_dateline,
			'topicid'=>$oGrouptopic->grouptopic_id,
			'topicuserid'=>$GLOBALS['___login___']['user_id'],
			'topicusername'=>$GLOBALS['___login___']['user_name'],
			'topictitle'=>$oGrouptopic['grouptopic_title'],
		);

		$oGroup->group_latestcomment=serialize($arrLatestData);
		$oGroup->group_topictodaynum=$oGroup->group_topictodaynum+1;
		$oGroup->group_totaltodaynum=$oGroup->group_topictodaynum+$oGroup->group_topiccommenttodaynum;
		$oGroup->save('update');
		if($oGroup->isError()){
			$this->E($oGroup->getErrorMessage());
		}

		// 保存小组今日数据
		$this->cache_site_();

		$sFeedUrl=Group_Extend::getTopicurl($oGrouptopic,false,false);

		// 发送feed
		$sFeedtemplate='<div class="feed_addgrouptopic"><span class="feed_title">'.Q::L('发布了一篇帖子','Controller').'&nbsp;<a href="{@grouptopic_link}">'.$oGrouptopic['grouptopic_title'].'</a></span><div class="feed_content">{grouptopic_message}</div><div class="feed_action"><a href="{@grouptopic_link}#comments">'.Q::L('回复','Controller').'</a></div></div>';

		$arrFeeddata=array(
			'@grouptopic_link'=>$sFeedUrl,
			'grouptopic_message'=>Core_Extend::subString($_POST['grouptopic_content'],100,false,1,false),
		);

		try{
			Core_Extend::addFeed($sFeedtemplate,$arrFeeddata);
		}catch(Exception $e){
			$this->E($e->getMessage());
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

		// AJAX数据返回
		$arrData=$oGrouptopic->toArray();
		$sUrl=!$arrData['grouptopic_status']?Group_Extend::getGroupurl($oGroup):Group_Extend::getTopicurl($oGrouptopic);
		$arrData['url']=$sUrl;

		$this->A($arrData,Q::L('发布帖子成功','Controller'),1);
	}

	protected function cache_site_(){
		if(!Q::classExists('Cache_Extend')){
			require_once(Core_Extend::includeFile('function/Cache_Extend'));
		}
		Cache_Extend::updateCache("group_site");
		
		Core_Extend::updateOption(
			array(
				'group_topictodaynum'=>$GLOBALS['_cache_']['group_option']['group_topictodaynum']+1,
				'group_totaltodaynum'=>$GLOBALS['_cache_']['group_option']['group_totaltodaynum']+1
			),'group'
		);
	}

}
