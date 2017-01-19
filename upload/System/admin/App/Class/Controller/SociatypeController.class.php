<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   社会化帐号控制器($$)*/

!defined('Q_PATH') && exit;

class SociatypeController extends AController{

	public function filter_(&$arrMap){
		$arrMap['A.sociatype_identifier']=array('like',"%".Q::G('sociatype_identifier')."%");
		$arrMap['A.sociatype_appid']=array('like',"%".Q::G('sociatype_appid')."%");
		$arrMap['A.sociatype_title']=array('like',"%".Q::G('sociatype_title')."%");
	}
	
	public function bForeverdelete_deep_(){
		$this->bForeverdelete_();
	}

	public function bForeverdelete_(){
		$sId=Q::G('id','G');
		$arrIds=explode(',',$sId);
		foreach($arrIds as $nId){
			if($this->is_system_sociatype($nId)){
				$this->E(Q::L('系统社会化帐号无法删除','Controller'));
			}
		}
	}

	protected function aInsert($nId=null){
		if(!Q::classExists('Cache_Extend')){
			require_once(Core_Extend::includeFile('function/Cache_Extend'));
		}
		Cache_Extend::updateCache("sociatype");
	}

	protected function aUpdate($nId=null){
		$this->aInsert();
	}

	public function check_identifier(){
		$sSociatypeIdentifier=trim(Q::G('sociatype_identifier'));
		$nId=intval(Q::G('id'));

		if(!$sSociatypeIdentifier){
			exit('false');
		}

		// 查询条件
		$arrWhere=array();
		$arrWhere['sociatype_identifier']=$sSociatypeIdentifier;
		if($nId){
			$arrWhere['sociatype_id']=array('neq',$nId);
		}

		$oSociatype=SociatypeModel::F()->where($arrWhere)->setColumns('sociatype_id')->getOne();
		if(empty($oSociatype['sociatype_id'])){
			exit('true');
		}else{
			exit('false');
		}
	}

	public function aForeverdelete_deep($sId){
		$this->aInsert();
	}

	public function aForeverdelete($sId){
		$this->aInsert();
	}

	protected function aForbid(){
		$this->aInsert();
	}
	
	protected function aResume(){
		$this->aInsert();
	}

	public function is_system_sociatype($nId){
		$nId=intval($nId);
		$oSociatype=SociatypeModel::F('sociatype_id=?',$nId)->setColumns('sociatype_id,sociatype_issystem')->getOne();
		if(empty($oSociatype['sociatype_id'])){
			return false;
		}

		if($oSociatype['sociatype_issystem']==1){
			return true;
		}

		return false;
	}

}
