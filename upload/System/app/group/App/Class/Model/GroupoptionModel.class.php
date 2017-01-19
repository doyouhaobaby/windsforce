<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   群组配置模型($$)*/

!defined('Q_PATH') && exit;

class GroupoptionModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'groupoption',
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
		$this->groupoption_name=C::text($this->groupoption_name);
	}

}
