<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   活动感兴趣用户模型($$)*/

!defined('Q_PATH') && exit;

class EventattentionuserModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'eventattentionuser',
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

}
