<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   权限节点缓存($$)*/

!defined('Q_PATH') && exit;

class UpdateCacheNode{

	public static function cache(){
		$arrData=array();

		$arrData=Rbac::getNodeList();

		Core_Extend::saveSyscache('node',$arrData);
	}

}
