<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   等级分组控制器($$)*/

!defined('Q_PATH') && exit;

class RatinggroupController extends AController{

	public function init__(){
		parent::init__();

		if($GLOBALS['___login___']['user_id']!=1){
			$this->E(Q::L('只有用户ID为1的超级管理员才能够访问本页','Controller'));
		}
	}
	
	public function filter_(&$arrMap){
		$arrMap['A.ratinggroup_name']=array('like',"%".Q::G('ratinggroup_name')."%");
		$arrMap['A.ratinggroup_title']=array('like',"%".Q::G('ratinggroup_title')."%");

		// 添加时间
		$this->getTime_('A.create_dateline',$arrMap);
	}

	public function bForbid_(){
		$this->check_appdevelop();

		$nId=intval(Q::G('id','G'));
		if($this->is_system_ratinggroup($nId)){
			$this->E(Q::L('系统等级分组无法禁用','Controller'));
		}
	}

	public function bForeverdelete_deep_(){
		$this->bForeverdelete_();
	}

	public function bForeverdelete_(){
		$this->check_appdevelop();

		$sId=Q::G('id','G');
		$arrIds=explode(',',$sId);
		foreach($arrIds as $nId){
			if($this->is_system_ratinggroup($nId)){
				$this->E(Q::L('系统等级分组无法删除','Controller'));
			}
		}
	}

	public function check_ratinggroupname(){
		$sRatinggroupName=trim(Q::G('ratinggroup_name'));
		$nId=intval(Q::G('id'));

		if(!$sRatinggroupName){
			exit('false');
		}

		// 查询条件
		$arrWhere=array();
		$arrWhere['ratinggroup_name']=$sRatinggroupName;
		if($nId){
			$arrWhere['ratinggroup_id']=array('neq',$nId);
		}

		$oRatinggroup=RatinggroupModel::F()->where($arrWhere)->setColumns('ratinggroup_id')->getOne();
		if(empty($oRatinggroup['ratinggroup_id'])){
			exit('true');
		}else{
			exit('false');
		}
	}

	public function is_system_ratinggroup($nId){
		$nId=intval($nId);

		$oRatinggroup=RatinggroupModel::F('ratinggroup_id=?',$nId)->setColumns('ratinggroup_id,ratinggroup_issystem')->getOne();
		if(empty($oRatinggroup['ratinggroup_id'])){
			return false;
		}

		if($oRatinggroup['ratinggroup_issystem']==1){
			return true;
		}

		return false;
	}

}
