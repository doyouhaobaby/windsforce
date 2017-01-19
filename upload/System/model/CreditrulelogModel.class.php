<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   积分规则记录模型($$)*/

!defined('Q_PATH') && exit;

class CreditrulelogModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'creditrulelog',
		);
	}

	static function F(){
		$arrArgs=func_get_args();
		return ModelMeta::instance(__CLASS__)->findByArgs($arrArgs);
	}

	static function M(){
		return ModelMeta::instance(__CLASS__);
	}	
	
	public function increase($nCreditrulelog,$arrLog){
		if($nCreditrulelog && !empty($arrLog) && is_array($arrLog)){
			$oDb=Db::RUN();
			$sSql="UPDATE ".$GLOBALS['_commonConfig_']['DB_PREFIX']."creditrulelog SET ".implode(',',$arrLog)." WHERE `creditrulelog_id`=".$nCreditrulelog;
			$oDb->query($sSql);
			return true;
		}
		return false;
	}

}
