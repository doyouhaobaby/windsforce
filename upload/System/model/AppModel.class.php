<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   应用模型($$)*/

!defined('Q_PATH') && exit;

class AppModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'app',
			'check'=>array(
				'app_name'=>array(
					array('require',Q::L('应用名不能为空','__COMMON_LANG__@Common')),
					array('max_length',32,Q::L('应用名最大长度为32个字符','__COMMON_LANG__@Common')),
				),
				'app_identifier'=>array(
					array('require',Q::L('应用唯一识别符不能为空','__COMMON_LANG__@Common')),
					array('english',Q::L('应用唯一识别符只能为英文字符','__COMMON_LANG__@Common')),
					array('max_length',32,Q::L('应用唯一识别符最大长度为32个字符','__COMMON_LANG__@Common')),
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

	public function filterAppindentifier(){
		$this->app_identifier=str_replace(array('_','-'),array('',''),$this->app_identifier);
	}

	protected function beforeSave_(){
		$this->app_identifier=strtolower(C::text($this->app_identifier));
		$this->app_name=C::text($this->app_name);
		$this->app_email=C::strip($this->app_email);
		$this->app_url=C::strip($this->app_url);
		$this->app_author=C::text($this->app_author);
		$this->app_authorurl=C::strip($this->app_authorurl);
		$this->app_version=C::text($this->app_version);
		$this->app_description=C::text($this->app_description);
	}

}
