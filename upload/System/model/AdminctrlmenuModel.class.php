<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   后台快捷菜单模型($$)*/

!defined('Q_PATH') && exit;

class AdminctrlmenuModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'adminctrlmenu',
			'autofill'=>array(
				array('user_id','userId','create','callback'),
				array('adminctrlmenu_admin','userName','create','callback'),
			),
			'check'=>array(
				'adminctrlmenu_title'=>array(
					array('require',Q::L('快捷导航标题不能为空','__COMMON_LANG__@Common')),
					array('max_length',50,Q::L('快捷导航标题最大长度为50个字符','__COMMON_LANG__@Common')),
				),
				'adminctrlmenu_url'=>array(
					array('require',Q::L('快捷导航URL地址不能为空','__COMMON_LANG__@Common')),
					array('max_length',255,Q::L('快捷导航URL地址最大长度为255个字符','__COMMON_LANG__@Common')),
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
		$this->adminctrlmenu_title=C::text($this->adminctrlmenu_title);
		$this->adminctrlmenu_url=C::strip($this->adminctrlmenu_url);
		$this->adminctrlmenu_admin=C::text($this->adminctrlmenu_admin);
	}

}
