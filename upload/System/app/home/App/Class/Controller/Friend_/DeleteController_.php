<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   删除好友($$)*/

!defined('Q_PATH') && exit;

class Delete_C_Controller extends InitController{

	public function index(){
		$nFriendId=intval(Q::G('friendid'));
		$nFan=intval(Q::G('fan'));
		
		$oFriendModel=Q::instance('FriendModel');
		$oFriendModel->deleteFriend($nFriendId,$GLOBALS['___login___']['user_id'],$nFan);
		if($oFriendModel->isError()){
			$this->E($oFriendModel->getErrorMessage());
		}else{
			$this->S(Q::L('删除好友成功','Controller'));
		}
	}

}
