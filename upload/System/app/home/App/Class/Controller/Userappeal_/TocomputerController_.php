<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   用户申诉信息保存到电脑($$)*/

!defined('Q_PATH') && exit;

class Tocomputer_C_Controller extends InitController{

	public function index(){
		if($GLOBALS['___login___']!==false){
			$this->U('home://ucenter/index');
		}

		$nAppealId=intval(Q::G('id','G'));
		$sUserid=trim(Q::G('user_id','G'));

		$sUserid=C::authcode($sUserid);
		if(empty($sUserid)){
			$this->E(Q::L('页面已过期','Controller'));
		}

		$oUser=UserModel::F('user_id=?',$sUserid)->getOne();
		if(empty($oUser->user_id)){
			$this->E(Q::L('Email账号不存在','Controller'));
		}

		if($oUser->user_status==0){
			$this->E(Q::L('该账户已经被禁止','Controller'));
		}

		if(empty($nAppealId)){
			$this->E(Q::L('无法获取申诉ID','Controller'));
		}

		$oAppeal=AppealModel::F('appeal_id=?',$nAppealId)->getOne();
		if(empty($oAppeal->appeal_id)){
			$this->E(Q::L('无效的申诉ID','Controller'));
		}

		$sName='APPEAL_'.date('Y_m_d_H_i_s',CURRENT_TIMESTAMP).'.txt';

		header('Content-Type: text/plain');
		header('Content-Disposition: attachment;filename="'.$sName.'"');
		if(preg_match("/MSIE([0-9].[0-9]{1,2})/",$_SERVER['HTTP_USER_AGENT'])){
			header('Cache-Control: must-revalidate,post-check=0,pre-check=0');
			header('Pragma: public');
		}else{
			header('Pragma: no-cache');
		}
		
		$sAppealscheduleUrl=Core_Extend::windsforceOuter('app=home&c=userappeal&a=schedule');
		$sNlbr="\r\n";

		$sContent='['.$GLOBALS['_option_']['site_name'].']'.Q::L('用户申诉回执单','Controller').$sNlbr;
		$sContent.='-----------------------------------------------------'.$sNlbr;
		$sContent.=Q::L('申诉人','Controller').':'.$oAppeal->appeal_realname.$sNlbr.$sNlbr;
		$sContent.=Q::L('申诉回执编号','Controller').':'.$oAppeal->appeal_receiptnumber.$sNlbr.$sNlbr;
		$sContent.='--'.Q::L('请牢记你的申诉编号，以便于随时查询申诉进度','Controller').$sNlbr;
		$sContent.=$sAppealscheduleUrl.$sNlbr.$sNlbr;
		$sContent.=Q::L('接受申诉结果的Email','Controller').':'.$oAppeal->appeal_email.$sNlbr.$sNlbr;
		$sContent.='-----------------------------------------------------'.$sNlbr;
		$sContent.=date('Y-m-d H:i',CURRENT_TIMESTAMP);

		echo $sContent;
	}

}
