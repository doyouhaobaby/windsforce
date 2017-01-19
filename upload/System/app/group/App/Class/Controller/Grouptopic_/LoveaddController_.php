<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   帖子喜欢处理逻辑控制器($$)*/

!defined('Q_PATH') && exit;

class Loveadd_C_Controller extends InitController{

	public function index(){
		// 获取参数
		$nId=intval(Q::G('grouptopic_id'));
		$sGrouptopiclovenote=trim(Q::G('grouptopiclove_note'));

		if(empty($sGrouptopiclovenote)){
			$sGrouptopiclovenote=Q::L('你没有填写喜欢备注','Controller');
		}
	
		$arrGrouptopic=Model::F_('grouptopic','@A','A.grouptopic_id=? AND A.grouptopic_status=1',$nId)
			->setColumns('A.*')
			->join(Q::C('DB_PREFIX').'group AS B','B.*','A.group_id=B.group_id')
			->getOne();
		if(empty($arrGrouptopic['grouptopic_id'])){
			$this->E(Q::L('你访问的主题不存在或已删除','Controller'));
		}

		// 判断帖子小组
		if(empty($arrGrouptopic['group_id'])){
			$this->E(Q::L('小组不存在或在审核中','Controller'));
		}

		try{
			// 验证小组权限
			Groupadmin_Extend::checkGroup($arrGrouptopic);
		}catch(Exception $e){
			$this->E($e->getMessage());
		}

		// 添加前检查是否已经喜欢过了
		$oGrouptopiclove=GrouptopicloveModel::F('user_id=? AND grouptopic_id=?',$GLOBALS['___login___']['user_id'],$arrGrouptopic['grouptopic_id'])->getOne();
		if(!empty($oGrouptopiclove['user_id'])){
			$this->E(Q::L('你已经喜欢过该帖子了，你不可以重复喜欢。','Controller'));
		}
		
		// 添加处理
		$oGrouptopiclove=new GrouptopicloveModel();
		$oGrouptopiclove->grouptopiclove_note=$sGrouptopiclovenote;
		$oGrouptopiclove->save();
		if($oGrouptopiclove->isError()){
			$this->E($oGrouptopiclove->getErrorMessage());
		}else{
			// 更新帖子的喜欢数
			Q::instance('GrouptopicModel')->rebuildGrouptopicloves($arrGrouptopic['grouptopic_id']);
			
			$this->S(Q::L('喜欢帖子成功，可以到个人中心查看你喜欢的帖子','Controller'));
		}
	}

}
