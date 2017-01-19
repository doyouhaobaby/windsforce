<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   短消息处理相关函数($$)*/

!defined('Q_PATH') && exit;

class Pm_Extend{

	static public function ubb($sContent){
		// 解析 [pm]*[/pm]
		$sContent=preg_replace("/\[pm\]\s*(\S+?)\s*\[\/pm\]/ise","self::getOnePm(\"\\1\")",$sContent);

		// 过滤处理
		$sContent=str_replace(array('[hr]','<br>'),array('<hr/>','<br/>'),$sContent);
		$sContent=nl2br($sContent);

		return $sContent;
	}

	static public function getOnePm($nPmId){
		$arrPm=Model::F_('pm','@A','A.pm_id=? AND A.pm_status=1',$nPmId)
			->setColumns('A.*')
			->joinLeft(Q::C('DB_PREFIX').'user AS B','B.user_name','A.pm_msgtoid=B.user_id')
			->query();
		if(empty($arrPm['pm_id'])){
			return '';
		}

		if(!in_array($GLOBALS['___login___']['user_id'],array($arrPm['pm_msgfromid'],$arrPm['pm_msgtoid']))){
			return '';
		}

		$sContent="<div class='reply-pm tips'>".
			"------------------ ".Q::L('原始短消息','__COMMON_LANG__@Common')." ------------------\r\n".
			"".Q::L('发送者','__COMMON_LANG__@Common').
			":&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"".Q::U('home://space@?id='.$arrPm['pm_msgfromid'])."\">{$arrPm['pm_msgfrom']}</a>\r\n".
			"".Q::L('发送时间','__COMMON_LANG__@Common').
			":&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".date('Y'.Q::L('年','__COMMON_LANG__@Common').
			'm'.Q::L('月','__COMMON_LANG__@Common').
			'd'.Q::L('日','__COMMON_LANG__@Common').
			' H:i',$arrPm['create_dateline'])."\r\n";

		$sPmUrl='';
		if($arrPm['pm_type']=='system'){
			$sPmUrl=Q::U('home://pm/show?id='.$arrPm['pm_id']);
		}elseif($arrPm['pm_msgfromid']==$GLOBALS['___login___']['user_id']){
			$sPmUrl=Q::U('home://pm/show?id='.$arrPm['pm_id'].'&muid='.$arrPm['pm_msgfromid']);
		}else{
			$sPmUrl=Q::U('home://pm/show?id='.$arrPm['pm_id'].'&uid='.$arrPm['pm_msgtoid']);
		}
		
		$sContent.="".Q::L('主题','__COMMON_LANG__@Common').
			":&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"{$sPmUrl}\">".
			($arrPm['pm_subject']?$arrPm['pm_subject']:Q::L('该短消息暂时没有主题','__COMMON_LANG__@Common'))."</a>\r\n";

		if($arrPm['pm_type']=='user'){
			$sContent.="".Q::L('收件人','__COMMON_LANG__@Common').
				":&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"".Q::U('home://space@?id='.$arrPm['pm_msgtoid'])."\">".
				$arrPm['user_name']."</a>\r\n";
		}else{
			$sContent.="<blockquote><em>".Q::L('本短消息属于系统短消息','__COMMON_LANG__@Common')."</em></blockquote>";
		}

		$sContent.="</div>";

		return $sContent;
	}

}
