<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   好友首页($$)*/

!defined('Q_PATH') && exit;

/** 导入个人信息函数 */
require_once(Core_Extend::includeFile('function/Profile_Extend'));

class Index_C_Controller extends InitController{

	public function index(){
		$arrWhere=array();
		
		$sType=trim(Q::G('type','G'));
		if($sType=='fan'){
			$arrWhere['A.friend_friendid']=$GLOBALS['___login___']['user_id'];
		}else{
			$arrWhere['A.user_id']=$GLOBALS['___login___']['user_id'];;
		}
		
		$sKey=trim(Q::G('key'));
		if(!empty($sKey)){
			if($sType=='fan'){
				$arrWhere['A.friend_username']=array('like',"%".$sKey."%");
			}else{
				$arrWhere['A.friend_friendusername']=array('like',"%".$sKey."%");
			}
		}
		
		$arrOptionData=$GLOBALS['_cache_']['home_option'];
	
		// 好友
		$arrWhere['A.friend_status']=1;
		$nTotalRecord=Model::F_('friend','@A')->where($arrWhere)
			->all()
			->getCounts();
		$oPage=Page::RUN($nTotalRecord,$arrOptionData['friend_list_num']);
		$arrFriends=Model::F_('friend','@A')->where($arrWhere)
			->setColumns('A.*')
			->join(Q::C('DB_PREFIX').'user AS B','B.user_name,B.user_sign','A.'.($sType=='fan'?'user_id':'friend_friendid').'=B.user_id')
			->order('A.create_dateline DESC')
			->limit($oPage->S(),$oPage->N())
			->getAll();

		Core_Extend::getSeo($this,array('title'=>Q::G('type','G')=='fan'?Q::L('我的粉丝','Controller'):Q::L('我的好友','Controller')));
		
		$this->assign('arrFriends',$arrFriends);
		$this->assign('sPageNavbar',$oPage->P(array('id'=>'pagination','style'=>'li','current'=>'active')));
		$this->assign('sType',$sType);
		$this->assign('sKey',$sKey);
		$this->display('friend+index');
	}

	public function get_gender_icon($nUserid){
		$oUserprofile=UserprofileModel::F('user_id=?',$nUserid)->getOne();
		if(!empty($oUserprofile['user_id'])){
			$nGender=$oUserprofile['userprofile_gender'];
		}else{
			$nGender=0;
		}

		return Profile_Extend::getUserprofilegender($nGender);
	}

}
