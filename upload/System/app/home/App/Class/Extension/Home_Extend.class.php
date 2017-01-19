<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   全局函数库文件($$)*/

!defined('Q_PATH') && exit;

class Home_Extend{

	public static function getHomefreshLink($arrHomefresh){
		return Q::U('home://fresh@?id='.$arrHomefresh['homefresh_id']);
	}
	
	public static function getOnlinedata(){
		$oDb=Db::RUN();

		// 查询在线统计
		$arrOnline=$oDb->getAllRows("SELECT k,COUNT(0) As countnum FROM(SELECT CASE WHEN user_id=0 THEN 'eq0' WHEN online_isstealth=1 THEN 'neq0s' ELSE 'neq0' END AS k FROM `".Q::C('DB_PREFIX')."online`) AS temptable GROUP BY k");

		$nOnlineAllnum=$nOnlineGuestnum=$nOnlineUsernum=$nOnlineStealthusernum=0;

		foreach($arrOnline as $arrTemp){
			if($arrTemp['k']=='eq0'){
				$nOnlineGuestnum=intval($arrTemp['countnum']);
			}elseif($arrTemp['k']=='neq0s'){
				$nOnlineStealthusernum=intval($arrTemp['countnum']);
			}elseif($arrTemp['k']=='neq0'){
				$nOnlineUsernum=intval($arrTemp['countnum']);
			}
		}

		$nOnlineUsernum+=$nOnlineStealthusernum;
		$nOnlineAllnum=$nOnlineGuestnum+$nOnlineUsernum;

		// 保存最大在线用户数量
		if($nOnlineAllnum>$GLOBALS['_option_']['online_totalmostnum']){
			Core_Extend::updateOption(
				array(
					'online_totalmostnum'=>$nOnlineAllnum,
					'online_mosttime'=>CURRENT_TIMESTAMP
				)
			);
		}

		// 首页的统计数据
		$arrOnlinedata['online_allnum']=$nOnlineAllnum;
		$arrOnlinedata['online_guestnum']=$nOnlineGuestnum;
		$arrOnlinedata['online_usernum']=$nOnlineUsernum;
		$arrOnlinedata['online_stealthusernum']=$nOnlineStealthusernum;
		$arrOnlinedata['online_totalmostnum']=$GLOBALS['_option_']['online_totalmostnum'];
		$arrOnlinedata['online_mosttime']=date('Y-m-d',$GLOBALS['_option_']['online_mosttime']);

		return $arrOnlinedata;
	}

	public static function getNewcomment($nId){
		return Model::F_('homefreshcomment','@A',
				'A.homefresh_id=? AND A.homefreshcomment_status=1 AND A.homefreshcomment_parentid=0',$nId
				)->setColumns('A.homefreshcomment_id,A.create_dateline,A.user_id,A.homefreshcomment_name,A.homefreshcomment_content,A.homefresh_id,A.homefreshcomment_parentid')
				->join(Q::C('DB_PREFIX').'user AS B','B.user_name','A.user_id=B.user_id')
				->order('A.homefreshcomment_id DESC')
				->limit(0,$GLOBALS['_cache_']['home_option']['homefreshcomment_limit_num'])
				->asArray()
				->getAll();
	}

	public static function getNewchildcomment($nId,$nCommentid,$bAll=false,$nCommentpage=1){
		$oHomefreshcommentSelect=Model::F_('homefreshcomment','@A',
			'A.homefresh_id=? AND A.homefreshcomment_status=1 AND A.homefreshcomment_parentid=?',$nId,$nCommentid
			)->order('A.homefreshcomment_id DESC');

		$sJoinUser=Q::C('DB_PREFIX').'user AS B';
		if($bAll===true){
			if($nCommentpage<1){
				$nCommentpage=1;
			}

			$nTotalHomefreshcommentNum=$oHomefreshcommentSelect->All()->getCounts();
			if($nTotalHomefreshcommentNum>0){
				$oPage=Page::RUN($nTotalHomefreshcommentNum,$GLOBALS['_cache_']['home_option']['homefreshchildcomment_list_num'],'','',$nCommentpage,false);
				$arrHomefreshcomments=Model::F_('homefreshcomment','@A',
				'A.homefresh_id=? AND A.homefreshcomment_status=1 AND A.homefreshcomment_parentid=?',$nId,$nCommentid
				)->setColumns('A.homefreshcomment_id,A.create_dateline,A.user_id,A.homefreshcomment_name,A.homefreshcomment_content,A.homefresh_id,A.homefreshcomment_parentid')
				->join($sJoinUser,'B.user_name','A.user_id=B.user_id')
				->order('A.homefreshcomment_id DESC')
				->limit($oPage->S(),$oPage->N())
				->asArray()
				->getAll();

				return array($arrHomefreshcomments,$oPage->P(array('id'=>'pagination_'.$nCommentid.'@pagenav'),'commentpage_'.$nCommentid),$nTotalHomefreshcommentNum<$GLOBALS['_cache_']['home_option']['homefreshchildcomment_list_num']?false:true);
			}else{
				return array('','',false);
			}
		}else{
			return $oHomefreshcommentSelect->limit(0,$GLOBALS['_cache_']['home_option']['homefreshchildcomment_limit_num'])
				->setColumns('A.homefreshcomment_id,A.create_dateline,A.user_id,A.homefreshcomment_name,A.homefreshcomment_content,A.homefresh_id,A.homefreshcomment_parentid')
				->join($sJoinUser,'B.user_name','A.user_id=B.user_id')
				->order('A.homefreshcomment_id DESC')
				->asArray()
				->getAll();
		}
	}

	public static function parse($sAttribute,$nType,$nHomefreshid){
		if(!in_array($nType,array(2,4,5,6)) || empty($sAttribute)){
			return '';
		}
		$sReturn=call_user_func(array('Home_Extend','parseData'.$nType.'_'),unserialize($sAttribute),$nHomefreshid);
		return $sReturn;
	}

	/**
	 * 解析音乐模板
	 */
	protected static function parseData2_($arrAttribute,$nHomefreshid){
		$sHtml='';

		foreach($arrAttribute as $nKey=>$arrVal){
			if($arrVal['type']=='xiamisearch'){
				$sLang1=Q::L('弹出播放','Controller');
				
				$sHtml.=<<<WINDSFORCE
<div class="album" id="xiamisearch-item-{$nHomefreshid}-{$nKey}">
	<div class="cover">
		<div class="cover_img"></div>
		<img src="{$arrVal['img']}">
	</div>
	<div class="cover_title">
		<a href="{$arrVal['url']}" target="_blank">{$arrVal['title']}</a>
	</div>
	<div class="cover_fun">
		<a target="_blank" href="{$arrVal['url']}"><span class="eject">{$sLang1}</span></a>
	</div>
	<embed width="340" height="33" wmode="transparent" type="application/x-shockwave-flash" src="{$arrVal['pid']}">
</div>
<div class="clearfix"></div>
WINDSFORCE;
			}elseif($arrVal['type']=='yinyuetai'){
				$sHtml.=<<<WINDSFORCE
<div id="swf_cover_{$nHomefreshid}_{$nKey}" class="video w240">
	<div class="video_bg">
		<div class="video_song">{$arrVal['title']}</div>
		<div class="video_name">{$arrVal['author']}</div>
		<a onclick="loadSwf('yinyuetai',{$nHomefreshid},{$nKey},'{$arrVal['title']}','{$arrVal['pid']}')" href="javascript:">
			<div class="video_play"></div>
			<img src="{$arrVal['img']}">
		</a>
	</div>
</div>
<div id="swf_play_{$nHomefreshid}_{$nKey}" style="display:none"></div>
<div class="clearfix"></div>
WINDSFORCE;
			}
		}

		return $sHtml;
	}

	/**
	 * 解析视频模板
	 */
	protected static function parseData4_($arrAttribute,$nHomefreshid){
		$sHtml='';

		foreach($arrAttribute as $nKey=>$arrVal){
			$sHtml.=<<<WINDSFORCE
<div id="swf_cover_{$nHomefreshid}_{$nKey}" class="video w240">
	<div class="video_bg">
		<div class="video_title">{$arrVal['title']}</div>
		<a onclick="loadSwf('{$arrVal['type']}video',{$nHomefreshid},{$nKey},'{$arrVal['title']}','{$arrVal['pid']}')" href="javascript:;">
			<div class="video_play"></div>
			<img src="{$arrVal['img']}">
		</a>
	</div>
</div>
<div id="swf_play_{$nHomefreshid}_{$nKey}" style="display:none"></div>
<div class="clearfix"></div>
WINDSFORCE;
		}

		return $sHtml;
	}

	/**
	 * 解析电影模板
	 */
	protected static function parseData5_($arrAttribute,$nHomefreshid){
		$sHtml='';

		foreach($arrAttribute as $nKey=>$arrVal){
			$sLang1=Q::L('电影名称','Controller');
			$sLang2=Q::L('导演','Controller');
			$sLang3=Q::L('主演','Controller');
			$sLang4=Q::L('类型','Controller');
			$sLang5=Q::L('上映日期','Controller');
			$sLang6=Q::L('时长','Controller');

			$sStarringHtml='';
			$arrTemp=array();
			if(is_array($arrVal['starring'])){
				foreach($arrVal['starring'] as $sValue){
					$arrTemp[]="<a href=\"http://movie.douban.com/search/".urlencode($sValue)."\" target=\"_blank\">{$sValue}</a>";
				}
			}
			
			$sStarringHtml=implode(' / ',$arrTemp);
			
			$sGenreHtml=implode(' / ',$arrVal['genre']);

			$sHtml.=<<<WINDSFORCE
<div class="movie">
	<div class="mov_img">
		<div class="score">{$arrVal['average']}</div>
		<div class="mov_yy"></div>
		<a target="_blank" href="{$arrVal['url']}"><img src="{$arrVal['img']}"></a>
	</div>
	<div class="mov_info">
		<li class="mov_title">{$sLang1}:<font>{$arrVal['title']}</font></li>
		<li>{$sLang2}:<font>{$arrVal['directe']}</font></li>
		<li>{$sLang3}:<font>{$sStarringHtml}</font></li>
		<li>{$sLang4}:<font>{$sGenreHtml}</font></li>
		<li>{$sLang5}:<font>{$arrVal['initialReleaseDate']}</font></li>
		<li>{$sLang6}:<font>{$arrVal['runtime']}</font></li>
	</div>
	<div class="clearfix"></div>
</div>
WINDSFORCE;
		}

		return $sHtml;
	}

	public static function getProgress(){
		if(ACTION_NAME==='index'){
			return 25;
		}elseif(ACTION_NAME==='step2'){
			return 50;
		}elseif(ACTION_NAME==='step3'){
			return 75;
		}elseif(ACTION_NAME==='step4'){
			return 100;
		}

		return 0;
	}

	public static function scheduleProgress($nProgress){
		if($nProgress==0){
			return 33;
		}elseif($nProgress==1){
			return 66;
		}elseif($nProgress==2 || $nProgress==3){
			return 100;
		}

		return 0;
	}

	static public function checkHomefreshedit($arrHomefresh){
		if(Core_Extend::isAdmin()){
			return true;
		}
		
		if($GLOBALS['___login___']!==false && $GLOBALS['___login___']['user_id']==$arrHomefresh['user_id'] && 
			CURRENT_TIMESTAMP-$arrHomefresh['create_dateline']<=$GLOBALS['_cache_']['home_option']['homefresh_edit_limittime']){
			return true;
		}

		return false;
	}

}
