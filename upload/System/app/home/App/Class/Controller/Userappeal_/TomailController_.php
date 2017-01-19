<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   用户申诉信息保存到邮件($$)*/

!defined('Q_PATH') && exit;

class Tomail_C_Controller extends InitController{

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
		
		$oMailModel=Q::instance('MailModel');
		$oMailConnect=$oMailModel->getMailConnect();

		$sAppealscheduleUrl=Core_Extend::windsforceOuter('app=home&c=userappeal&a=schedule');
		$sNlbr=$oMailConnect->getIsHtml()===true?'<br/>':"\r\n";

		$sEmailSubject=$GLOBALS['_option_']['site_name'].Q::L('用户申诉回执单','Controller');
		$sEmailContent='<b>'.Q::L('尊敬的用户','Controller').':</b>'.$sNlbr;
		$sEmailContent.='-----------------------------------------------------'.$sNlbr;
		$sEmailContent.=Q::L('申诉人','Controller').':'.$oAppeal->appeal_realname.$sNlbr.$sNlbr;
		$sEmailContent.=Q::L('申诉回执编号','Controller').':'.$oAppeal->appeal_receiptnumber.$sNlbr.$sNlbr;
		$sEmailContent.='--'.Q::L('请牢记你的申诉编号，以便于随时查询申诉进度','Controller').$sNlbr;
		$sEmailContent.="<a href=\"{$sAppealscheduleUrl}\">{$sAppealscheduleUrl}</a>".$sNlbr.$sNlbr;
		$sEmailContent.=Q::L('接受申诉结果的Email','Controller').':'.$oAppeal->appeal_email.$sNlbr.$sNlbr;
		$sEmailContent.='-----------------------------------------------------'.$sNlbr;
		$sEmailContent.=date('Y-m-d H:i',CURRENT_TIMESTAMP);

		$oMailConnect->setEmailTo($oAppeal->appeal_email);
		$oMailConnect->setEmailSubject($sEmailSubject);
		$oMailConnect->setEmailMessage($sEmailContent);
		$oMailConnect->send();
		if($oMailConnect->isError()){
			$this->E($oMailConnect->getErrorMessage());
		}

		$this->assign('__WaitSecond__',5);
		$this->assign('__JumpUrl__','javascript:history.back(-1);');

		$this->S(Q::L('申诉回执编号已发送到您的邮箱','Controller').' '.$oAppeal->appeal_email);
	}

}
