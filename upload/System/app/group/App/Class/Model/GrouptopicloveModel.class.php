<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   群组喜欢帖子数据模型($$)*/

!defined('Q_PATH') && exit;

class GrouptopicloveModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'grouptopiclove',
			'autofill'=>array(
				array('user_id','userId','create','callback'),
				array('grouptopiclove_username','userName','create','callback'),
			),
			'check'=>array(
				'grouptopiclove_title'=>array(
					array('require',Q::L('喜欢备注不能为空','__APPGROUP_COMMON_LANG__@Model')),
					array('max_length',300,Q::L('喜欢备注不能超过300个字符','__APPGROUP_COMMON_LANG__@Model')),
				),
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
		$this->grouptopiclove_username=C::text($this->grouptopiclove_username);
		$this->grouptopiclove_note=C::text($this->grouptopiclove_note);
	}

}
