<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   小组个人空间小组($$)*/

!defined('Q_PATH') && exit;

class Group_C_Controller extends InitController{
	
	public function index(){
		$nId=intval(Q::G('id','G'));
		
		$arrUserInfo=Model::F_('user','user_status=1 AND user_id=?',$nId)->setColumns('user_id,user_name')->getOne();
		if(empty($arrUserInfo['user_id'])){
			$this->E(Q::L('你指定的用户不存在','Controller'));
		}else{
			$this->assign('arrUserInfo',$arrUserInfo);
		}

		$this->assign('nId',$nId);

		// 读取我加入的所有小组
		$arrWhere=array();
		$arrWhere['B.group_status']=1;
		$arrWhere['A.user_id']=$nId;

		$arrGroups=Model::F_('groupuser','@A')->where($arrWhere)
			->setColumns('A.*')
			->join(Q::C('DB_PREFIX').'group AS B','B.*','A.group_id=B.group_id')
			->order('A.create_dateline DESC')
			->getAll();

		// 读取我加入的小组
		if($GLOBALS['___login___']!==FALSE){
			$arrGroupuser=$arrGroupuserId=array();
			foreach($arrGroups as $arrVal){
				$arrGroupuserId[]=$arrVal['group_id'];
			}
			$arrGroupuser=Model::F_('groupuser')
				->where(array('group_id'=>$arrGroupuserId?array('in',$arrGroupuserId):0,'user_id'=>$GLOBALS['___login___']['user_id']))
				->getColumn('group_id',true);
			if(empty($arrGroupuser)){
				$arrGroupuser=array();
			}
		}else{
			$arrGroupuser=array();
		}

		Core_Extend::getSeo($this,array('title'=>$arrUserInfo['user_name'].' - '.Q::L('我的小组','Controller')));

		$this->assign('arrGroups',$arrGroups);
		$this->assign('arrGroupuser',$arrGroupuser);
		$this->display('space+group');
	}

}
