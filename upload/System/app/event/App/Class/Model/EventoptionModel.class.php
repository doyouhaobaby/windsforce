<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   配置模型($$)*/

!defined('Q_PATH') && exit;

class EventoptionModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'eventoption',
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
		$this->eventoption_name=C::text($this->eventoption_name);
	}

}
