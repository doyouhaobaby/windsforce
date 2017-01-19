<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   帮组分类模型($$)*/

!defined('Q_PATH') && exit;

class HomehelpcategoryModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'homehelpcategory',
			'check'=>array(
				'homehelpcategory_name'=>array(
					array('require',Q::L('帮助分类不能为空','__APPHOME_COMMON_LANG__@Model')),
					array('max_length',32,Q::L('帮助分类不能超过32个字符','__APPHOME_COMMON_LANG__@Model')),
					array('homehelpcategoryName',Q::L('帮助分类名字已经存在','__APPHOME_COMMON_LANG__@Model'),'condition'=>'must','extend'=>'callback'),
				),
				'homehelpcategory_sort'=>array(
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

	public function homehelpcategoryName(){
		return self::uniqueField_('homehelpcategory_name','homehelpcategory_id','value');
	}

	public function getHomehelpcategory(){
		return self::F()->order('homehelpcategory_sort DESC,homehelpcategory_id ASC')->all()->query();
	}

	protected function beforeSave_(){
		$this->homehelpcategory_name=C::text($this->homehelpcategory_name);
		
		if($this->homehelpcategory_sort<0){
			$this->homehelpcategory_sort=0;
		}
		if($this->homehelpcategory_sort>999){
			$this->homehelpcategory_sort=999;
		}
	}

	public function homehelpcategoryCount($nHomehelpcategoryId){
		if(empty($nHomehelpcategoryId)){
			return;
		}

		// 更新站点帮组分类的数量
		$nHomehelpNums=HomehelpModel::F('homehelpcategory_id=?',$nHomehelpcategoryId)->all()->getCounts();
		$oHomehelpcategory=HomehelpcategoryModel::F('homehelpcategory_id=?',$nHomehelpcategoryId)->query();
		$oHomehelpcategory->homehelpcategory_count=$nHomehelpNums;
		$oHomehelpcategory->save('update');
	}

}
