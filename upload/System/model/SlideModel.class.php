<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   滑动幻灯片模型($$)*/

!defined('Q_PATH') && exit;

class SlideModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'slide',
			'check'=>array(
				'slide_title'=>array(
					array('require',Q::L('幻灯片标题不能为空','__COMMON_LANG__@Common')),
					array('max_length',50,Q::L('幻灯片标题最大长度为50个字符','__COMMON_LANG__@Common')),
				),
				'slide_img'=>array(
					array('require',Q::L('幻灯片图片不能为空','__COMMON_LANG__@Common')),
					array('max_length',325,Q::L('幻灯片图片最大长度为325个字符','__COMMON_LANG__@Common')),
				),
				'slide_url'=>array(
					array('require',Q::L('幻灯片URL不能为空','__COMMON_LANG__@Common')),
					array('max_length',325,Q::L('幻灯片URL最大长度为325个字符','__COMMON_LANG__@Common')),
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
		$this->slide_title=C::text($this->slide_title);
		$this->slide_url=C::strip($this->slide_url);
		$this->slide_img=C::strip($this->slide_img);
		
		if($this->slide_sort<0){
			$this->slide_sort=0;
		}
		if($this->slide_sort>999){
			$this->slide_sort=999;
		}
	}

}
