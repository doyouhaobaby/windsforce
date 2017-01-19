<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   添加帖子控制器($$)*/

!defined('Q_PATH') && exit;

class Add_C_Controller extends InitController{

	public function index(){
		try{
			Core_Extend::checkSpam();
		}catch(Exception $e){
			$this->E($e->getMessage());
		}
		
		$nGroupid=intval(Q::G('gid','G'));

		// 快捷发贴
		if(empty($nGroupid)){
			$arrGroups=Model::F_('groupuser','@A','A.user_id=?',$GLOBALS['___login___']['user_id'])
				->setColumns('A.*')
				->join(Q::C('DB_PREFIX').'group AS B','B.*','A.group_id=B.group_id')
				->getAll();

			if(empty($arrGroups)){
				$this->E(Q::L('用户尚未加入任何小组','Controller'));
			}

			$this->assign('arrGroups',$arrGroups);
		}else{
			// 访问权限
			$arrGroup=Model::F_('group','group_id=? AND group_status=1',$nGroupid)->getOne();
			if(empty($arrGroup['group_id'])){
				$this->E(Q::L('小组不存在或者还在审核中','Controller'));
			}

			$this->assign('arrGroup',$arrGroup);

			// 取得用户是否加入了小组
			$arrGroupuser=Group_Extend::getGroupuser($arrGroup['group_id']);
			$this->assign('arrGroupuser',$arrGroupuser);

			try{
				// 验证小组权限
				Groupadmin_Extend::checkGroup($arrGroup,true);
			}catch(Exception $e){
				$this->E($e->getMessage());
			}
		}
		
		// 如果不是在某个小组发贴，读取一个小组
		$nLabel=0;
		if(empty($nGroupid) && isset($arrGroups[0])){
			$nGroupid=$arrGroups[0]['group_id'];
			$nLabel=1;
		}

		// 小组帖子分类
		$arrGrouptopiccategorys=Model::F_('grouptopiccategory','group_id=?',$nGroupid)
			->setColumns('grouptopiccategory_id,grouptopiccategory_name,grouptopiccategory_topicnum,group_id,grouptopiccategory_sort')
			->order('grouptopiccategory_sort ASC')
			->getAll();

		if($nLabel==1){
			$nGroupid='';
		}

		Core_Extend::getSeo($this,array('title'=>Q::L('发布帖子','Controller').(!empty($arrGroup['group_id'])?' - '.$arrGroup['group_nikename']:'')));

		$this->assign('arrGrouptopiccategorys',$arrGrouptopiccategorys);
		$this->assign('nGroupid',$nGroupid);
		$this->assign('nDisplaySeccode',$GLOBALS['_option_']['seccode_publish_status']);
		$this->display('grouptopic+add');
	}

}
