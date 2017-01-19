<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   群组Api接口控制器($$)*/

!defined('Q_PATH') && exit;

class ApiController extends InitController{

	public function new_topic(){
		$this->child('Api@Newtopic','index');
	}

	public function hot_topic(){
		$this->child('Api@Hottopic','index');
	}

	public function recommend_group(){
		$this->child('Api@Recommendgroup','index');
	}

}
