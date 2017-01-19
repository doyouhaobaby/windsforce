<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   小组头部背景设置控制器($$)*/

!defined('Q_PATH') && exit;

class Headerbg_C_Controller extends InitController{

	public function index(){
		// 获取参数
		$sId=trim(Q::G('gid','G'));

		// 判断小组是否存在
		$arrGroup=Group_Extend::getGroup($sId);
		if(empty($arrGroup['group_id'])){
			$this->E(Q::L('小组不存在或在审核中','Controller'));
		}

		// 取得用户是否加入了小组
		$arrGroupuser=Group_Extend::getGroupuser($arrGroup['group_id']);

		// 读取系统背景
		$arrSystembgs=C::listDir(WINDSFORCE_PATH.'/app/group/Static/Images/groupbg',false,true);

		// 取得当前背景
		$sGroupHeaderbg=Group_Extend::getGroupHeaderbg($arrGroup['group_headerbg']);
		
		Core_Extend::getSeo($this,array('title'=>Q::L('头部背景设置','Controller').' - '.$arrGroup['group_nikename']));
		
		$this->assign('sGroupHeaderbg',$sGroupHeaderbg);
		$this->assign('nUploadSize',Core_Extend::getUploadSize($GLOBALS['_cache_']['group_option']['group_headbg_uploadfile_maxsize']));
		$this->assign('arrGroup',$arrGroup);
		$this->assign('arrGroupuser',$arrGroupuser);
		$this->display('groupadmin+headerbg');
	}

}
