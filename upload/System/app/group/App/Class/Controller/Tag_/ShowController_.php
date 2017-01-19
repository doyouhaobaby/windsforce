<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   小组标签查找帖子($$)*/

!defined('Q_PATH') && exit;

class Show_C_Controller extends InitController{

	public function index(){
		$sTag=trim(Q::G('tag','G'));

		// 不存在则提示没有标签
		if(!empty($sTag)){
			$arrGrouptopictag=Model::F_('grouptopictag','grouptopictag_name=?',$sTag)->getOne();
			if(empty($arrGrouptopictag['grouptopictag_id'])){
				$this->E(Q::L('用户标签不存在','Controller'));
			}

			// 读取帖子列表
			$arrWhere=array();
			$arrWhere['A.grouptopictag_id']=$arrGrouptopictag['grouptopictag_id'];
			$arrWhere['B.grouptopic_status']=1;
			
			$nTotalRecord=Model::F_('grouptopictagindex','@A')->where($arrWhere)
				->join(Q::C('DB_PREFIX').'grouptopic AS B','B.*','A.grouptopic_id=B.grouptopic_id')
				->all()
				->getCounts();
			$oPage=Page::RUN($nTotalRecord,$GLOBALS['_cache_']['group_option']['group_tag_listtopicnum']);
			$arrGrouptopics=Model::F_('grouptopictagindex','@A')->where($arrWhere)
				->join(Q::C('DB_PREFIX').'grouptopic AS B','B.*','A.grouptopic_id=B.grouptopic_id')
				->join(Q::C('DB_PREFIX').'group AS C','C.group_name,C.group_nikename','C.group_id=B.group_id')
				->joinLeft(Q::C('DB_PREFIX').'grouptopiccategory AS D','D.grouptopiccategory_name','D.grouptopiccategory_id=B.grouptopiccategory_id')
				->order('B.grouptopic_id DESC')
				->limit($oPage->S(),$oPage->N())
				->getAll();

			// 热门标签
			Core_Extend::loadCache('group_hottag');

			Core_Extend::getSeo($this,array('title'=>$arrGrouptopictag['grouptopictag_name'].' - '.Q::L('标签','Controller')));
			
			$this->assign('arrGrouptopictag',$arrGrouptopictag);
			$this->assign('arrHottags',$GLOBALS['_cache_']['group_hottag']);
			$this->assign('arrGrouptopics',$arrGrouptopics);
			$this->assign('sPageNavbar',$oPage->P(array('id'=>'pagination','style'=>'li','current'=>'active')));
		}else{
			$this->U('group://tag/index');
		}
		
		$this->display('tag+show');
	}

}
