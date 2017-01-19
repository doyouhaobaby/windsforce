<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   帖子分类设置对话框控制器($$)*/

!defined('Q_PATH') && exit;

class Categorytopicdialog_C_Controller extends InitController{

	public function index(){
		$nGroupid=intval(Q::G('groupid','G'));
		$sGrouptopicid=trim(Q::G('grouptopicid','G'));
		$nCategoryid=intval(Q::G('category_id','G'));
		
		$arrGrouptopicid=Q::normalize($sGrouptopicid);
		$sGrouptopics=implode(',',$arrGrouptopicid);
		if(empty($sGrouptopicid)){
			$this->E(Q::L('没有待操作的帖子','Controller'));
		}
		
		// 取得小组的分类
		$arrGrouptopiccategorys=Model::F_('grouptopiccategory','group_id=?',$nGroupid)
			->setColumns('grouptopiccategory_id,grouptopiccategory_name')
			->order('grouptopiccategory_sort ASC,create_dateline DESC')
			->getAll();
		
		if(isset($_GET['category_id'])){
			$this->assign('nCategoryid',$nCategoryid);
		}

		$this->assign('sGrouptopics',$sGrouptopics);
		$this->assign('nGroupid',$nGroupid);
		$this->assign('arrGrouptopiccategorys',$arrGrouptopiccategorys);
		$this->assign('sTitle',Q::L('你选择了 %d 篇帖子','Controller',null,count($arrGrouptopicid)));
		$this->display('grouptopicadmin+categorytopicdialog');
	}

}
