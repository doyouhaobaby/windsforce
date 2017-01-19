<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   群组用户关联模型($$)*/

!defined('Q_PATH') && exit;

class GroupuserModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'groupuser',
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
