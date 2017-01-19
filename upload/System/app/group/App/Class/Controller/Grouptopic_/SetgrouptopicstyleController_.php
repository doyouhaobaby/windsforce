<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   设置帖子浏览风格控制器($$)*/

!defined('Q_PATH') && exit;

class Setgrouptopicstyle_C_Controller extends InitController{

	public function index(){
		$nStyle=intval(Q::G('style','G'));
		if(!in_array($nStyle,array(1,2))){
			$nStyle=1;
		}

		Q::cookie('group_grouptopicstyle',$nStyle);

		$this->S(Q::L('帖子样式切换成功','Controller'));
	}

}
