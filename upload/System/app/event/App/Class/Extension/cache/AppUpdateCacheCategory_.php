<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   活动分类缓存($$)*/

!defined('Q_PATH') && exit;

class AppUpdateCacheCategory{

	public static function cache(){
		$arrData=array();
		
		$arrData=Model::F_('eventcategory','eventcategory_parentid=?',0)
			->setColumns('eventcategory_id,eventcategory_name,eventcategory_count')
			->order('eventcategory_sort ASC')
			->getAll();

		foreach($arrData as &$arrVal){
			$arrVal['child']=self::child_($arrVal['eventcategory_id']);
		}

		Core_Extend::saveSyscache('event_category',$arrData);
	}

	protected static function child_($nId){
		return Model::F_('eventcategory','eventcategory_parentid=?',$nId)
			->setColumns('eventcategory_id,eventcategory_name,eventcategory_count')
			->order('eventcategory_sort ASC')
			->getAll();
	}

}
