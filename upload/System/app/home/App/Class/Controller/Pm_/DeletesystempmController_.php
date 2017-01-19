<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   删除系统短消息($$)*/

!defined('Q_PATH') && exit;

class Deletesystempm_C_Controller extends InitController{

	public function index(){
		$arrPmIds=Q::G('pmid','P');

		if(is_array($arrPmIds)){
			$oPm=Q::instance('PmModel');
			foreach($arrPmIds as $nPmId){
				$oPm->deleteSystemmessage($nPmId);
				if($oPm->isError()){
					$this->E($oPm->getErrorMessage());
				}
			}
		}

		$this->S(Q::L('删除系统短消息成功','Controller'));
	}

}
