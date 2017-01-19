<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   添加新鲜事($$)*/

!defined('Q_PATH') && exit;

class Add_C_Controller extends InitController{

	public function index(){
		try{
			$arrData=array();

			$oLasthomefresh=HomefreshModel::F('user_id=?',$GLOBALS['___login___']['user_id'])->order('create_dateline DESC')->getOne();
			if(!empty($oLasthomefresh['homefresh_id'])){
				$arrData['lasttime']=$oLasthomefresh['create_dateline'];
			}
			
			Core_Extend::checkSpam($arrData);
		}catch(Exception $e){
			$this->E($e->getMessage());
		}

		// 禁止前台发布新鲜事
		if($GLOBALS['_cache_']['home_option']['homefresh_frontadd']==0 && !Core_Extend::isAdmin()){
			$this->E(Q::L('前台禁止发布新鲜事','Controller'));
		}
		
		if($GLOBALS['_option_']['seccode_publish_status']==1){
			$this->_oParent->check_seccode(true);
		}

		$sMessage=trim(C::cleanJs(Q::G('homefresh_message','P')));
		if(empty($sMessage)){
			$this->E(Q::L('新鲜事内容不能为空','Controller'));
		}

		// 解析新鲜事内容
		$sMessage=Core_Extend::replaceAttachment($sMessage);
		$arrParsemessage=Core_Extend::contentParsetag($sMessage);
		$sMessage=$arrParsemessage['content'];

		// 话题功能
		if(!empty($arrParsemessage['tags'])){
			foreach($arrParsemessage['tags'] as $sHomefreshtag){
				$oHomefreshtag=Q::instance('HomefreshtagModel');
				$oHomefreshtag->insertHomefreshtag($sHomefreshtag);
				if($oHomefreshtag->isError()){
					$this->E($oHomefreshtag->getErrorMessage());
				}
			}

			if(!Q::classExists('Cache_Extend')){
				require_once(Core_Extend::includeFile('function/Cache_Extend'));
			}
			Cache_Extend::updateCache('hottag');
			Cache_Extend::updateCache('hottagtop');
		}
		
		// 新鲜事模型
		$oHomefresh=new HomefreshModel();
		$nHomefreshType=intval(Q::G('homefresh_type'));
		if(in_array($nHomefreshType,array(2,4,5,6))){
			switch($nHomefreshType){
				case 2:
					$arrParseData=$oHomefresh->parseMusicString(trim(Q::G('urlmusic')));
					break;
				case 4:
					$arrParseData=$oHomefresh->parseVideoString(trim(Q::G('urlmusic')));
					break;
				case 5:
					$arrParseData=$oHomefresh->parseMovieString(Q::G('item'));
					break;
			}
			$oHomefresh->homefresh_attribute=serialize($arrParseData);
		}
		if(in_array($nHomefreshType,array(2,3,4,5,6))){
			$oHomefresh->homefresh_type=$nHomefreshType;
		}
		$oHomefresh->homefresh_message=$sMessage;

		// 新鲜事需要审核
		if($GLOBALS['_cache_']['home_option']['homefresh_audit']==1 && !Core_Extend::isAdmin()){
			$oHomefresh->homefresh_status=0;
		}else{
			$oHomefresh->homefresh_status=1;
		}
		$oHomefresh->save();
		if($oHomefresh->isError()){
			$this->E($oHomefresh->getErrorMessage());
		}else{
			$sFeedLink='home://fresh@?id='.$oHomefresh['homefresh_id'];
			
			// 发送feed
			$sFeedtemplate='<div class="feed_addhomefresh"><span class="feed_title">'.Q::L('发布了一条新鲜事','Controller').'&nbsp;<a href="{@homefresh_link}">'.Q::L('查看','Controller').'</a></span><div class="feed_content">{homefresh_message}</div><div class="feed_action"><a href="{@homefresh_link}#comments">'.Q::L('回复','Controller').'</a></div></div>';

			$arrFeeddata=array(
				'@homefresh_link'=>$sFeedLink,
				'homefresh_message'=>Core_Extend::subString($oHomefresh['homefresh_message'],100,false,1,false),
			);

			try{
				Core_Extend::addFeed($sFeedtemplate,$arrFeeddata);
			}catch(Exception $e){
				$this->E($e->getMessage());
			}

			// 发送提醒
			if($arrParsemessage['atuserids']){
				foreach($arrParsemessage['atuserids'] as $nAtuserid){
					if($nAtuserid!=$GLOBALS['___login___']['user_id']){
						$sHomefreshmessage=Core_Extend::subString($oHomefresh['homefresh_message'],100,false,1,false);
						
						$sNoticetemplate='<div class="notice_credit"><span class="notice_title"><a href="{@space_link}">{user_name}</a>&nbsp;'.Q::L('在新鲜事中提到了你','Controller').'</span><div class="notice_content"><div class="notice_quote"><span class="notice_quoteinfo">{content_message}</span></div></div><div class="notice_action"><a href="{@homefresh_link}">'.Q::L('查看','Controller').'</a></div></div>';

						$arrNoticedata=array(
							'@space_link'=>'home://space@?id='.$GLOBALS['___login___']['user_id'],
							'user_name'=>$GLOBALS['___login___']['user_name'],
							'@homefresh_link'=>$sFeedLink,
							'content_message'=>$sHomefreshmessage,
						);

						try{
							Core_Extend::addNotice($sNoticetemplate,$arrNoticedata,$nAtuserid,'athomefresh',$oHomefresh['homefresh_id']);
						}catch(Exception $e){
							$this->E($e->getMessage());
						}
					}
				}
			}

			if($GLOBALS['_cache_']['home_option']['homefresh_audit']==1 && !Core_Extend::isAdmin()){
				$arrHomefreshData['url']=Q::U('home://public/index');
				$sSuccessMessage=Q::L('你发布的新鲜事需要通过审核才能够显示','Controller');
			}else{
				$arrHomefreshData['url']=Q::U('home://fresh@?id='.$oHomefresh['homefresh_id']);
				$sSuccessMessage=Q::L('添加新鲜事成功','Controller');
			}

			$this->cache_site_();

			// 更新积分
			Core_Extend::updateCreditByAction('posthomefresh',$GLOBALS['___login___']['user_id']);
			Q::instance('UsercountModel')->increase(array($GLOBALS['___login___']['user_id']),array('usercount_homfresh'=>1));

			$this->A($arrHomefreshData,$sSuccessMessage,1);
		}
	}

	protected function cache_site_(){
		if(!Q::classExists('Cache_Extend')){
			require_once(Core_Extend::includeFile('function/Cache_Extend'));
		}
		Cache_Extend::updateCache("site");

		// 保存home今日数据
		Core_Extend::updateOption(
			array(
				'todayhomefreshnum'=>$GLOBALS['_option_']['todayhomefreshnum']+1,
				'todaytotalnum'=>$GLOBALS['_option_']['todaytotalnum']+1,
			)
		);
	}

}
