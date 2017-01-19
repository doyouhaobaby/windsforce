<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   后台快捷菜单缓存($$)*/

!defined('Q_PATH') && exit;

class UpdateCacheAdminctrlmenu{

	public static function cache(){
		$arrData=array();

		$arrAdminctrlmenus=Model::F_('adminctrlmenu','adminctrlmenu_status=?',1)
			->setColumns('adminctrlmenu_id,adminctrlmenu_title,adminctrlmenu_url')
			->order('adminctrlmenu_sort ASC,create_dateline ASC')
			->getAll();
		if(is_array($arrAdminctrlmenus)){
			foreach($arrAdminctrlmenus as $oAdminctrlmenu){
				$arrData[]=array(
					'adminctrlmenu_id'=>$oAdminctrlmenu['adminctrlmenu_id'],
					'adminctrlmenu_title'=>$oAdminctrlmenu['adminctrlmenu_title'],
					'adminctrlmenu_url'=>$oAdminctrlmenu['adminctrlmenu_url'],
				);
			}
		}

		Core_Extend::saveSyscache('adminctrlmenu',$arrData);
	}

}
