<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   没有权限访问($$)*/

!defined('Q_PATH') && exit;

class Rbacerror_C_Controller extends InitController{

	public function index(){
		$sRbacerrorreferer=trim(Q::cookie('_rbacerror_referer_'));
		if(empty($sRbacerrorreferer)){
			Q::cookie('_rbacerror_referer_',null,-1);
			$this->U('home://public/index');
		}

		Core_Extend::getSeo($this,array('title'=>Q::L('没有权限访问','Controller')));

		$this->assign('sRbacerrorreferer',$sRbacerrorreferer);
		$this->display('public+rbacerror');
	}

}
