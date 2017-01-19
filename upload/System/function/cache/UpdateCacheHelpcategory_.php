<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   帮助分类缓存($$)*/

!defined('Q_PATH') && exit;

class UpdateCacheHelpcategory{

	public static function cache(){
		$arrData=array();

		$arrData=Model::F_('homehelpcategory')
			->setColumns('homehelpcategory_id,homehelpcategory_name')
			->order('homehelpcategory_sort DESC,homehelpcategory_id ASC')
			->getAll();

		Core_Extend::saveSyscache('helpcategory',$arrData);
	}

}
