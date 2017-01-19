<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   网站帮组模型($$)*/

!defined('Q_PATH') && exit;

class HomehelpModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'homehelp',
			'autofill'=>array(
				array('user_id','userId','create','callback'),
				array('homehelp_username','userName','create','callback'),
				array('homehelp_updateuserid','userId','update','callback'),
				array('homehelp_updateusername','userName','update','callback'),
			),
			'check'=>array(
				'homehelp_title'=>array(
					array('require',Q::L('帮助标题不能为空','__APPHOME_COMMON_LANG__@Model')),
					array('max_length',250,Q::L('帮助标题不能超过250个字符','__APPHOME_COMMON_LANG__@Model')),
				),
				'homehelp_content'=>array(
					array('require',Q::L('帮助内容不能为空','__APPHOME_COMMON_LANG__@Model')),
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
		$this->homehelp_title=C::text($this->homehelp_title);
		$this->homehelp_content=Core_Extend::replaceAttachment(C::cleanJs($this->homehelp_content));
		$this->homehelp_username=C::text($this->homehelp_username);
		$this->homehelp_updateusername=C::text($this->homehelp_updateusername);
	}

}
