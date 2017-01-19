<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   申诉信息模型($$)*/

!defined('Q_PATH') && exit;

class AppealModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'appeal',
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
		$this->appeal_realname=C::text($this->appeal_realname);
		$this->appeal_address=C::text($this->appeal_address);
		$this->appeal_idnumber=C::text($this->appeal_idnumber);
		$this->appeal_email=C::strip($this->appeal_email);
		$this->appeal_receiptnumber=C::text($this->appeal_receiptnumber);
		$this->appeal_reason=C::text($this->appeal_reason);
	}

}
