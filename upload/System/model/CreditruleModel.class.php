<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   积分规则模型($$)*/

!defined('Q_PATH') && exit;

class CreditruleModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'creditrule',
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
		$this->creditrule_name=C::text($this->creditrule_name);
		$this->creditrule_action=C::text($this->creditrule_action);
	}

}
