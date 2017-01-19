<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   添加标签($$)*/

!defined('Q_PATH') && exit;

class Add_C_Controller extends InitController{

	public function index(){
		$nUserId=$GLOBALS['___login___']['user_id'];
		$sHometagName=Q::G('hometag_name');

		$oTag=Q::instance('HometagModel');
		$oTag->addTag($nUserId,$sHometagName);
		if($oTag->isError()){
			$this->E($oTag->getErrorMessage());
		}

		$this->S(Q::L('添加用户标签成功','Controller'));
	}

}
