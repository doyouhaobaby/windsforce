<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   新鲜事分类缓存($$)*/

!defined('Q_PATH') && exit;

class UpdateCacheCategory{

	public static function cache(){
		$arrData=Model::F_('homefreshcategory')
			->setColumns('homefreshcategory_id,homefreshcategory_name,homefreshcategory_count')
			->order('homefreshcategory_sort ASC,homefreshcategory_id DESC')
			->getAll();

		Core_Extend::saveSyscache('category',$arrData);
	}

}
