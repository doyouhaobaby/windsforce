<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   个人空间基本资料($$)*/

!defined('Q_PATH') && exit;

/** 导入个人信息处理函数 */
require_once(Core_Extend::includeFile('function/Profile_Extend'));

class Base_C_Controller extends InitController{

	public function index(){
		$nId=intval(Q::G('id','G'));
		
		$arrUserInfo=Model::F_('user','@A')
			->setColumns('A.user_id,A.user_name,A.create_dateline,A.user_lastlogintime,A.user_sign,A.user_nikename')
			->join(Q::C('DB_PREFIX').'userprofile AS B','B.*','A.user_id=B.user_id')
			->join(Q::C('DB_PREFIX').'usercount AS C','C.*','A.user_id=C.user_id')
			->joinLeft(Q::C('DB_PREFIX').'online AS G','G.online_isstealth,G.online_ip','A.user_id=G.user_id')
			->where(array('A.user_status'=>1,'A.user_id'=>$nId))
			->getOne();

		if(empty($arrUserInfo)){
			$this->E(Q::L('你指定的用户不存在','Controller'));
		}else{
			$this->assign('arrUserInfo',$arrUserInfo);
		}

		$this->_arrUserInfo=$arrUserInfo;

		//基本类型
		$sDo=Q::G('do','G');
		if(!in_array($sDo,array('','base','contact','edu','work','info'))){
			$sDo='';
		}

		Core_Extend::loadCache('userprofilesetting');
		$this->assign('arrUserprofilesettingDatas',$GLOBALS['_cache_']['userprofilesetting']);
		$this->assign('sDirthDistrict',Profile_Extend::getDistrict($arrUserInfo,'birth',false));
		$this->assign('sResideDistrict',Profile_Extend::getDistrict($arrUserInfo,'reside',false));
		
		//好友
		$arrFriends=Model::F_('friend','@A','A.user_id=? AND A.friend_status=1',$nId)
			->setColumns('A.*')
			->joinLeft(Q::C('DB_PREFIX').'user AS B','B.user_name','A.friend_friendid=B.user_id')
			->limit(0,$GLOBALS['_cache_']['home_option']['my_friend_limit_num'])
			->order('A.create_dateline DESC')
			->getAll();

		$this->assign('sDo',$sDo);
		$this->assign('nId',$nId);
		$this->assign('arrFriends',$arrFriends);

		//用户等级名字
		$this->assign('arrRatinginfo',Core_Extend::getUserrating($arrUserInfo['usercount_extendcredit1'],false));
		$this->assign('nUserscore',$arrUserInfo['usercount_extendcredit1']);

		//视图
		$arrProfileSetting=Profile_Extend::getProfileSetting();
		$this->assign('arrBases',$arrProfileSetting[0]);
		$this->assign('arrContacts',$arrProfileSetting[1]);
		$this->assign('arrEdus',$arrProfileSetting[2]);
		$this->assign('arrWorks',$arrProfileSetting[3]);
		$this->assign('arrInfos',$arrProfileSetting[4]);

		$arrInfoMenus=Profile_Extend::getInfoMenu();
		$this->assign('arrInfoMenus',$arrInfoMenus);

		// 最近留言
		$arrWhere=array();
		$arrWhere['A.userguestbook_status']=1;
		$arrWhere['A.userguestbook_userid']=$nId;

		$nTotalRecord=Model::F_('userguestbook','@A')->where($arrWhere)->all()->getCounts();
		$arrUserguestbookLists=Model::F_('userguestbook','@A')->where($arrWhere)
			->limit(0,$GLOBALS['_cache_']['home_option']['homefreshcomment_list_num'])
			->setColumns('A.userguestbook_id,A.user_id,A.create_dateline,A.userguestbook_content')
			->joinLeft(Q::C('DB_PREFIX').'user AS B','B.user_name','A.user_id=B.user_id')
			->order('A.userguestbook_id DESC')
			->getAll();

		Core_Extend::getSeo($this,array('title'=>$arrUserInfo['user_name'].' - '.Q::L('个人空间','Controller')));
		
		$this->assign('nDisplaySeccode',$GLOBALS['_cache_']['home_option']['seccode_comment_status']);
		$this->assign('nTotalUserguestbook',$nTotalRecord);
		$this->assign('arrUserguestbookLists',$arrUserguestbookLists);
		$this->display('space+index');
	}

}
