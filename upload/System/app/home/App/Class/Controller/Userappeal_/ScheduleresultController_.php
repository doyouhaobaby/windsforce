<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   申诉结果($$)*/

!defined('Q_PATH') && exit;

class Scheduleresult_C_Controller extends InitController{

	public function index(){
		if($GLOBALS['___login___']!==false){
			$this->U('home://ucenter/index');
		}

		$this->_oParent->check_seccode(true);

		$sAppealReceiptnumber=trim(Q::G('appeal_receiptnumber','P'));
		$sAppealEmail=trim(Q::G('appeal_email','P'));
		if(empty($sAppealReceiptnumber)){
			$this->E(Q::L('申诉回执编号不能为空','Controller'));
		}

		if(empty($sAppealEmail)){
			$this->E(Q::L('申诉邮箱不能为空','Controller'));
		}

		Check::RUN();
		if(!Check::C($sAppealEmail,'email')){
			$this->E(Q::L('申诉邮箱错误','Controller'));
		}

		$oAppeal=AppealModel::F('appeal_email=? AND appeal_receiptnumber=?',$sAppealEmail,$sAppealReceiptnumber)->getOne();
		if(empty($oAppeal->appeal_id)){
			$this->E(Q::L('申诉回执编号或者申诉邮箱错误,又或者该申诉回执已被删除','Controller'));
		}

		if($oAppeal->appeal_status==0){
			$this->E(Q::L('该申诉回执已经被关闭','Controller'));
		}

		Core_Extend::getSeo($this,array('title'=>Q::L('申诉结果','Controller')));

		$this->assign('oAppeal',$oAppeal);
		$this->display('userappeal+scheduleresult');
	}

}
