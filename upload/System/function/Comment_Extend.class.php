<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   通用评论检测相关函数($$)*/

!defined('Q_PATH') && exit;

/** 载入home应用配置信息 */
if(!isset($GLOBALS['_cache_']['home_option'])){
	if(!Q::classExists('HomeoptionModel')){
		require_once(WINDSFORCE_PATH.'/System/app/home/App/Class/Model/HomeoptionModel.class.php');
	}

	Core_Extend::loadCache('home_option');
}

class Comment_Extend{

	static public function banIp($sOnlineip=null){
		$sCommentBanIp=trim($GLOBALS['_cache_']['home_option']['comment_ban_ip']);
		if(is_null($sOnlineip)){
			$sOnlineip=C::getIp();
		}

		if($GLOBALS['_cache_']['home_option']['comment_banip_enable']==1 && $sCommentBanIp){
			$sCommentBanIp=str_replace('，',',',$sCommentBanIp);
			$arrCommentBanIp=Q::normalize(explode(',', $sCommentBanIp));
			if(is_array($arrCommentBanIp) && count($arrCommentBanIp)){
				foreach($arrCommentBanIp as $sValueCommentBanIp){
					$sValueCommentBanIp=str_replace('\*','.*',preg_quote($sValueCommentBanIp,"/"));
					if(preg_match("/^{$sValueCommentBanIp}/",$sOnlineip)){
						return false;
					}
				}
			}
		}

		return true;
	}

	static public function commentName($sCommentName){
		$arrNamekeys=array("\\",'&',' ',"'",'"','/','*',',','<','>',"\r","\t","\n",'#','$','(',')','%','@','+','?',';','^');
		
		foreach($arrNamekeys as $sNamekey){
			if(strpos($sCommentName,$sNamekey)!==false){
				return false;
			}
		}

		return true;
	}

	static public function commentMinLen($sCommentContent){
		$nCommentMinLen=intval($GLOBALS['_cache_']['home_option']);
		if($nCommentMinLen && strlen($sCommentContent)<$nCommentMinLen){
			return false;
		}

		return true;
	}

	static public function commentMaxLen($sCommentContent,$nCommentMaxLen=0){
		if($nCommentMaxLen==0){
			$nCommentMaxLen=intval($GLOBALS['_cache_']['home_option']['comment_max_len']);
		}
		
		if($nCommentMaxLen && strlen($sCommentContent)>$nCommentMaxLen){
			return false;
		}

		return true;
	}

	static public function commentSpamUrl($sCommentContent){
		$bDisallowedSpamWordToDatabase=$GLOBALS['_cache_']['home_option']['disallowed_spam_word_to_database']?true:false;
		if($GLOBALS['_cache_']['home_option']['comment_spam_enable']){
			$nCommentSpamUrlNum=intval($GLOBALS['_cache_']['home_option']['comment_spam_url_num']);
			if($nCommentSpamUrlNum){
				if(substr_count($sCommentContent,'http://')>=$nCommentSpamUrlNum){
					if($bDisallowedSpamWordToDatabase){
						return false;
					}else{
						return 0;
					}
				}
			}
		}

		return true;
	}

	static public function commentSpamWords($sCommentContent){
		$bDisallowedSpamWordToDatabase=$GLOBALS['_cache_']['home_option']['disallowed_spam_word_to_database']?true:false;
		if($GLOBALS['_cache_']['home_option']['comment_spam_enable']){
			$sSpamWords=trim($GLOBALS['_cache_']['home_option']['comment_spam_words']);
			if($sSpamWords){
				$sSpamWords=str_replace('，',',',$sSpamWords);
				$arrSpamWords=Q::normalize(explode(',',$sSpamWords));
				if(is_array($arrSpamWords) && count($arrSpamWords)){
					foreach($arrSpamWords as $sValueSpamWords){
						if($sValueSpamWords){
							if(preg_match("/".preg_quote($sValueSpamWords,'/')."/i",$sCommentContent)){
								if($bDisallowedSpamWordToDatabase){
									return array(false,$sValueSpamWords);
								}else{
									return 0;
								}
								break;
							}
						}
					}
				}
			}
		}

		return true;
	}

	static public function commentSpamContentsize($sCommentContent,$nCommentSpamContentSize=0){
		if($nCommentSpamContentSize==0){
			$nCommentSpamContentSize=intval($GLOBALS['_cache_']['home_option']['comment_spam_content_size']);
		}

		$bDisallowedSpamWordToDatabase=$GLOBALS['_cache_']['home_option']['disallowed_spam_word_to_database']?true:false;
		if($GLOBALS['_cache_']['home_option']['comment_spam_enable']){
			if($nCommentSpamContentSize){
				if(strlen($sCommentContent)>=$nCommentSpamContentSize){
					if($bDisallowedSpamWordToDatabase){
						return false;
					}else{
						return 0;
					}
				}
			}

			return true;
		}
	}

	static public function commentSpamPostSpace($nLastPostTime){
		return !(CURRENT_TIMESTAMP-$nLastPostTime<=$GLOBALS['_cache_']['home_option']['comment_post_space']);
	}

	static public function commentDisallowedallenglishword($sCommentContent){
		$bDisallowedSpamWordToDatabase=$GLOBALS['_cache_']['home_option']['disallowed_spam_word_to_database']?true:false;
		if($GLOBALS['_cache_']['home_option']['disallowed_all_english_word']){
			$sPattern='/[一-龥]/u';
			if(!preg_match_all($sPattern,$sCommentContent,$arrMatch)){
				if($bDisallowedSpamWordToDatabase){
					return false;
				}else{
					return 0;
				}
			}
		}

		return true;
	}

	static public function addFeed($sTitle,$sType,$sCommentLink,$sCommentTitle,$sCommentMessage,$sFileinfo=''){
		$sFeedtemplate='<div class="feed_'.$sType.'"><span class="feed_title">'.$sTitle.'&nbsp;<a href="{@commentlink}">{commenttitle}</a></span><div class="feed_content">'.$sFileinfo.'<div class="feed_quote"><span class="feed_quoteinfo">{commentmessage}</span></div></div><div class="feed_action"><a href="{@commentlink}">'.Q::L('回复','__COMMON_LANG__@Common').'</a></div></div>';

		$arrFeeddata=array(
			'@commentlink'=>$sCommentLink,
			'commenttitle'=>Core_Extend::subString(strip_tags($sCommentTitle),30,false,0,false),
			'commentmessage'=>Core_Extend::subString($sCommentMessage,100,false,1,false),
		);

		Core_Extend::addFeed($sFeedtemplate,$arrFeeddata);
	}

	static public function addNotice($sTitle,$sType,$sCommentLink,$sCommentTitle,$sCommentMessage,$nUserid,$sNoticeType,$nFromId,$sFileinfo=''){
		$sNoticetemplate='<div class="notice_'.$sType.'"><span class="notice_title"><a href="{@space_link}">{user_name}</a>&nbsp;'.$sTitle.'&nbsp;<a href="{@commentlink}">{commenttitle}</a></span><div class="notice_content">'.$sFileinfo.'<div class="notice_quote"><span class="notice_quoteinfo">{commentmessage}</span></div></div><div class="notice_action"><a href="{@commentlink}">'.Q::L('查看','__COMMON_LANG__@Common').'</a></div></div>';

		$arrNoticedata=array(
			'@space_link'=>'home://space@?id='.$GLOBALS['___login___']['user_id'],
			'user_name'=>$GLOBALS['___login___']['user_name'],
			'@commentlink'=>$sCommentLink,
			'commenttitle'=>Core_Extend::subString(strip_tags($sCommentTitle),30,false,0,false),
			'commentmessage'=>Core_Extend::subString($sCommentMessage,100,false,1,false),
		);

		Core_Extend::addNotice($sNoticetemplate,$arrNoticedata,$nUserid,$sNoticeType,$nFromId);
	}

}
