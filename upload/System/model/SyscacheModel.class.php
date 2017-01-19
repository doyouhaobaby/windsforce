<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   缓存模型($$)*/

!defined('Q_PATH') && exit;

class SyscacheModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'syscache',
		);
	}

	static function F(){
		$arrArgs=func_get_args();
		return ModelMeta::instance(__CLASS__)->findByArgs($arrArgs);
	}

	static function M(){
		return ModelMeta::instance(__CLASS__);
	}

	protected function beforeSave_(){
		$this->syscache_name=C::strip($this->syscache_name);
	}

	static public function delCache($sKey){
		self::M()->deleteWhere(array('syscache_name'=>$sKey)); 
	}

}
