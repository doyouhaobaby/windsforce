<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   群组帖子分类模型($$)*/

!defined('Q_PATH') && exit;

class GrouptopiccategoryModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'grouptopiccategory',
			'check'=>array(
				'grouptopiccategory_name'=>array(
					array('require',Q::L('群组帖子分类不能为空','__APPGROUP_COMMON_LANG__@Model')),
					array('max_length',30,Q::L('群组帖子分类不能超过30个字符','__APPGROUP_COMMON_LANG__@Model'))
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
	
	public function insertGroupcategory($nGroupId){
		$this->group_id=$nGroupId;
		$this->save('create');
	}

	protected function beforeSave_(){
		$this->grouptopiccategory_name=C::text($this->grouptopiccategory_name);
		
		if($this->grouptopiccategory_sort<0){
			$this->grouptopiccategory_sort=0;
		}
		if($this->grouptopiccategory_sort>999){
			$this->grouptopiccategory_sort=999;
		}
	}

	public function grouptopiccategoryByGroupid($nGroupid){
		return self::F('group_id=?',$nGroupid)->order('grouptopiccategory_sort DESC')->getAll();
	}

}
