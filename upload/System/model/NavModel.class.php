<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   菜单模型($$)*/

!defined('Q_PATH') && exit;

class NavModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'nav',
			'check'=>array(
				'nav_name'=>array(
					array('require',Q::L('导航条名字不能为空','__COMMON_LANG__@Common')),
					array('max_length',32,Q::L('导航条名字最大长度为32','__COMMON_LANG__@Common'))
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

	public function customIdentifier(){
		$this->nav_identifier='custom_'.C::randString(6);
	}

	protected function beforeSave_(){
		$this->nav_name=C::text($this->nav_name);
		$this->nav_identifier=C::text($this->nav_identifier);
		$this->nav_title=C::text($this->nav_title);
		$this->nav_url=C::text($this->nav_url);
		$this->nav_style=C::strip($this->nav_style);
		$this->nav_icon=C::strip($this->nav_icon);

		if($this->nav_sort<0){
			$this->nav_sort=0;
		}
		if($this->nav_sort>999){
			$this->nav_sort=999;
		}
	}

}
