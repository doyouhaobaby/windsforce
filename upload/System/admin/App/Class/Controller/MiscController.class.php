<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   后台杂项控制器($$)*/

!defined('Q_PATH') && exit;

class MiscController extends AController{

	public function index($sModel=null,$bDisplay=true){
		$sFiles=trim(Q::G('file','G'));
		$sType=trim(Q::G('type','G'));

		Admintheme_Extend::pathContent($sFiles,$sType);
	}

}
