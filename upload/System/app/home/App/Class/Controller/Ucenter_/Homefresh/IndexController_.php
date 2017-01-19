<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   新鲜事列表($$)*/

!defined('Q_PATH') && exit;

class Index_C_Controller extends InitController{

	public function index(){
		$arrWhere=array();

		// 话题
		$sKey=trim(Q::G('key','G'));
		if(!empty($sKey)){
			$oHomefreshtag=Model::F_('homefreshtag','homefreshtag_status=1 AND homefreshtag_name=?',$sKey)
				->setColumns('homefreshtag_id,homefreshtag_name,homefreshtag_username,create_dateline,homefreshtag_usercount,homefreshtag_homefreshcount,user_id')
				->getOne();
			if(empty($oHomefreshtag['homefreshtag_id'])){
				$this->assign('__JumpUrl__',Q::U('home://ucenter/index'));
				$this->E(Q::L('话题不存在或者被禁止了','Controller'));
			}

			$arrWhere['A.homefresh_message']=array('like',"%[TAG]#{$sKey}#[/TAG]%");
			$this->assign('oHomefreshtag',$oHomefreshtag);
			$sHomefreshtag=$oHomefreshtag['homefreshtag_name'];
		}

		// @user_name
		$sAtusername=trim(Q::G('at','G'));
		if(!empty($sAtusername)){
			$oUser=Model::F_('user','user_status=1 AND user_name=?',$sAtusername)
				->setColumns('user_id,user_name')
				->getOne();
			if(empty($oUser['user_id'])){
				$this->assign('__JumpUrl__',Q::U('home://ucenter/index'));
				$this->E(Q::L('用户不存在或者被禁止了','Controller'));
			}

			$arrWhere['A.homefresh_message']=array('like',"%[MESSAGE]@{$sAtusername}[/MESSAGE]%");
			$this->assign('oAtuser',$oUser);
			$sAtusername=$oUser['user_name'];
		}
		
		// 类型
		if(!empty($oHomefreshtag['homefreshtag_id']) || !empty($oUser['user_id'])){
			$sType='all';
		}else{
			$sType=trim(Q::G('type','G'));
			if(empty($sType)){
				$sType='';
			}
		}
		$this->assign('sType',$sType);

		// 分类
		$nCid=intval(Q::G('cid','G'));
		if($nCid){
			$arrWhere['A.homefreshcategory_id']=$nCid;
		}
		$this->assign('nCid',$nCid);

		switch($sType){
			case 'myself':
				$arrWhere['A.user_id']=$GLOBALS['___login___']['user_id'];
				break;
			case 'friend':
				// 仅好友
				$arrUserIds=Core_Extend::getFriendById($GLOBALS['___login___']['user_id']);
				if(!empty($arrUserIds)){
					$arrWhere['A.user_id']=array('in',$arrUserIds);
				}else{
					$arrWhere['A.user_id']='';
				}
				break;
			case 'all':
				// 这里可以设置用户隐私，比如用户不愿意将动态放出
				break;
			default:
				// 我和好友
				$arrUserIds=Core_Extend::getFriendById($GLOBALS['___login___']['user_id']);
				$arrUserIds[]=$GLOBALS['___login___']['user_id'];
				if(!empty($arrUserIds)){
					$arrWhere['A.user_id']=array('in',$arrUserIds);
				}else{
					$arrWhere['A.user_id']='';
				}
				break;
		}

		// 赞
		$sGoodCookie=Q::cookie('homefresh_goodnum');
		$arrGoodCookie=explode(',',$sGoodCookie);
		$this->assign('arrGoodCookie',$arrGoodCookie);

		// 新鲜事
		$arrWhere['A.homefresh_status']=1;
		$nTotalRecord=Model::F_('homefresh','@A')->where($arrWhere)->getCounts();
		$oPage=Page::RUN($nTotalRecord,$GLOBALS['_cache_']['home_option']['homefresh_list_num']);
		$arrHomefreshs=Model::F_('homefresh','@A')
			->setColumns('A.homefresh_id,A.homefresh_title,A.user_id,A.create_dateline,A.homefresh_attribute,A.homefresh_message,A.homefresh_commentnum,A.homefresh_goodnum,A.homefresh_viewnum,A.homefresh_type,A.homefresh_thumb,A.homefreshcategory_id')
			->join(Q::C('DB_PREFIX').'user AS B','B.user_name','A.user_id=B.user_id')
			->joinLeft(Q::C('DB_PREFIX').'homefreshcategory AS E','E.homefreshcategory_name','A.homefreshcategory_id=E.homefreshcategory_id')
			->where($arrWhere)
			->order('A.homefresh_id DESC')
			->limit($oPage->S(),$oPage->N())
			->asArray()
			->getAll();

		// 热门话题
		Core_Extend::loadCache('hottag');
		Core_Extend::loadCache('category');
		$this->assign('arrHothomefreshtags',$GLOBALS['_cache_']['hottag']);
		$this->assign('arrCategorys',$GLOBALS['_cache_']['category']);

		$arrCids=array();
		if(!empty($GLOBALS['_cache_']['category'])){
			foreach($GLOBALS['_cache_']['category'] as $arrTempValue){
				array_push($arrCids,$arrTempValue['homefreshcategory_id']);
			}
		}
		$this->assign('arrCids',$arrCids);

		// SEO标题
		$sSeoTitle='';
		if(!empty($sHomefreshtag)){
			$sSeoTitle=$sHomefreshtag.' - ';
		}elseif(!empty($sAtusername)){
			$sSeoTitle='@'.$sAtusername.' - ';
		}
		$sSeoTitle=$sSeoTitle.Q::L('用户中心','Controller');

		Core_Extend::getSeo($this,array('title'=>$sSeoTitle));

		$this->assign('arrHomefreshs',$arrHomefreshs);
		$this->assign('nTotalHomefreshnum',$nTotalRecord);
		$this->assign('sPageNavbar',$oPage->P(array('id'=>'pagination','style'=>'li','current'=>'active')));
		$this->assign('nDisplayCommentSeccode',$GLOBALS['_cache_']['home_option']['seccode_comment_status']);
		$this->display('homefresh+index');
	}

}
