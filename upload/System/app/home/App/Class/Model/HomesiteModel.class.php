<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   网站联系信息模型($$)*/

!defined('Q_PATH') && exit;

class HomesiteModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'homesite',
			'check'=>array(
				'homesite_name'=>array(
					array('require',Q::L('信息名字不能为空','__APPHOME_COMMON_LANG__@Model')),
					array('number_underline_english',Q::L('信息名字只能是由数字，下划线，字母组成','__APPHOME_COMMON_LANG__@Model')),
					array('max_length',32,Q::L('信息名字不能超过32个字符','__APPHOME_COMMON_LANG__@Model')),
					array('homesiteName',Q::L('信息名字已经存在','__APPHOME_COMMON_LANG__@Model'),'condition'=>'must','extend'=>'callback'),
				),
				'homesite_nikename'=>array(
					array('require',Q::L('信息别名不能为空','__APPHOME_COMMON_LANG__@Model')),
					array('max_length',32,Q::L('信息别名不能超过32个字符','__APPHOME_COMMON_LANG__@Model')),
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

	public function homesiteName(){
		return self::uniqueField_('homesite_name','homesite_id','value');
	}

	protected function beforeSave_(){
		$this->homesite_name=C::text($this->homesite_name);
		$this->homesite_nikename=C::text($this->homesite_nikename);
		$this->homesite_content=Core_Extend::replaceAttachment(C::cleanJs($this->homesite_content));
	}

}
