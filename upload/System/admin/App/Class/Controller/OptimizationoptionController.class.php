<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   性能优化控制器($$)*/

!defined('Q_PATH') && exit;

class OptimizationoptionController extends OptionController{

	public function index($sModel=null,$bDisplay=true){
		$arrOptionData=$GLOBALS['_option_'];
		$this->assign('arrOptions',$arrOptionData);
		$this->display();
	}

}
