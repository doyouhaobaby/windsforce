<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   用户和标签之间的索引模型($$)*/

!defined('Q_PATH') && exit;

class HometagindexModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'hometagindex',
		);
	}

	static function F(){
		$arrArgs=func_get_args();
		return ModelMeta::instance(__CLASS__)->findByArgs($arrArgs);
	}

	static function M(){
		return ModelMeta::instance(__CLASS__);
	}

}
