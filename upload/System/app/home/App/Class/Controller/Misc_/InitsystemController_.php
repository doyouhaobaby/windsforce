<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   系统安装程序初始化控制器($$)*/

!defined('Q_PATH') && exit;

class Initsystem_C_Controller extends InitController{

	public function index(){
		// 更新系统缓存
		require_once(Core_Extend::includeFile('function/Cache_Extend'));
		Cache_Extend::updateCache();
	}

}
