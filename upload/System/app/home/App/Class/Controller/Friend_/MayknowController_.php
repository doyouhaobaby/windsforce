<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   你可能认识的好友($$)*/

!defined('Q_PATH') && exit;

/** 
 * 我和某个人有共同的好友的话，那么我们可能认识 
 * 通过比对我的好友和我的好友的好友可以找出来
 */
class Mayknow_C_Controller extends InitController{

	public function index(){
		require_once(Core_Extend::includeFile('function/Profile_Extend'));
		
		$nMaxnum=36;
		$nLoginuserid=intval($GLOBALS['___login___']['user_id']);
		
		// 初始化我的好友ID和好友的好友ID
		$arrMyfirendUserids=$arrFriendUserids=$arrFriendlistUserids=array();
		
		// 我的好友数据
		$nI=0;
		$arrMyfriends=Model::F_('friend','user_id=? AND friend_status=1',$nLoginuserid)->setColumns('friend_friendid')->getAll();
		if(!empty($arrMyfriends)){
			foreach($arrMyfriends as $arrMyfriends){
				// 读取100位之内好友数据
				if($nI<100){
					$arrFriendUserids[$arrMyfriends['friend_friendid']]=$arrMyfriends['friend_friendid'];
				}
				$arrMyfirendUserids[$arrMyfriends['friend_friendid']]=$arrMyfriends['friend_friendid'];
				$nI++;
			}
		}

		$arrMyfirendUserids[$nLoginuserid]=$nLoginuserid;

		// 查找我的好友的好友数据
		$nI=0;
		$arrFriendlists=array();

		if($arrFriendUserids){
			$arrFriendfriends=Model::F_('friend','friend_friendid in('.implode(',',$arrFriendUserids).') AND friend_status=?',1)->limit(0,200)->setColumns('user_id')->getAll();
			$arrFriendUserids[$nLoginuserid]=$nLoginuserid;
			if(!empty($arrFriendfriends)){
				foreach($arrFriendfriends as $arrFriendfriend){
					if(empty($arrMyfirendUserids[$arrFriendfriend['user_id']])){
						$arrFriendlistUserids[]=$arrFriendfriend['user_id'];
						$nI++;
						if($nI>=$nMaxnum){
							break;
						}
					}
				}
			}

			if($arrFriendlistUserids){
				$arrFriendlists=Model::F_('user','user_id in('.implode(',',$arrFriendlistUserids).') AND user_status=?',1)->order('user_id DESC')->getAll();
				if(empty($arrFriendlists)){
					$arrFriendlists=array();
				}
			}
		}

		Core_Extend::getSeo($this,array('title'=>Q::L('可能认识的人','Controller')));

		$this->assign('arrFriendlists',$arrFriendlists);
		$this->display('friend+mayknow');
	}

}
