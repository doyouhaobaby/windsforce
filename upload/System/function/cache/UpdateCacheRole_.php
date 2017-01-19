<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   角色缓存($$)*/

!defined('Q_PATH') && exit;

class UpdateCacheRole{

	public static function cache(){
		$arrData=array();

		$arrData=Model::F_('role','role_status=1')
			->setColumns('role_id,role_name,role_nikename,rolegroup_id')
			->order('role_id DESC')
			->getAll();

		Core_Extend::saveSyscache('role',$arrData);
	}

}
