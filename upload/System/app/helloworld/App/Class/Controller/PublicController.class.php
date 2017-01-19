<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   Helloworld首页显示($$)*/

!defined('Q_PATH') && exit;

class PublicController extends InitController{

	public function index(){
		Core_Extend::getSeo($this,array('title'=>'Hello world!'));
		$this->display('public+index');
	}

}
