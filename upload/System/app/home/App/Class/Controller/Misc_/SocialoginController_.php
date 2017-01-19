<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   发送社会化登陆时间控制器($$)*/

!defined('Q_PATH') && exit;

class Socialogin_C_Controller extends InitController{

	public function index(){
		$nTime=intval(Q::G('time'));
		if($nTime>0){
			Q::cookie('SOCIA_LOGIN_TIME',$nTime);
		}

		$this->S(Q::L('登陆COOKIE有效期','Controller').' '.$nTime.' (S)',0);
	}

}
