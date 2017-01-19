<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   群组管理相关函数($$)*/

!defined('Q_PATH') && exit;

class Groupadmin_Extend{
	
	static public function getGroupuser($nGroupid,$nUserid=0){
		if($nUserid>0){
			$nGroupuser=self::isGroupuser($nGroupid,$nUserid);
		}else{
			if($GLOBALS['___login___']===false){
				$nGroupuser=0;
			}else{
				$nGroupuser=self::isGroupuser($nGroupid,$GLOBALS['___login___']['user_id']);
			}
		}
		return $nGroupuser;
	}

	static public function getGroupUserrole($nGroupid){
		/* 4 超级管理员 -1 游客 -2 非成员 0 会员 1 小组管理员 2 组长 */
		if($GLOBALS['___login___']===false){
			return -1;
		}
		
		if(Core_Extend::isAdmin()){
			return 4;
		}

		$arrTrygroupuser=Model::F_('groupuser','user_id=? AND group_id=?',$GLOBALS['___login___']['user_id'],$nGroupid)
			->setColumns('user_id,groupuser_isadmin')
			->getOne();
		if(empty($arrTrygroupuser['user_id'])){
			return -2;
		}else{
			return $arrTrygroupuser['groupuser_isadmin'];
		}
	}

	static public function checkTopicedit($arrGrouptopic){
		if(!Core_Extend::checkRbac('group@grouptopic@edit')){
			return false;
		}

		$nGroupuserrole=Groupadmin_Extend::getGroupUserrole($arrGrouptopic['group_id']);
		
		$bAllowedEdittopic=false;
		if(in_array($nGroupuserrole,array(1,2,4))){
			$bAllowedEdittopic=true;
		}

		if($GLOBALS['___login___']!==false && $GLOBALS['___login___']['user_id']==$arrGrouptopic['user_id'] && CURRENT_TIMESTAMP-$arrGrouptopic['create_dateline']<=$GLOBALS['_cache_']['group_option']['grouptopic_edit_limittime']){
			$bAllowedEdittopic=true;
		}

		return $bAllowedEdittopic;
	}

	static public function checkTopicmove(){
		if(Core_Extend::isAdmin()){
			return true;
		}
		
		if(!Core_Extend::checkRbac('group@grouptopicadmin@movetopic')){
			return false;
		}

		// 再次限制，仅管理员（前提是已经授权）和后台超级管理员可以执行
		$arrUserrole=Model::F_('userrole','user_id=? AND role_id=1',$GLOBALS['___login___']['user_id'])
			->setColumns('user_id')
			->getOne();
		if(!empty($arrUserrole['user_id'])){
			return true;
		}

		return false;
	}

	static public function checkCommentRbac($arrGroup,$arrComment){
		if(!Core_Extend::checkRbac('group@grouptopic@editcommenttopic_dialog')){
			return false;
		}

		$nGroupuserrole=Groupadmin_Extend::getGroupUserrole($arrGroup['group_id']);
		
		$bAllowedEditcomment=false;
		if(in_array($nGroupuserrole,array(1,2,4))){
			$bAllowedEditcomment=true;
		}

		if(($nGroupuserrole==0 && $arrGroup['group_ispost']!=1) || ($nGroupuserrole==-2 && $arrGroup['group_ispost']==2)){
			if($arrComment['user_id']==$GLOBALS['___login___']['user_id'] && CURRENT_TIMESTAMP-$arrComment['create_dateline']<=$GLOBALS['_cache_']['group_option']['grouptopiccomment_edit_limittime']){
				$bAllowedEditcomment=true;
			}
		}

		return $bAllowedEditcomment;
	}

	static public function checkAdminGroupRbac($nGroupid){
		$nGroupuserrole=Groupadmin_Extend::getGroupUserrole($nGroupid);

		$bAllowedAdmin=false;
		if(in_array($nGroupuserrole,array(2,4))){
			$bAllowedAdmin=true;
		}

		return $bAllowedAdmin;
	}

	static public function checkCommentadminRbac($arrGroup,$arrType=array('group@grouptopicadmin@deletecomment','group@grouptopicadmin@hidecomment','group@grouptopicadmin@stickreplycomment','group@grouptopicadmin@auditcomment')){
		if(!Core_Extend::checkRbac($arrType)){
			return false;
		}

		$nGroupuserrole=Groupadmin_Extend::getGroupUserrole($arrGroup['group_id']);
		
		$bAllowedEditcomment=false;
		if(in_array($nGroupuserrole,array(1,2,4))){
			$bAllowedEditcomment=true;
		}

		return $bAllowedEditcomment;
	}

	static public function checkTopicadminRbac($nGroupid,$arrType=array('group@grouptopicadmin@deletetopic','group@grouptopicadmin@closetopic','group@grouptopicadmin@sticktopic','group@grouptopicadmin@digesttopic','group@grouptopicadmin@recommendtopic','group@grouptopicadmin@hidetopic','group@grouptopicadmin@categorytopic','group@grouptopicadmin@tagtopic','group@grouptopicadmin@colortopic','group@grouptopicadmin@uptopic','group@grouptopicadmin@audittopic')){
		if(!empty($nGroupid['group_id'])){
			$nGroupid=$nGroupid['group_id'];
		}
		
		if(!Core_Extend::checkRbac($arrType)){
			return false;
		}

		$nGroupuserrole=Groupadmin_Extend::getGroupUserrole($nGroupid);

		$bAllowedEditcomment=false;
		if(in_array($nGroupuserrole,array(1,2,4))){
			$bAllowedEditcomment=true;
		}

		return $bAllowedEditcomment;
	}

	static public function checkGroup($arrGroup,$bCheckAdd=false,$nUserid=null){
		if(Core_Extend::isAdmin()){
			return true;
		}
		
		if(is_null($nUserid)){
			$nUserid=$GLOBALS['___login___']['user_id'];
		}

		if(empty($arrGroup['group_id'])){
			$arrGroup=Model::F_('group','group_id=? AND group_status=1',$arrGroup)->getOne();
			if(empty($arrGroup['group_id'])){
				Q::E(Q::L('小组不存在或者还在审核中','Controller'));
			}
		}

		if($arrGroup['group_isopen']==0){
			if($GLOBALS['___login___']===false){
				C::urlGo(Core_Extend::windsforceReferer(),3,Q::L('只有该小组成员才能够访问小组','Controller').'&nbsp;&nbsp;'.Q::L('首先你需要的登录后才能够继续操作','Controller'));
			}
			
			$arrGroupuser=Model::F_('groupuser','user_id=? AND group_id=?',$nUserid,$arrGroup['group_id'])->getOne();
			if(empty($arrGroupuser['user_id'])){
				Q::E(Q::L('只有该小组成员才能够访问小组','Controller').'&nbsp;<span id="listgroup_'.$arrGroup['group_id'].'" class="commonjoinleave_group"><a href="javascript:void(0);" onclick="joinGroup('.$arrGroup['group_id'].',\'listgroup_'.$arrGroup['group_id'].'\');">'.Q::L('我要加入','Controller').'</a></span>');
			}
		}

		if($bCheckAdd===true){
			if($arrGroup['group_ispost']==0){
				$arrGroupuser=Model::F_('groupuser','user_id=? AND group_id=?',$GLOBALS['___login___']['user_id'],$arrGroup['group_id'])->getOne();
				if(empty($arrGroupuser['user_id'])){
					Q::E(Q::L('只有该小组成员才能发帖','Controller').'&nbsp;<span id="listgroup_'.$arrGroup['group_id'].'" class="commonjoinleave_group"><a href="javascript:void(0);" onclick="joinGroup('.$arrGroup['group_id'].',\'listgroup_'.$arrGroup['group_id'].'\');">'.Q::L('我要加入','Controller').'</a></span>');
				}
			}elseif($arrGroup['group_ispost']==1){
				Q::E(Q::L('该小组目前拒绝任何人发帖','Controller'));
			}
		}

		return true;
	}

	public static function isGroupuser($nGroupid,$nUserid){
		$arrTrygroupuser=Model::F_('groupuser','user_id=? AND group_id=?',$nUserid,$nGroupid)
			->setColumns('user_id')
			->getOne();
		if(empty($arrTrygroupuser['user_id'])){
			return 0;
		}else{
			return 1;
		}
	}

}
