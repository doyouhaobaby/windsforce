<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   群组相关函数($$)*/

!defined('Q_PATH') && exit;

class Group_Extend{

	public static function getGroupIcon($arrGroup,$bAdmin=false){
		if(empty($arrGroup['group_id'])){
			$arrGroup=Model::F_('group',(Core_Extend::isPostInt($arrGroup)?'group_id':'group_name').'=?',$arrGroup)
				->setColumns('group_id,group_icon,group_totaltodaynum,group_isopen')
				->getOne();
		}

		if(empty($arrGroup['group_id'])){
			return __ROOT__.'/System/app/group/Theme/Default/Public/Images/group_icon.gif';
		}
		
		if(!empty($arrGroup['group_icon'])){
			return __ROOT__.'/user/attachment/app/group/icon/'.$arrGroup['group_icon'];
		}else{
			if($bAdmin===false){
				if($arrGroup['group_totaltodaynum']>0){
					if($arrGroup['group_isopen']=='1'){
						return Appt::path('group_icon.gif','group');
					}else{
						return Appt::path('group_icon_lock.gif','group');
					}
				}else{
					if($arrGroup['group_isopen']=='1'){
						return Appt::path('group_icon_old.gif','group');
					}else{
						return Appt::path('group_icon_oldlock.gif','group');
					}
				}
			}else{
				return __ROOT__.'/System/app/group/Theme/Default/Public/Images/group_icon.gif';
			}
		}
	}

	public static function getGroupHeaderbg($sImgname){
		if(!empty($sImgname)){
			if(Core_Extend::isPostInt($sImgname)){
				return __ROOT__.'/System/app/group/Static/Images/groupbg/'.$sImgname.'.jpg';
			}else{
				return __ROOT__.'/user/attachment/app/group/icon/'.$sImgname;
			}
		}else{
			return __ROOT__.'/System/app/group/Static/Images/groupbg/1.jpg';
		}
	}

	public static function getIconName($sFilename){
		return Upload_Extend::getIconName('group',intval(Q::G('gid'))).'.'.C::getExtName($sFilename,2);
	}

	public static function getHeaderbgName($sFilename){
		return Upload_Extend::getIconName('group',intval(Q::G('gid')),'headerbg').'.'.C::getExtName($sFilename,2);
	}

	public static function getGroupurl($arrGroup,$sMore=''){
		return Q::U('group://gid@?id='.(!empty($arrGroup['group_name'])?$arrGroup['group_name']:$arrGroup['group_id']).$sMore);
	}

	public static function getTopicurl($arrGrouptopic,$bRouter=true,$bFull=true){
		$sUrl=($bRouter===true?'group://topic@?id='.$arrGrouptopic['grouptopic_id']:'group://grouptopic/view?id='.$arrGrouptopic['grouptopic_id']);
		
		if($bFull===true){
			return Q::U($sUrl);
		}else{
			return $sUrl;
		}
	}
	
	public static function getTopiccommenturl($arrGrouptopic,$nCommentId='',$bRouter=true,$bFull=true){
		$sUrl=($bRouter===true?'group://topic@?id='.$arrGrouptopic['grouptopic_id']:'group://grouptopic/view?id='.$arrGrouptopic['grouptopic_id']).
			'&isolation_commentid='.($nCommentId?$nCommentId:$arrGrouptopic['grouptopiccomment_id']);
		
		if($bFull===true){
			return Q::U($sUrl);
		}else{
			return $sUrl;
		}
	}

	public static function getGroupuser($nGroupId){
		// 取得用户是否加入了小组
		if($GLOBALS['___login___']!==FALSE){
			$arrGroupuser=array();
			$arrGroupuser=Model::F_('groupuser')
				->where(array('group_id'=>$nGroupId,'user_id'=>$GLOBALS['___login___']['user_id']))
				->getColumn('group_id',true);
			if(empty($arrGroupuser)){
				$arrGroupuser=array();
			}
		}else{
			$arrGroupuser=array();
		}

		return $arrGroupuser;
	}

	public static function chearGroupuserrole($nUserid){
		// 清理小组长
		$arrGroupusers=Model::F_('groupuser','groupuser_isadmin=2 AND user_id=?',$nUserid)->getAll();
		if(empty($arrGroupusers)){
			Q::instance('RoleModel')->delGroupUser(2,array($nUserid));
		}

		// 清理小组管理员
		$arrGroupusers=Model::F_('groupuser','groupuser_isadmin=1 AND user_id=?',$nUserid)->getAll();
		if(empty($arrGroupusers)){
			Q::instance('RoleModel')->delGroupUser(3,array($nUserid));
		}
	}

	public static function getGroup($sGroupId,$bId=false,$bReturnObj=false){
		$sField=$bId===false?(Core_Extend::isPostInt($sGroupId)?'group_id':'group_name'):'group_id';
		if($bReturnObj===false){
			return Model::F_('group','@A','A.'.$sField.'=? AND A.group_status=1',$sGroupId)
				->getOne();
		}else{
			return GroupModel::F($sField.'=? AND group_status=1',$sGroupId)->getOne();
		}
	}

}
