<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   群组Wap控制器($$)*/

!defined('Q_PATH') && exit;

/** wap页面 */
define('IN_WAP',true);

class WapController extends WInitController{

	public function index(){
		$this->child('Wap@Grouptopic/Index','index');
	}
	
	public function show(){
		$this->child('Wap@Grouptopic/Show','index');
	}

}
