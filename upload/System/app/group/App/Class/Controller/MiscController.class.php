<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   小组杂项控制器($$)*/

!defined('Q_PATH') && exit;

class MiscController extends InitController{

	public function groupname(){
		$this->child('Misc@Groupname','index');
	}
	
}
