<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   群组帖子相关函数($$)*/

!defined('Q_PATH') && exit;

class Grouptopic_Extend{

	static public function grouptopicClose($nClosestatus,$bReturnImg=false){
		if($nClosestatus==1){
			$sImgurl=Appt::path('locked.gif','group');
			if($bReturnImg===true){
				return ' <img class="grouptopicclose_date" src="'.$sImgurl.'" border="0" align="absmiddle" title="'.Q::L('关闭主题','Controller').'"/> ';
			}else{
				return $sImgurl;
			}
		}

		return '';
	}
	
	static public function grouptopicStick($nStickstatus,$bReturnImg=false){
		if($nStickstatus>0){
			$sImgurl=Appt::path('grouptopic/sticktopic_'.$nStickstatus.'.gif','group');
			if($bReturnImg===true){
				return ' <img class="grouptopicstick_date" src="'.$sImgurl.'" border="0" align="absmiddle" title="'.($nStickstatus==3?Q::L('全局置顶主题','Controller'):Q::L('小组置顶主题','Controller').' '.$nStickstatus).'"/> ';
			}else{
				return $sImgurl;
			}
		}

		return '';
	}

	static public function grouptopicDigest($nDigeststatus,$bReturnImg=false){
		if($nDigeststatus>0){
			$sImgurl=Appt::path('grouptopic/digest_'.$nDigeststatus.'.gif','group');
			if($bReturnImg===true){
				return ' <img class="grouptopicdigest_date" src="'.$sImgurl.'" border="0" align="absmiddle" title="'.Q::L('精华主题','Controller').' '.$nDigeststatus.'"/> ';
			}else{
				return $sImgurl;
			}
		}

		return '';
	}

	static public function grouptopicRecommend($nRecommendstatus,$bReturnImg=false){
		if($nRecommendstatus>0){
			$sImgurl=Appt::path('grouptopic/recommend_'.$nRecommendstatus.'.gif','group');
			if($bReturnImg===true){
				return ' <img class="grouptopicrecommend_date" src="'.$sImgurl.'" border="0" align="absmiddle" title="'.($nRecommendstatus==2?Q::L('系统推荐主题','Controller'):Q::L('组长推荐主题','Controller')).'"/> ';
			}else{
				return $sImgurl;
			}
		}

		return '';
	}

	static public function grouptopicThumb($nThumbstatus,$bReturnImg=false){
		if($nThumbstatus>0){
			$sImgurl=Appt::path('image.gif','group');
			if($bReturnImg===true){
				return ' <img class="grouptopicthumb_date" src="'.$sImgurl.'" border="0" align="absmiddle" title="'.Q::L('缩略图主题','Controller').'"/> ';
			}else{
				return $sImgurl;
			}
		}

		return '';
	}

	static public function grouptopicHot($nCommentnum,$nViewnum,$bReturnImg=false){
		$nHot=0;
		
		if($nCommentnum>=$GLOBALS['_cache_']['group_option']['group_hottopic3_comments'] && $nViewnum>=$GLOBALS['_cache_']['group_option']['group_hottopic3_views']){
			$nHot=3;
		}elseif($nCommentnum>=$GLOBALS['_cache_']['group_option']['group_hottopic2_comments'] && $nViewnum>=$GLOBALS['_cache_']['group_option']['group_hottopic2_views']){
			$nHot=2;
		}elseif($nCommentnum>=$GLOBALS['_cache_']['group_option']['group_hottopic1_comments'] && $nViewnum>=$GLOBALS['_cache_']['group_option']['group_hottopic1_views']){
			$nHot=1;
		}
		
		if($nHot>0){
			$sImgurl=Appt::path('hot_'.$nHot.'.gif','group');
			if($bReturnImg===true){
				return ' <img class="grouptopicthumb_date" src="'.$sImgurl.''.'" border="0" align="absmiddle" title="'.Q::L('热门主题','Controller').$nHot.'"/> ';
			}else{
				return $sImgurl;
			}
		}

		return '';
	}

	static public function grouptopicHighlight($sColor,$bReturnImg=false){
		if(!$sColor){
			return '';
		}

		$arrColor=@unserialize($sColor);
		if($arrColor){
			$sImgurl=Appt::path('highlight.gif','group');
			if($bReturnImg===true){
				return ' <img class="grouptopichighlight_date" src="'.$sImgurl.'" border="0" align="absmiddle" title="'.Q::L('高亮主题','Controller').'"/> ';
			}else{
				return $sImgurl;
			}
		}else{
			return '';
		}
	}

	static public function grouptopiclistIcon($arrGrouptopic){
		$sGroupurl=Group_Extend::getTopicurl($arrGrouptopic);

		$sTitle=Q::L('新窗口打开','Controller');
		$sIcon=Appt::path('folder_common.gif','group');

		if($arrGrouptopic['grouptopic_comments']>0 && $arrGrouptopic['grouptopic_latestcomment']){
			$arrLatestComment=json_decode($arrGrouptopic['grouptopic_latestcomment'],true);
			if(CURRENT_TIMESTAMP-$arrLatestComment['commenttime']<=86400){
				$sIcon=Appt::path('folder_new.gif','group');
				$sTitle=Q::L('有新回复','Controller').' - '.$sTitle;
			}
		}
		
		if($arrGrouptopic['grouptopic_sticktopic']>0){
			$sIcon=Appt::path('grouptopic/sticktopic_'.$arrGrouptopic['grouptopic_sticktopic'].'.gif','group');
			$sTitle=($arrGrouptopic['grouptopic_sticktopic']==3?Q::L('全局置顶主题','Controller'):Q::L('小组置顶主题','Controller').' '.$arrGrouptopic['grouptopic_sticktopic']).' - '.$sTitle;
		}

		if($arrGrouptopic['grouptopic_isclose']==1){
			$sIcon=Appt::path('locked.gif','group');
			$sTitle=Q::L('关闭的主题','Controller').' - '.$sTitle;
		}

		return '<a href="'.$sGroupurl.'" title="'.$sTitle.'" target="_blank"><img src="'.$sIcon.'" style="position:relative;top:-2px;"/></a>';
	}

	static public function grouptopicColor($sColor){
		if(!$sColor){
			return '';
		}

		$arrColor=@unserialize($sColor);
		if($arrColor){
			$sReturn='';

			if(!empty($arrColor[0])){
				$sReturn.='color:'.$arrColor[0].';';
			}

			if(!empty($arrColor[1][1])){
				$sReturn.="font-weight: bold;";
			}

			if(!empty($arrColor[1][2])){
				$sReturn.="font-style: italic;";
			}

			if(!empty($arrColor[1][3])){
				$sReturn.="text-decoration: underline;";
			}

			if(!empty($arrColor[2])){
				$sReturn.='background-color:'.$arrColor[2].';';
			}

			return $sReturn;
		}
	}

}
