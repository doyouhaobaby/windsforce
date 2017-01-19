<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   积分记录操作信息模型($$)*/

!defined('Q_PATH') && exit;

class CreditoperationModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'creditoperation',
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
		$this->creditoperation_name=C::text($this->creditoperation_name);
		$this->creditoperation_title=C::text($this->creditoperation_title);
	}

}
