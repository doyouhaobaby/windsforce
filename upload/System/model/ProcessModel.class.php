<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   进程模型($$)*/

!defined('Q_PATH') && exit;

class ProcessModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'process',
		);
	}

	static function F(){
		$arrArgs=func_get_args();
		return ModelMeta::instance(__CLASS__)->findByArgs($arrArgs);
	}

	static function M(){
		return ModelMeta::instance(__CLASS__);
	}

	public function deleteProcess($sName,$nTime){
		$oProcess=self::F('process_id=? OR process_expiry=?',$sName,intval($nTime))->getOne();
		if(!empty($oProcess['process_id'])){
			$oProcess->destroy();
			return true;
		}else{
			return false;
		}
	}

}
