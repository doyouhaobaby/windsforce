<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   后台操作记录控制器($$)*/

!defined('Q_PATH') && exit;

class AdminlogController extends AController{

	public function init__(){
		parent::init__();

		if($GLOBALS['___login___']['user_id']!=1){
			$this->E(Q::L('只有用户ID为1的超级管理员才能够访问本页','Controller'));
		}
	}
	
	public function filter_(&$arrMap){
		$arrMap['A.adminlog_username']=array('like',"%".Q::G('adminlog_username')."%");
		$arrMap['A.adminlog_info']=array('like',"%".Q::G('adminlog_info')."%");

		// 添加时间
		$this->getTime_('A.create_dateline',$arrMap);
	}

	public function clear(){
		$this->display();
	}

	public function clear_all(){
		$oDb=Db::RUN();
		$sSql="TRUNCATE ".$GLOBALS['_commonConfig_']['DB_PREFIX']."adminlog";
		$oDb->query($sSql);
		
		$this->assign('__JumpUrl__',Q::U('adminlog/index'));
		$this->S(Q::L('清空后台管理数据成功','Controller'));
	}

}
