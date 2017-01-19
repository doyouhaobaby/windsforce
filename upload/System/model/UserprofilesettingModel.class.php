<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   用户个人信息字段配置模型($$)*/

!defined('Q_PATH') && exit;

class UserprofilesettingModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'userprofilesetting',
			'check'=>array(
				'userprofilesetting_title'=>array(
					array('require',Q::L('用户栏目名不能为空','__COMMON_LANG__@Common')),
					array('userprofilesettingTitle',Q::L('用户栏目名已经存在','__COMMON_LANG__@Common'),'condition'=>'must','extend'=>'callback'),
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

	public function userprofilesettingTitle(){
		return self::uniqueField_('userprofilesetting_title','userprofilesetting_id','id');
	}

	protected function beforeSave_(){
		$this->userprofilesetting_title=C::text($this->userprofilesetting_title);

		if($this->userprofilesetting_sort<0){
			$this->userprofilesetting_sort=0;
		}
		if($this->userprofilesetting_sort>999){
			$this->userprofilesetting_sort=999;
		}
	}

}
