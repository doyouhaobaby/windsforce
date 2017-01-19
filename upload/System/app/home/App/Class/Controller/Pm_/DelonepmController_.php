<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   删除一条短消息($$)*/

!defined('Q_PATH') && exit;

class Delonepm_C_Controller extends InitController{

	public function index($nId='',$nUserId='',$nFromId=''){
		$nOldId=$nId;
		if(empty($nId)){
			$nId=Q::G('id');
		}
		if(empty($nId)){
			$this->E(Q::L('你没有指定要删除的短消息','Controller'));
		}
		
		if(empty($nUserId)){
			$nUserId=$GLOBALS['___login___']['user_id'];
		}
		
		if(empty($nFromId) && $nFromId!==0){
			$nFromId=Q::G('uid');;
		}
		
		$oPmModel=PmModel::F("pm_id=? AND pm_type='user' AND pm_msgtoid=? ".($nFromId!==0?'AND pm_msgfromid='.$nFromId:''),$nId,$nUserId)->query();
		if(empty($oPmModel['pm_id'])){
			$this->E(Q::L('待删除的短消息不存在','Controller'));
		}
		
		$oPmModel->pm_status=0;
		$oPmModel->save('update');
		if($oPmModel->isError()){
			$this->E($oPmModel->getErrorMessage());
		}else{
			if(empty($nOldId)){
				$this->S(Q::L('删除短消息成功','Controller'));
			}
		}
	}

	public function select(){
		$arrPmIds=Q::G('pmid','P');
		$arrUserId=Q::G('uid','P');
		if(empty($arrPmIds)){
			$this->E(Q::L('你没有指定要删除的短消息','Controller'));
		}
		
		if(is_array($arrPmIds)){
			foreach($arrPmIds as $nPmId){
				$this->index($nPmId,$GLOBALS['___login___']['user_id'],0);
			}
		}
		
		$this->S(Q::L('删除短消息成功','Controller'));
	}

}
