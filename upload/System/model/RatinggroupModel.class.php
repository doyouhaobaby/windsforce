<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   级别分组模型($$)*/

!defined('Q_PATH') && exit;

class RatinggroupModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'ratinggroup',
			'check'=>array(
				'ratinggroup_name'=>array(
					array('require',Q::L('组名不能为空','__COMMON_LANG__@Common')),
					array('english',Q::L('组名只能为英文字符','__COMMON_LANG__@Common')),
					array('max_length',50,Q::L('组名最大长度为50个字符','__COMMON_LANG__@Common')),
					array('ratinggroupName',Q::L('组名已经存在','__COMMON_LANG__@Common'),'condition'=>'must','extend'=>'callback'),
				),
				'ratinggroup_title'=>array(
					array('require',Q::L('组显示名不能为空','__COMMON_LANG__@Common')),
					array('max_length',50,Q::L('组显示名最大长度为50个字符','__COMMON_LANG__@Common')),
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

	public function ratinggroupName(){
		return self::uniqueField_('ratinggroup_name','ratinggroup_id','id');
	}

	protected function beforeSave_(){
		$this->ratinggroup_name=C::text($this->ratinggroup_name);
		$this->ratinggroup_title=C::text($this->ratinggroup_title);

		if($this->ratinggroup_sort<0){
			$this->ratinggroup_sort=0;
		}
		if($this->ratinggroup_sort>999){
			$this->ratinggroup_sort=999;
		}
	}

}
