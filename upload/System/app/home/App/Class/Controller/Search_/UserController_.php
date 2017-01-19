<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   用户搜索($$)*/

!defined('Q_PATH') && exit;

class User_C_Controller extends InitController{

	public function index(){
		$sKey=urldecode(trim(Q::G('key')));
		$sKey=htmlspecialchars($sKey);

		if(!empty($sKey)){
			C::urlGo(Q::U('home://friend/searchresult?user_name='.urlencode($sKey),array(),true));
		}

		Core_Extend::getSeo($this,array('title'=>Q::L('用户搜索','Controller')));

		$this->assign('sKey',$sKey);
		$this->display('search+user');
	}

}
