<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   用户签名预览($$)*/

!defined('Q_PATH') && exit;

class Usersign_C_Controller extends InitController{

	public function index(){
		$sContent=trim(Q::G('content'));
		echo Core_Extend::usersign($sContent);
	}

}
