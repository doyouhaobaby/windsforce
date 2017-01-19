<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   小组分类缓存($$)*/

!defined('Q_PATH') && exit;

class AppUpdateCacheCategory{

	public static function cache(){
		$arrData=array();

		$arrCategorys=Model::F_('groupcategory','groupcategory_parentid=?',0)
			->setColumns('groupcategory_id,groupcategory_name,groupcategory_count')
			->order('groupcategory_sort ASC')
			->getAll();
		foreach($arrCategorys as $arrVal){
			$arrVal['child']=self::child_($arrVal['groupcategory_id']);
			$arrData[$arrVal['groupcategory_id']]=$arrVal;
		}

		Core_Extend::saveSyscache('group_category',$arrData);
	}

	protected static function child_($nId){
		return Model::F_('groupcategory','groupcategory_parentid=?',$nId)
			->setColumns('groupcategory_id,groupcategory_name,groupcategory_count')
			->order('groupcategory_sort ASC')
			->getAll();
	}

}
