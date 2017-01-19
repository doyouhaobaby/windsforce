<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   标记阅读系统短消息($$)*/

!defined('Q_PATH') && exit;

class Readselect_C_Controller extends InitController{

	public function index(){
		$arrPmIds=Q::G('pmid','P');
		if(empty($arrPmIds)){
			$this->E(Q::L('你没有指定要标记的短消息','Controller'));
		}
		
		if($arrPmIds){
			$oPm=Q::instance('PmModel');
			foreach($arrPmIds as $nPmId){
				$oPm->readSystemmessage($nPmId);
				if($oPm->isError()){
					$this->E($oPm->getErrorMessage());
				}
			}
		}
		
		$this->S(Q::L('标记短消息已读成功','Controller'));
	}

}
