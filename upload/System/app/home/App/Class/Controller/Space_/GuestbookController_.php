<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   个人空间留言板($$)*/

!defined('Q_PATH') && exit;

/** 导入个人信息处理函数 */
require_once(Core_Extend::includeFile('function/Profile_Extend'));

class Guestbook_C_Controller extends InitController{

	public function index(){
		$nId=intval(Q::G('id','G'));
		
		$arrUserInfo=Model::F_('user','@A')
			->setColumns('A.user_id,A.user_name,A.create_dateline,A.user_lastlogintime,A.user_sign,A.user_nikename')
			->join(Q::C('DB_PREFIX').'userprofile AS B','B.*','A.user_id=B.user_id')
			->join(Q::C('DB_PREFIX').'usercount AS C','C.*','A.user_id=C.user_id')
			->joinLeft(Q::C('DB_PREFIX').'online AS G','G.online_isstealth,G.online_ip','A.user_id=G.user_id')
			->where(array('A.user_status'=>1,'A.user_id'=>$nId))
			->getOne();

		if(empty($arrUserInfo['user_id'])){
			$this->E(Q::L('你指定的用户不存在','Controller'));
		}else{
			$this->assign('arrUserInfo',$arrUserInfo);
			$this->_arrUserInfo=$arrUserInfo;
		}

		// 判断邮件等外部地址过来的查找评论地址
		$nIsolationCommentid=intval(Q::G('isolation_commentid','G'));
		if($nIsolationCommentid){
			$result=UserguestbookModel::getCommenturlByid($nIsolationCommentid);
			if($result===false){
				$this->E(Q::L('该条留言已被删除、屏蔽或者尚未通过审核','Controller'));
			}

			C::urlGo($result);
			exit();
		}

		//用户等级名字
		$this->assign('arrRatinginfo',Core_Extend::getUserrating($arrUserInfo['usercount_extendcredit1'],false));
		$this->assign('nUserscore',$arrUserInfo['usercount_extendcredit1']);

		// 获取留言列表
		$arrWhere=array();
		$arrWhere['A.userguestbook_status']=1;
		$arrWhere['A.userguestbook_userid']=$nId;

		$nTotalRecord=Model::F_('userguestbook','@A')->where($arrWhere)->all()->getCounts();
		$oPage=Page::RUN($nTotalRecord,$GLOBALS['_cache_']['home_option']['homefreshcomment_list_num'],'@space@?id='.$nId.'&type=guestbook');
		$arrUserguestbookLists=Model::F_('userguestbook','@A')->where($arrWhere)
			->setColumns('A.userguestbook_id,A.user_id,A.create_dateline,A.userguestbook_content')
			->joinLeft(Q::C('DB_PREFIX').'user AS B','B.user_name','A.user_id=B.user_id')
			->order('A.userguestbook_id DESC')
			->limit($oPage->S(),$oPage->N())
			->getAll();

		Core_Extend::getSeo($this,array('title'=>$arrUserInfo['user_name'].' - '.Q::L('我的留言板','Controller')));
		
		$this->assign('nId',$nId);
		$this->assign('nDisplaySeccode',$GLOBALS['_cache_']['home_option']['seccode_comment_status']);
		$this->assign('nTotalUserguestbook',$nTotalRecord);
		$this->assign('sPageNavbar',$oPage->P(array('id'=>'pagination','style'=>'li','current'=>'active')));
		$this->assign('arrUserguestbookLists',$arrUserguestbookLists);
		$this->display('space+guestbook');
	}

}
