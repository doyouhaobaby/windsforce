<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   活动参加用户模型($$)*/

!defined('Q_PATH') && exit;

class EventuserModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'eventuser',
			'autofill'=>array(
				array('user_id','userId','create','callback'),
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
		$this->eventuser_contact=C::text($this->eventuser_contact);
	}

}
