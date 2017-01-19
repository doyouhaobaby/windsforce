<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   查找下一篇帖子控制器($$)*/

!defined('Q_PATH') && exit;

class Next_C_Controller extends InitController{

	public function index(){
		// 获取参数
		$nId=intval(Q::G('id','G'));
	
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

		// 读取小组中的下一个帖子
		$arrGrouptopicnext=Model::F_('grouptopic','@A','A.grouptopic_id>? AND A.grouptopic_status=1 AND A.group_id=?',$nId,$arrGrouptopic['group_id'])
			->setColumns('A.grouptopic_id')
			->order('A.grouptopic_id ASC')
			->getOne();
		if(empty($arrGrouptopicnext['grouptopic_id'])){
			$this->E(Q::L('没有比这更新的主题了','Controller'));
		}

		C::urlGo(Group_Extend::getTopicurl($arrGrouptopicnext));
		exit();
	}

}
