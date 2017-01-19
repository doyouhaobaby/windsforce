<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   节点分组模型($$)*/

!defined('Q_PATH') && exit;

class NodegroupModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'nodegroup',
			'check'=>array(
				'nodegroup_name'=>array(
					array('require',Q::L('组名不能为空','__COMMON_LANG__@Common')),
					array('english',Q::L('组名只能为英文字符','__COMMON_LANG__@Common')),
					array('max_length',50,Q::L('组名最大长度为50个字符','__COMMON_LANG__@Common')),
					array('nodegroupName',Q::L('组名已经存在','__COMMON_LANG__@Common'),'condition'=>'must','extend'=>'callback'),
				),
				'nodegroup_title'=>array(
					array('require',Q::L('组显示名不能为空','__COMMON_LANG__@Common')),
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

	public function nodegroupName(){
		return self::uniqueField_('nodegroup_name','nodegroup_id','id');
	}

	protected function beforeSave_(){
		$this->nodegroup_name=C::text($this->nodegroup_name);
		$this->nodegroup_title=C::text($this->nodegroup_title);
		
		if($this->nodegroup_sort<0){
			$this->nodegroup_sort=0;
		}
		if($this->nodegroup_sort>999){
			$this->nodegroup_sort=999;
		}
	}

}
