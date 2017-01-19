<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   配置模型($$)*/

!defined('Q_PATH') && exit;

class OptionModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'option',
			'props'=>array(
				'option_name'=>array('readonly'=>true),
			),
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
		$this->option_name=C::text($this->option_name);
	}

}
