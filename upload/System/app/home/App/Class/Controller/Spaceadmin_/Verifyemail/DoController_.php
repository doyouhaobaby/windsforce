<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   发送验证信息($$)*/

!defined('Q_PATH') && exit;

class Do_C_Controller extends InitController{

	public function index(){
		$oUser=Model::F_('user','user_id=?',$GLOBALS['___login___']['user_id'])->getOne();
		if($oUser['user_isverify']==1){
			$this->E(Q::L('Email已经验证过了，无需重复验证','Controller'));
		}
		
		// 部分验证
		if(empty($oUser['user_email'])){
			$this->E(Q::L('Email地址不能为空','Controller'));
		}

		Check::RUN();
		if(!Check::C($oUser['user_email'],'email')){
			$this->E(Q::L('Email格式不正确','Controller'));
		}

		if($oUser['user_status']==0){
			$this->E(Q::L('该账户已经被禁止','Controller'));
		}

		// 随机码
		$sUserverifycode=md5(C::randString(32));
		$sUserverifyUrl=Core_Extend::windsforceOuter('app=home&c=spaceadmin&a=checkrevifyemail&email='.urlencode($oUser['user_email']).'&hash='.urlencode(C::authcode($sUserverifycode,false,null,$GLOBALS['_option_']['verifyemail_expired'])));

		$oMailModel=Q::instance('MailModel');
		$oMailConnect=$oMailModel->getMailConnect();

		$sEmailSubject=$GLOBALS['_option_']['site_name'].' '.Q::L('Email验证信息','Controller');
		$sNlbr=$oMailConnect->getIsHtml()===true?'<br/>':"\r\n";
		$sEmailContent=Q::L('你需要验证的Email','Controller').':'.$sNlbr;
		$sEmailContent.='Email:'.$oUser['user_email'].$sNlbr;
		$sEmailContent.=Q::L('验证Email链接','Controller').':'.$sNlbr;
		$sEmailContent.="<a href=\"{$sUserverifyUrl}\">{$sUserverifyUrl}</a>".$sNlbr.$sNlbr;
		$sEmailContent.="-----------------------------------------------------".$sNlbr;
		$sEmailContent.=Q::L('这是系统用于验证Email的邮件，请勿回复','Controller').$sNlbr;
		$sEmailContent.=Q::L('链接过期时间','Controller').':'.$GLOBALS['_option_']['verifyemail_expired'].Q::L('秒','__COMMON_LANG__@Common').$sNlbr;

		$oMailConnect->setEmailTo($oUser['user_email']);
		$oMailConnect->setEmailSubject($sEmailSubject);
		$oMailConnect->setEmailMessage($sEmailContent);
		$oMailConnect->send();
		if($oMailConnect->isError()){
			$this->E($oMailConnect->getErrorMessage());
		}

		// 保存随机码
		$oUser->user_verifycode=$sUserverifycode;
		$oUser->setAutofill(false);
		$oUser->save('update');
		if($oUser->isError()){
			$this->E($oUser->getErrorMessage());
		}

		$this->S(Q::L('邮件已发送到你指定的邮箱','Controller'));
	}

}
