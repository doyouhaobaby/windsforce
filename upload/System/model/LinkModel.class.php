<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   友情链接模型($$)*/

!defined('Q_PATH') && exit;

class LinkModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'link',
			'check'=>array(
				'link_sort'=>array(
					array('number',Q::L('序号只能是数字','__COMMON_LANG__@Common')),
				),
				'link_name'=>array(
					array('require',Q::L('链接名字不能为空','__COMMON_LANG__@Common')),
					array('max_length',32,Q::L('链接名字最大长度为32','__COMMON_LANG__@Common'))
				),
				'link_url'=>array(
					array('require',Q::L('链接URL 不能为空','__COMMON_LANG__@Common')),
					array('max_length',250,Q::L('链接Url 最大长度为250','__COMMON_LANG__@Common')),
				),
				'link_logo'=>array(
					array('empty'),
					array('max_length',360,Q::L('链接Logo 最大长度为360','__COMMON_LANG__@Common')),
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
		$this->link_name=C::text($this->link_name);
		$this->link_description=C::text($this->link_description);
		$this->link_logo=C::strip($this->link_logo);
		$this->link_url=C::strip($this->link_url);

		if($this->link_sort<0){
			$this->link_sort=0;
		}
		if($this->link_sort>999){
			$this->link_sort=999;
		}
	}

}
