<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   群组分类模型($$)*/

!defined('Q_PATH') && exit;

class GroupcategoryModel extends CommonModel{

	static public function init__(){
		return array(
			'table_name'=>'groupcategory',
			'check'=>array(
				'groupcategory_name'=>array(
					array('require',Q::L('群组分类不能为空','__APPGROUP_COMMON_LANG__@Model')),
					array('max_length',32,Q::L('群组分类不能超过32个字符','__APPGROUP_COMMON_LANG__@Model'))
				),
				'groupcategory_parentid'=>array(
					array('groupcategoryParentId',Q::L('群组分类不能为自己','__APPGROUP_COMMON_LANG__@Model'),'condition'=>'must','extend'=>'callback'),
					array('groupcategoryParentLevel',Q::L('群组分类只允许二级分类','__APPGROUP_COMMON_LANG__@Model'),'condition'=>'must','extend'=>'callback'),
				),
				'groupcategory_sort'=>array(
					array('number',Q::L('序号只能是数字','__APPGROUP_COMMON_LANG__@Model'),'condition'=>'notempty','extend'=>'regex'),
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

	public function groupcategoryParentId(){
		$nGroupcategoryId=Q::G('value');
		$nGroupcategoryParentid=Q::G('groupcategory_parentid');
		if(!empty($nGroupcategoryId) && !empty($nGroupcategoryParentid) && $nGroupcategoryId==$nGroupcategoryParentid){
			return false;
		}

		return true;
	}

	public function groupcategoryParentLevel(){
		$nGroupcategoryId=Q::G('value');
		$nGroupcategoryParentid=Q::G('groupcategory_parentid');
		if($nGroupcategoryParentid==0){
			return true;
		}

		$nParentId=self::F('groupcategory_id=?',$nGroupcategoryParentid)->getColumn('groupcategory_parentid');
		if($nParentId>0){
			return false;
		}

		return true;
	}

	public function getGroupcategory(){
		return self::F()->order('groupcategory_id ASC,groupcategory_sort DESC')->all()->query();
	}

	public function getParentGroupcategory($nParentGroupcategoryId){
		if($nParentGroupcategoryId==0){
			return Q::L('顶级分类','__APPGROUP_COMMON_LANG__@Model');
		}else{
			$oGroupcategory=self::F('groupcategory_id=?',$nParentGroupcategoryId)->query();
			if(!empty($oGroupcategory->groupcategory_id)){
				return $oGroupcategory->groupcategory_name;
			}else{
				return Q::L('群组父级分类已经损坏，你可以编辑分类进行修复','__APPGROUP_COMMON_LANG__@Model');
			}
		}
	}
	
	public function getGroupcategoryTree(){
		$arrGroupcategorys=$this->getGroupcategory();
		$oGroupcategoryTree=new TreeCategory();
		foreach($arrGroupcategorys as $oCategory){
			$oGroupcategoryTree->setNode($oCategory->groupcategory_id,$oCategory->groupcategory_parentid,$oCategory->groupcategory_name);
		}
		return $oGroupcategoryTree;
	}

	protected function beforeSave_(){
		$this->groupcategory_name=C::text($this->groupcategory_name);

		if($this->groupcategory_sort<0){
			$this->groupcategory_sort=0;
		}
		if($this->groupcategory_sort>999){
			$this->groupcategory_sort=999;
		}
	}

}
