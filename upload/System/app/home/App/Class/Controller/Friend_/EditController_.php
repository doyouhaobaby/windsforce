<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   编辑好友备注($$)*/

!defined('Q_PATH') && exit;

class Edit_C_Controller extends InitController{

	public function index(){
		$nFriendId=Q::G('friendid');
		$sComment=trim(Q::G('comment'));
		$nFan=intval(Q::G('fan'));

		$oFriendModel=Q::instance('FriendModel');
		$oFriendModel->editFriendComment($nFriendId,$GLOBALS['___login___']['user_id'],$sComment,$nFan);
		if($oFriendModel->isError()){
			$this->E($oFriendModel->getErrorMessage());
		}else{
			$this->S(Q::L('更新备注成功','Controller'));
		}
	}

}
