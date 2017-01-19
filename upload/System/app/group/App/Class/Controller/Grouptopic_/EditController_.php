<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   编辑帖子控制器($$)*/

!defined('Q_PATH') && exit;

class Edit_C_Controller extends InitController{

	public function index(){
		$nTid=intval(Q::G('tid','G'));

		$arrGrouptopic=Model::F_('grouptopic','@A','A.grouptopic_id=? AND A.grouptopic_status=1',$nTid)
			->setColumns('A.*')
			->join(Q::C('DB_PREFIX').'group AS B','B.*','A.group_id=B.group_id')
			->join(Q::C('DB_PREFIX').'grouptopiccontent AS C','C.grouptopic_content','A.grouptopic_id=C.grouptopic_id')
			->getOne();
		if(empty($arrGrouptopic['grouptopic_id'])){
			$this->E(Q::L('不存在你要编辑的主题','Controller'));
		}

		try{
			// 验证小组权限
			Groupadmin_Extend::checkGroup($arrGrouptopic,true);
		}catch(Exception $e){
			$this->E($e->getMessage());
		}

		if(!Groupadmin_Extend::checkTopicedit($arrGrouptopic)){
			$this->E(Q::L('你没有权限编辑帖子','Controller'));
		}
	
		// 取得小组帖子分类
		$arrGrouptopiccategorys=Model::F_('grouptopiccategory','group_id=?',$arrGrouptopic['group_id'])
			->setColumns('grouptopiccategory_id,grouptopiccategory_name,grouptopiccategory_topicnum,group_id,grouptopiccategory_sort')
			->order('grouptopiccategory_sort ASC')
			->getAll();


		// 获取帖子标签
		$sTag='';
		$arrGrouptopictags=Model::F_('grouptopictagindex','@A','A.grouptopic_id=?',$arrGrouptopic['grouptopic_id'])
			->setColumns('A.*')
			->join(Q::C('DB_PREFIX').'grouptopictag AS B','B.grouptopictag_name,B.grouptopictag_count','A.grouptopictag_id=B.grouptopictag_id')
			->order('B.create_dateline DESC')
			->getAll();
		if(!empty($arrGrouptopictags)){
			foreach($arrGrouptopictags as $arrGrouptopictag){
				$sTag.=$arrGrouptopictag['grouptopictag_name'].',';
			}
			$sTag=trim($sTag,',');
		}

		// 取得用户是否加入了小组
		$arrGroupuser=Group_Extend::getGroupuser($arrGrouptopic['group_id']);

		Core_Extend::getSeo($this,array('title'=>$arrGrouptopic['grouptopic_title'].' - '.Q::L('帖子编辑','Controller')));
		
		$this->assign('arrGrouptopic',$arrGrouptopic);
		$this->assign('arrGrouptopiccategorys',$arrGrouptopiccategorys);
		$this->assign('sTag',$sTag);
		$this->assign('arrGroup',$arrGrouptopic);
		$this->assign('nGroupid',$arrGrouptopic['group_id']);
		$this->assign('arrGroupuser',$arrGroupuser);
		$this->assign('nDisplaySeccode',$GLOBALS['_option_']['seccode_publish_status']);
		$this->display('grouptopic+add');
	}

}
