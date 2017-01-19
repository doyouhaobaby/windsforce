<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   帖子分类删除控制器($$)*/

!defined('Q_PATH') && exit;

class Topiccategorydelete_C_Controller extends InitController{

	public function index(){
		// 获取参数
		$sId=trim(Q::G('gid'));
		$nGrouptopiccategoryid=intval(Q::G('cid'));
		
		// 判断小组是否存在
		$arrGroup=Group_Extend::getGroup($sId);
		if(empty($arrGroup['group_id'])){
			$this->E(Q::L('小组不存在或在审核中','Controller'));
		}
		
		$oGrouptopiccategory=GrouptopiccategoryModel::F('grouptopiccategory_id=? AND group_id=?',$nGrouptopiccategoryid,$arrGroup['group_id'])->query();
		if(empty($oGrouptopiccategory['grouptopiccategory_id'])){
			$this->E(Q::L('你删除的帖子分类不存在','Controller'));
		}
		
		// 执行删除
		$oModelMeta=GrouptopiccategoryModel::M();
		$oModelMeta->deleteWhere(array('grouptopiccategory_id'=>$nGrouptopiccategoryid));
		if($oModelMeta->isError()){
			$this->E($oModelMeta->getErrorMessage());
		}else{
			// 将分类ID重置为0
			Q::instance('GrouptopicModel')->resetCategory($nGrouptopiccategoryid);
			$this->S(Q::L('帖子分类删除成功','Controller'));
		}
	}

}
