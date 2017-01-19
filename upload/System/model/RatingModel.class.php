<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   级别模型($$)*/

!defined('Q_PATH') && exit;

class RatingModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'rating',
			'check'=>array(
				'rating_name'=>array(
					array('require',Q::L('级别名不能为空','__COMMON_LANG__@Common')),
					array('max_length',50,Q::L('级别名最大长度为50个字符','__COMMON_LANG__@Common')),
					array('uniqueRatingName',Q::L('组名已经存在','__COMMON_LANG__@Common'),'condition'=>'must','extend'=>'callback'),
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
	
	public function uniqueRatingName(){
		return self::uniqueField_('rating_name','rating_id','id');
	}

	protected function beforeSave_(){
		$this->rating_name=C::text($this->rating_name);
		$this->rating_nikename=C::text($this->rating_nikename);
		$this->rating_remark=C::text($this->rating_remark);
	}

}
