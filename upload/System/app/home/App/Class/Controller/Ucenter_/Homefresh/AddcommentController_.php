<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   添加评论($$)*/

!defined('Q_PATH') && exit;

/** 导入通用评论检测相关函数 */
require_once(Core_Extend::includeFile('function/Comment_Extend'));

class Addcomment_C_Controller extends InitController{

	public function index(){
		try{
			Core_Extend::checkSpam();
		}catch(Exception $e){
			$this->E($e->getMessage());
		}

		// 读取新鲜事数据
		$oHomefresh=HomefreshModel::F('homefresh_id=?',intval(Q::G('homefresh_id')))->getOne();
		if(empty($oHomefresh['homefresh_id'])){
			$this->E(Q::L('新鲜事不存在','Controller'));
		}

		$arrOptions=$GLOBALS['_cache_']['home_option'];
		if($arrOptions['close_comment_feature']==1){
			$this->E(Q::L('系统关闭了评论功能','__COMMON_LANG__@Common'));
		}

		if($arrOptions['seccode_comment_status']==1){
			$this->_oParent->check_seccode(true);
		}

		// IP禁止功能
		$sOnlineip=C::getIp();
		if(!Comment_Extend::banIp($sOnlineip)){
			$this->E(Q::L('您的IP %s 已经被系统禁止发表评论','__COMMON_LANG__@Common',null,$sOnlineip));
		}

		// 评论名字检测
		$sCommentName=trim(Q::G('homefreshcomment_name'));
		if(empty($sCommentName)){
			$this->E(Q::L('评论名字不能为空','__COMMON_LANG__@Common'));
		}

		if(!Comment_Extend::commentName($sCommentName)){
			$this->E(Q::L('此评论名字包含不可接受字符或被管理员屏蔽,请选择其它名字','__COMMON_LANG__@Common'));
		}

		// 评论内容长度检测
		$sCommentContent=C::cleanJs(strip_tags(trim(Q::G('homefreshcomment_content'))));
		$nCommentMinLen=intval($arrOptions['comment_min_len']);
		if(!Comment_Extend::commentMinLen($sCommentContent)){
			$this->E(Q::L('评论内容最少的字节数为 %d','__COMMON_LANG__@Common',null,$nCommentMinLen));
		}

		$nCommentMaxLen=intval($arrOptions['comment_max_len']);
		if(!Comment_Extend::commentMaxLen($sCommentContent)){
			$this->E(Q::L('评论内容最大的字节数为 %d','__COMMON_LANG__@Common',null,$nCommentMaxLen));
		}

		// 创建评论模型
		$oHomefreshcomment=new HomefreshcommentModel();

		// SPAM 垃圾信息阻止: URL数量限制
		$result=Comment_Extend::commentSpamUrl($sCommentContent);
		if($result===false){
			$nCommentSpamUrlNum=intval($arrOptions['comment_spam_url_num']);
			$this->E(Q::L('评论内容中出现的链接数量超过了系统的限制 %d 条','__COMMON_LANG__@Common',null,$nCommentSpamUrlNum));
		}
		if($result===0){
			$oHomefreshcomment->homefreshcomment_status=0;
		}

		// SPAM 垃圾信息阻止: 屏蔽字符检测
		$result=Comment_Extend::commentSpamWords($sCommentContent);
		if($result===false){
			if(is_array($result)){
				$this->E(Q::L("你的评论内容包含系统屏蔽的词语%s",'__COMMON_LANG__@Common',null,$result[1]));
			}
		}
		if($result===0){
			$oHomefreshcomment->homefreshcomment_status=0;
		}

		// SPAM 垃圾信息阻止: 评论内容长度限制
		$result=Comment_Extend::commentSpamContentsize($sCommentContent);
		if($result===false){
			$nCommentSpamContentSize=intval($arrOptions['comment_spam_content_size']);
			$this->E(Q::L('评论内容最大的字节数为%d','__COMMON_LANG__@Common',null,$nCommentSpamContentSize));
		}
		if($result===0){
			$oHomefreshcomment->homefreshcomment_status=0;
		}

		// 发表评论间隔时间
		$nCommentPostSpace=intval($arrOptions['comment_post_space']);
		if($nCommentPostSpace){
			$oUserLasthomefreshcomment=HomefreshcommentModel::F('user_id=?',$GLOBALS['___login___']['user_id'])->order('homefreshcomment_id DESC')->getOne();
			if(!empty($oUserLasthomefreshcomment['homefreshcomment_id'])){
				$nLastPostTime=$oUserLasthomefreshcomment['create_dateline'];
				if(!Comment_Extend::commentSpamPostSpace($nLastPostTime)){
					$this->E(Q::L('为防止灌水,发表评论时间间隔为 %d 秒','__COMMON_LANG__@Common',null,$nCommentPostSpace));
				}
			}
		}

		// 评论重复检测
		if($arrOptions['comment_repeat_check']){
			$nCurrentTimeStamp=CURRENT_TIMESTAMP;
			$oTryComment=HomefreshcommentModel::F("homefreshcomment_name=? AND homefreshcomment_content=? AND {$nCurrentTimeStamp}-create_dateline<86400 AND homefreshcomment_ip=?",$sCommentName,$sCommentContent,$sOnlineip)->order('homefreshcomment_id DESC')->query();
			if(!empty($oTryComment['homefreshcomment_id'])){
				$this->E(Q::L('你提交的评论已经存在,24小时之内不允许出现相同的评论','__COMMON_LANG__@Common'));
			}
		}

		// 纯英文评论阻止
		$result=Comment_Extend::commentDisallowedallenglishword($sCommentContent);
		if($result===false){
			$this->E('You should type some Chinese word(like 你好)in your comment to pass the spam-check, thanks for your patience! '.Q::L('您的评论中必须包含汉字','__COMMON_LANG__@Common'));
		}
		if($result===0){
			$oHomefreshcomment->homefreshcomment_status=0;
		}

		// 评论审核
		if($arrOptions['audit_comment']==1){
			$oHomefreshcomment->homefreshcomment_status=0;
		}

		// 保存评论数据
		$_POST=array_merge($_POST,$_GET);
		$arrParsecontent=Core_Extend::contentParsetag($sCommentContent);
		$sCommentContent=$arrParsecontent['content'];
		$oHomefreshcomment->homefreshcomment_content=$sCommentContent;
		$oHomefreshcomment->homefresh_id=intval(Q::G('homefresh_id'));
		$oHomefreshcomment->save('create');
		if($oHomefreshcomment->isError()){
			$this->E($oHomefreshcomment->getErrorMessage());
		}else{
			// 更新评论数量
			Q::instance('HomefreshModel')->updateHomefreshcommentnum(intval(Q::G('homefresh_id')));

			// 发送feed
			$sCommentLink='home://fresh@?id='.$oHomefresh['homefresh_id'].'&isolation_commentid='.$oHomefreshcomment['homefreshcomment_id'];
			$sCommentTitle=$oHomefresh['homefresh_title']?$oHomefresh['homefresh_title']:strip_tags($oHomefresh['homefresh_message']);
			$sCommentTitle=$sCommentTitle;
			$sCommentMessage=$oHomefreshcomment['homefreshcomment_content'];

			try{
				Comment_Extend::addFeed(Q::L('评论了新鲜事','Controller'),'addhomefreshcomment',$sCommentLink,$sCommentTitle,$sCommentMessage);
			}catch(Exception $e){
				$this->E($e->getMessage());
			}

			// 发送提醒
			if($oHomefresh['user_id']!=$GLOBALS['___login___']['user_id']){
				$sCommentTitle=$oHomefresh['homefresh_title']?$oHomefresh['homefresh_title']:strip_tags($oHomefresh['homefresh_message']);
				$sCommentMessage=$oHomefreshcomment['homefreshcomment_content'];

				try{
					Comment_Extend::addNotice(Q::L('评论了你的新鲜事','Controller'),'addhomefreshcomment',$sCommentLink,$sCommentTitle,$sCommentMessage,$oHomefresh['user_id'],'homefreshcomment',$oHomefresh['homefresh_id']);
				}catch(Exception $e){
					$this->E($e->getMessage());
				}
			}

			// 发送评论被回复提醒
			if($oHomefreshcomment['homefreshcomment_parentid']>0){
				$oHomefreshcommentParent=HomefreshcommentModel::F('homefreshcomment_id=?',$oHomefreshcomment['homefreshcomment_parentid'])->getOne();

				if(!empty($oHomefreshcommentParent['homefreshcomment_id']) && $oHomefreshcommentParent['user_id']!=$GLOBALS['___login___']['user_id']){
					$sCommentTitle=$oHomefreshcomment['homefreshcomment_content'];
					$sCommentMessage=$oHomefreshcomment['homefreshcomment_content'];

					try{
						Comment_Extend::addNotice(Q::L('回复了你的评论','Controller'),'replyhomefreshcomment',$sCommentLink,$sCommentTitle,$sCommentMessage,$oHomefreshcommentParent['user_id'],'replyhomefreshcomment',$oHomefreshcommentParent['homefreshcomment_id']);
					}catch(Exception $e){
						$this->E($e->getMessage());
					}
				}
			}

			// 发送评论提醒
			if($arrParsecontent['atuserids']){
				foreach($arrParsecontent['atuserids'] as $nAtuserid){
					if($nAtuserid!=$GLOBALS['___login___']['user_id']){
						$sHomefreshcommentmessage=Core_Extend::subString($oHomefreshcomment['homefreshcomment_content'],100,false,1,false);
						
						$sNoticetemplate='<div class="notice_athomefreshcomment"><span class="notice_title"><a href="{@space_link}">{user_name}</a>&nbsp;'.Q::L('在新鲜事评论中提到了你','Controller').'</span><div class="notice_content"><div class="notice_quote"><span class="notice_quoteinfo">{content_message}</span></div></div><div class="notice_action"><a href="{@homefreshcomment_link}">'.Q::L('查看','Controller').'</a></div></div>';

						$arrNoticedata=array(
							'@space_link'=>'home://space@?id='.$GLOBALS['___login___']['user_id'],
							'user_name'=>$GLOBALS['___login___']['user_name'],
							'@homefreshcomment_link'=>$sCommentLink,
							'content_message'=>$sHomefreshcommentmessage,
						);

						try{
							Core_Extend::addNotice($sNoticetemplate,$arrNoticedata,$nAtuserid,'athomefreshcomment',$oHomefreshcomment['homefreshcomment_id']);
						}catch(Exception $e){
							$this->E($e->getMessage());
						}
					}
				}
			}
		}

		$arrCommentData=$oHomefreshcomment->toArray();

		$nQuick=intval(Q::G('quick','G'));
		if($nQuick==1){
			$arrCommentData['homefreshcomment_content']=nl2br(Core_Extend::ubb($arrCommentData['homefreshcomment_content'],false));
			$arrCommentData['comment_name']=Core_Extend::getUsernameById($oHomefreshcomment->user_id);
			$arrCommentData['create_dateline']=Core_Extend::timeFormat($arrCommentData['create_dateline']);
			$arrCommentData['avatar']=Core_Extend::avatar($arrCommentData['user_id'],'small');
			$arrCommentData['url']=Q::U('home://space@?id='.$arrCommentData['user_id']);
			$arrCommentData['num']=$oHomefresh->homefresh_commentnum;
			$arrCommentData['viewurl']=Q::U('home://fresh@?id='.$arrCommentData['homefresh_id'].'&isolation_commentid='.$arrCommentData['homefreshcomment_id']);
			$arrCommentData['usericon']=Core_Extend::getUsericon($arrCommentData['user_id']);
		}else{
			$arrCommentData['jumpurl']=Q::U('home://fresh@?id='.$arrCommentData['homefresh_id'].'&isolation_commentid='.$arrCommentData['homefreshcomment_id']).
				'#comment-'.$oHomefreshcomment['homefreshcomment_id'];
		}

		// 更新积分
		Core_Extend::updateCreditByAction('commoncomment',$GLOBALS['___login___']['user_id']);

		$this->A($arrCommentData,Q::L('添加新鲜事评论成功','Controller'),1);
	}

}
