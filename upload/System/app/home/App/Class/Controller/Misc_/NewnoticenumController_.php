<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   提醒条数($$)*/

!defined('Q_PATH') && exit;

class Newnoticenum_C_Controller extends InitController{

	public function index(){
		header("Content-Type:text/html; charset=utf-8");
		
		$arrData=array();

		$nUserId=intval(Q::G('uid'));
		if(empty($nUserId)){
			$arrData=array('num'=>0);
		}else{
			$arrWhere['notice_isread']=0;
			$arrWhere['user_id']=$GLOBALS['___login___']['user_id'];
			$arrData['num']=Model::F_('notice')->where($arrWhere)->all()->getCounts();
		}

		exit(json_encode($arrData));
	}

}
