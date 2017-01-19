<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   级别控制器($$)*/

!defined('Q_PATH') && exit;

class RatingController extends AController{

	public function init__(){
		parent::init__();

		if($GLOBALS['___login___']['user_id']!=1){
			$this->E(Q::L('只有用户ID为1的超级管理员才能够访问本页','Controller'));
		}
	}
	
	public function filter_(&$arrMap){
		$arrMap['A.rating_name']=array('like',"%".Q::G('rating_name')."%");
		
		$nRatinggroupId=Q::G('ratinggroup_id');
		if($nRatinggroupId!==null && $nRatinggroupId!=''){
			$arrMap['A.ratinggroup_id']=$nRatinggroupId;
		}
	}

	protected function sqljoin_(){
		return "->joinLeft('".Q::C('DB_PREFIX')."ratinggroup AS B','B.*','A.ratinggroup_id=B.ratinggroup_id')";
	}

	public function bIndex_(){
		$this->getRatinggroup();

		$arrRatingtype=C::listDir(WINDSFORCE_PATH.'/Public/images/rating');
		$this->assign('arrRatingtype',$arrRatingtype);
		$this->assign('arrOptions',$GLOBALS['_option_']);
	}

	public function update_option(){
		$oOptionController=new OptionController();
		$oOptionController->update_option();
	}

	public function bAdd_(){
		$this->bEdit_();
	}
	
	public function bEdit_(){
		$this->check_appdevelop();
		$this->getRatinggroup();
	}

	public function check_ratingname(){
		$sRatingName=trim(Q::G('rating_name'));
		$nId=intval(Q::G('id'));

		if(!$sRatingName){
			exit('false');
		}

		// 查询条件
		$arrWhere=array();
		$arrWhere['rating_name']=$sRatingName;
		if($nId){
			$arrWhere['rating_id']=array('neq',$nId);
		}

		$oRating=RatingModel::F()->where($arrWhere)->setColumns('rating_id')->getOne();
		if(empty($oRating['rating_id'])){
			exit('true');
		}else{
			exit('false');
		}
	}
	
	public function getRatinggroup(){
		$arrRatinggroup=array_merge(array(array('ratinggroup_id'=>0,'ratinggroup_title'=>Q::L('未分组','Controller'))),
				RatinggroupModel::F()->setColumns('ratinggroup_id,ratinggroup_title')->asArray()->all()->query()
		);
		$this->assign('arrRatinggroup',$arrRatinggroup);
	}

	public function bForeverdelete_deep_(){
		$this->bForeverdelete_();
	}

	public function bForeverdelete_(){
		$this->check_appdevelop();
		$sId=Q::G('id','G');

		$arrIds=explode(',',$sId);
		if(is_array($arrIds)){
			foreach($arrIds as $nId){
				if($this->is_system_rating($nId)){
					$this->E(Q::L('系统级别无法删除','Controller'));
				}
			}
		}
	}

	public function change_ratinggroup(){
		$this->check_appdevelop();
		$sId=trim(Q::G('id','G'));
		$nRatinggroupId=intval(Q::G('ratinggroup_id','G'));
		
		if(!empty($sId)){
			if($nRatinggroupId){
				// 判断级别分组是否存在
				$oRatinggroup=RatinggroupModel::F('ratinggroup_id=?',$nRatinggroupId)->getOne();
				if(empty($oRatinggroup['ratinggroup_id'])){
					$this->E(Q::L('你要移动的级别分组不存在','Controller'));
				}
			}
			
			$arrIds=explode(',', $sId);
			foreach($arrIds as $nId){
				if($this->is_system_rating($nId)){
					$this->E(Q::L('系统级别无法移动','Controller'));
				}
				
				$oRating=RatingModel::F('rating_id=?',$nId)->getOne();
				$oRating->ratinggroup_id=$nRatinggroupId;
				$oRating->save('update');
				if($oRating->isError()){
					$this->E($oRating->getErrorMessage());
				}
			}

			$this->S(Q::L('移动级别分组成功','Controller'));
		}else{
			$this->E(Q::L('操作项不存在','Controller'));
		}
	}

	public function bInput_change_ajax_(){
		$this->check_appdevelop();
	}

	public function is_system_rating($nId){
		$nId=intval($nId);

		$oRating=RatingModel::F('rating_id=?',$nId)->setColumns('rating_id,rating_issystem')->getOne();
		if(empty($oRating['rating_id'])){
			return false;
		}

		if($oRating['rating_issystem']==1){
			return true;
		}

		return false;
	}

}
