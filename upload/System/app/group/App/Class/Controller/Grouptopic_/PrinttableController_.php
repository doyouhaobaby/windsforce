<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   帖子打印控制器($$)*/

!defined('Q_PATH') && exit;

class Printtable_C_Controller extends InitController{

	public function index(){
		if(!Core_Extend::checkRbac('group@grouptopic@view')){
			$this->E(Q::L('你没有权限查看帖子','Controller'));
		}
		
		// 获取参数
		$nId=intval(Q::G('id','G'));
	
		$arrGrouptopic=Model::F_('grouptopic','@A','A.grouptopic_id=? AND A.grouptopic_status=1',$nId)
			->setColumns('A.*')
			->join(Q::C('DB_PREFIX').'group AS B','B.*','A.group_id=B.group_id')
			->join(Q::C('DB_PREFIX').'grouptopiccontent AS C','C.grouptopic_content','A.grouptopic_id=C.grouptopic_id')
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

		// 更新点击量
		Model::M_('grouptopic')->updateWhere(array('grouptopic_views'=>$arrGrouptopic['grouptopic_views']+1),'grouptopic_id=?',$nId);
		$arrGrouptopic['grouptopic_views']++;

		// 判断用户是否回复过帖子
		if($arrGrouptopic['grouptopic_onlycommentview']==1){
			$bHavecomment=false;
			if($GLOBALS['___login___']!==false){
				if($arrGrouptopic['user_id']==$GLOBALS['___login___']['user_id']){
					$bHavecomment=true;
				}else{
					$arrTrygrouptopiccomment=Model::F_('grouptopiccomment','user_id=? AND grouptopic_id=?',$GLOBALS['___login___']['user_id'],$arrGrouptopic['grouptopic_id'])
						->setColumns('grouptopiccomment_id')
						->getOne();
					if(!empty($arrTrygrouptopiccomment['grouptopiccomment_id'])){
						$bHavecomment=true;
					}
				}
			}
			$this->assign('bHavecomment',$bHavecomment);
		}
		
		// 回复列表
		$arrWhere=array();
		$arrWhere['A.grouptopiccomment_status']=1;
		$arrWhere['A.grouptopic_id']=$arrGrouptopic['grouptopic_id'];

		if(Groupadmin_Extend::checkCommentadminRbac($arrGrouptopic,array('group@grouptopicadmin@auditcomment'))){
			$arrWhere['A.grouptopiccomment_status']=array('neq',CommonModel::STATUS_RECYLE);
		}

		$arrComments=Model::F_('grouptopiccomment','@A')->where($arrWhere)
			->joinLeft(Q::C('DB_PREFIX').'user AS C','C.user_name,C.user_nikename','A.user_id=C.user_id')
			->order('A.grouptopiccomment_status ASC,A.grouptopiccomment_stickreply DESC,A.create_dateline '.($arrGrouptopic['grouptopic_ordertype']==1?'DESC':'ASC'))
			->limit(0,$GLOBALS['_cache_']['group_option']['grouptopic_listcommentnum'])
			->getAll();

		Core_Extend::getSeo($this,array('title'=>$arrGrouptopic['grouptopic_title'].' - '.$arrGrouptopic['group_nikename']));

		$this->assign('arrGrouptopic',$arrGrouptopic);
		$this->assign('arrComments',$arrComments);
		$this->display('grouptopic+printtable');
	}

}
