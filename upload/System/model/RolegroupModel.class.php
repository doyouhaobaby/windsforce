<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   角色分组模型($$)*/

!defined('Q_PATH') && exit;

class RolegroupModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'rolegroup',
			'check'=>array(
				'rolegroup_name'=>array(
					array('require',Q::L('组名不能为空','__COMMON_LANG__@Common')),
					array('max_length',50,Q::L('组名最大长度为50个字符','__COMMON_LANG__@Common')),
					array('english',Q::L('组名只能为英文字符','__COMMON_LANG__@Common')),
					array('rolegroupName',Q::L('组名已经存在','__COMMON_LANG__@Common'),'condition'=>'must','extend'=>'callback'),
				),
				'rolegroup_title'=>array(
					array('require',Q::L('组显示名不能为空','__COMMON_LANG__@Common')),
					array('max_length',50,Q::L('显示名最大长度为50个字符','__COMMON_LANG__@Common')),
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

	public function rolegroupName(){
		return self::uniqueField_('rolegroup_name','rolegroup_id','id');
	}

	protected function beforeSave_(){
		$this->rolegroup_name=C::text($this->rolegroup_name);
		$this->rolegroup_title=C::text($this->rolegroup_title);

		if($this->rolegroup_sort<0){
			$this->rolegroup_sort=0;
		}
		if($this->rolegroup_sort>999){
			$this->rolegroup_sort=999;
		}
	}

}
