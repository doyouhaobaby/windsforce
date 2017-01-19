<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   验证码验证($$)*/

!defined('Q_PATH') && exit;

class Validateseccode_C_Controller extends InitController{

	public function index(){
		$sSeccode=trim(Q::G('seccode'));
		if(empty($sSeccode)){
			exit('false');
		}
		
		$bResult=Core_Extend::checkSeccode($sSeccode);
		if(!$bResult){
			exit('false');
		}
		
		exit('true');
	}

}
