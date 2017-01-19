<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   角色分组缓存($$)*/

!defined('Q_PATH') && exit;

class UpdateCacheRolegroup{

	public static function cache(){
		$arrData=array();

		$arrData=Model::F_('rolegroup','rolegroup_status=1')
			->setColumns('rolegroup_id,rolegroup_name,rolegroup_title')
			->order('rolegroup_sort DESC,rolegroup_id ASC')
			->getAll();

		Core_Extend::saveSyscache('rolegroup',$arrData);
	}

}
