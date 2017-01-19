<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   小组图标设置控制器($$)*/

!defined('Q_PATH') && exit;

class Icon_C_Controller extends InitController{

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
		
		// 取得ICON
		$sGroupIcon=Group_Extend::getGroupIcon($arrGroup,true);
		
		Core_Extend::getSeo($this,array('title'=>Q::L('小组图标设置','Controller').' - '.$arrGroup['group_nikename']));
		
		$this->assign('sGroupIcon',$sGroupIcon);
		$this->assign('nUploadSize',Core_Extend::getUploadSize($GLOBALS['_cache_']['group_option']['group_icon_uploadfile_maxsize']));
		$this->assign('arrGroup',$arrGroup);
		$this->assign('arrGroupuser',$arrGroupuser);
		$this->display('groupadmin+icon');
	}

}
