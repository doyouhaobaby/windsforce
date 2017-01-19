<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   Home缓存控制器($$)*/

!defined('Q_PATH') && exit;

class AppcacheController extends AController{

	public function index($sModel=null,$bDisplay=true){
		// 更新缓存
		if(!Q::classExists('Cache_Extend')){
			require_once(Core_Extend::includeFile('function/Cache_Extend'));
		}
		Cache_Extend::appUpdateCache('','home');
		
		$this->assign('__JumpUrl__',Q::U('globalcache/index'));

		$this->S(Q::L('缓存更新成功','Controller'));
	}

}
