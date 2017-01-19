<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   新鲜事类型模型($$)*/

!defined('Q_PATH') && exit;

class HomefreshcategoryModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'homefreshcategory',
			'check'=>array(
				'homefreshcategory_name'=>array(
					array('require',Q::L('文章类型名称不能为空','__APPHOME_COMMON_LANG__@Model')),
					array('max_length',255,Q::L('文章类型名称不能超过255个字符','__APPHOME_COMMON_LANG__@Model')),
				),
				'homefreshcategory_sort'=>array(
					array('number',Q::L('序号只能是数字','__APPHOME_COMMON_LANG__@Model'),'condition'=>'notempty','extend'=>'regex'),
				)
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

	public function getEventcategory(){
		return self::F()->order('homefreshcategory_id ASC,homefreshcategory_sort ASC')->all()->query();
	}

	public function rebuildHomefreshnum(){
		$arrCategorys=$this->getEventcategory();
		if(is_array($arrCategorys)){
			foreach($arrCategorys as $oCategory){
				$nHomefreshnums=Model::F_('homefresh','homefresh_status=1 AND homefreshcategory_id=?',$oCategory['homefreshcategory_id'])->all()->getCounts();
				Model::M_('homefreshcategory')->updateWhere(array('homefreshcategory_count'=>$nHomefreshnums),'homefreshcategory_id=?',$oCategory['homefreshcategory_id']);
			}
		}
	}

	protected function beforeSave_(){
		$this->homefreshcategory_name=C::text($this->homefreshcategory_name);
		
		if($this->homefreshcategory_sort<0){
			$this->homefreshcategory_sort=0;
		}
		if($this->homefreshcategory_sort>999){
			$this->homefreshcategory_sort=999;
		}
	}

}
