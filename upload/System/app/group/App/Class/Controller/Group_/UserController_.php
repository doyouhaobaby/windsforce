<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   小组成员控制器($$)*/

!defined('Q_PATH') && exit;

class User_C_Controller extends InitController{

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

		// 取得小组管理成员
		$this->get_user_($arrGroup['group_id']);

		// 读取成员列表
		$arrWhere=array();
		$arrWhere['A.group_id']=$arrGroup['group_id'];
		$arrWhere['A.groupuser_isadmin']=0;

		$nTotalRecord=Model::F_('groupuser','@A')->where($arrWhere)
			->all()
			->getCounts();
		$oPage=Page::RUN($nTotalRecord,$GLOBALS['_cache_']['group_option']['group_listusernum'],'@group://group/user?gid='.(!empty($arrGroup['group_name'])?$arrGroup['group_name']:$arrGroup['group_id']).'&page={page}');
		$arrGroupusers=Model::F_('groupuser','@A')->where($arrWhere)
			->join(Q::C('DB_PREFIX').'user AS B','B.user_name','A.user_id=B.user_id')
			->order("A.create_dateline DESC")
			->limit($oPage->S(),$oPage->N())
			->getAll();

		Core_Extend::getSeo($this,array(
			'title'=>Q::L('小组成员','Controller').' - '.$arrGroup['group_nikename'],
			'keywords'=>Q::L('小组成员','Controller').','.$arrGroup['group_nikename']));

		$this->assign('arrGroupusers',$arrGroupusers);
		$this->assign('sPageNavbar',$oPage->P(array('id'=>'pagination','style'=>'li','current'=>'active')));
		$this->assign('arrGroup',$arrGroup);
		$this->assign('arrGroupuser',$arrGroupuser);
		$this->display('group+user');
	}

	protected function get_user_($nGroupid){
		// 读取小组创始人
		$arrGroupleaders=Model::F_('groupuser','@A','group_id=? AND groupuser_isadmin=2',$nGroupid)
			->join(Q::C('DB_PREFIX').'user AS B','B.user_name','A.user_id=B.user_id')
			->order('A.create_dateline DESC')
			->getAll();

		// 读取小组管理员
		$arrGroupadmins=Model::F_('groupuser','@A','group_id=? AND groupuser_isadmin=1',$nGroupid)
			->join(Q::C('DB_PREFIX').'user AS B','B.user_name','A.user_id=B.user_id')
			->order('A.create_dateline DESC')
			->getAll();
		
		$this->assign('arrGroupleaders',$arrGroupleaders);
		$this->assign('arrGroupadmins',$arrGroupadmins);
	}

}