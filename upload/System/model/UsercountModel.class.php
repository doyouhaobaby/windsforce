<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   用户统计模型($$)*/

!defined('Q_PATH') && exit;

class UsercountModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'usercount',
		);
	}

	static function F(){
		$arrArgs=func_get_args();
		return ModelMeta::instance(__CLASS__)->findByArgs($arrArgs);
	}

	static function M(){
		return ModelMeta::instance(__CLASS__);
	}

	public function increase($arrUserId,$arrCredit){
		$arrSql=array();

		$arrAllowkey=self::$_arrMeta[$this->_sClassName]->_arrTableMeta['field'];
		foreach($arrCredit as $sKey=>$value){
			if(($value=intval($value)) && $value && $sKey!='user_id' && in_array($sKey,$arrAllowkey)){
				$arrSql[]="`{$sKey}`=`{$sKey}`+'{$value}'";
			}
		}

		if(!empty($arrSql)){
			$oDb=Db::RUN();
			$sSql="UPDATE ".$GLOBALS['_commonConfig_']['DB_PREFIX']."usercount SET ".implode(',',$arrSql)." WHERE user_id IN (".implode(',',$arrUserId).")";
			$oDb->query($sSql);
		}
	}

}
