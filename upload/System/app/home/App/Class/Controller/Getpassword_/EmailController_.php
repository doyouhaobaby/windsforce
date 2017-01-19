<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   通过邮件发送密码重置链接($$)*/

!defined('Q_PATH') && exit;

class Email_C_Controller extends InitController{

	public function index(){
		if($GLOBALS['___login___']!==false){
			$this->U('home://spaceadmin/password');
		}

		$this->_oParent->check_seccode(true);

		$sEmail=trim(Q::G('user_email','P'));
		if(empty($sEmail)){
			$this->E(Q::L('Email地址不能为空','Controller'));
		}

		Check::RUN();
		if(!Check::C($sEmail,'email')){
			$this->E(Q::L('Email格式不正确','Controller'));
		}

		$oUser=UserModel::F('user_email=?',$sEmail)->getOne();
		if(empty($oUser->user_id)){
			$this->E(Q::L('Email账号不存在','Controller'));
		}
		if($oUser->user_status==0){
			$this->E(Q::L('该账户已经被禁止','Controller'));
		}

		$sTemppassword=md5(C::randString(32));
		$oUser->user_temppassword=$sTemppassword;
		$oUser->setAutofill(false);
		$oUser->save('update');
		if($oUser->isError()){
			$this->E($oUser->getErrorMessage());
		}
		
		$sGetPasswordUrl=Core_Extend::windsforceOuter('app=home&c=getpassword&a=reset&email='.urlencode($sEmail).'&hash='.urlencode(C::authcode($sTemppassword,false,null,$GLOBALS['_option_']['getpassword_expired'])));

		$oMailModel=Q::instance('MailModel');
		$oMailConnect=$oMailModel->getMailConnect();

		$sEmailSubject=$GLOBALS['_option_']['site_name'].Q::L('会员密码找回','Controller');
		$sNlbr=$oMailConnect->getIsHtml()===true?'<br/>':"\r\n";
		$sEmailContent=Q::L('你的登录信息','Controller').':'.$sNlbr;
		$sEmailContent.='Email:'.$sEmail.$sNlbr;
		$sEmailContent.=Q::L('重置密码链接','Controller').':'.$sNlbr;
		$sEmailContent.="<a href=\"{$sGetPasswordUrl}\">{$sGetPasswordUrl}</a>".$sNlbr.$sNlbr;
		$sEmailContent.="-----------------------------------------------------".$sNlbr;
		$sEmailContent.=Q::L('这是系统用于找回密码的邮件，请勿回复','Controller').$sNlbr;
		$sEmailContent.=Q::L('链接过期时间','Controller').':'.$GLOBALS['_option_']['getpassword_expired'].Q::L('秒','__COMMON_LANG__@Common').$sNlbr;

		$oMailConnect->setEmailTo($sEmail);
		$oMailConnect->setEmailSubject($sEmailSubject);
		$oMailConnect->setEmailMessage($sEmailContent);
		$oMailConnect->send();
		if($oMailConnect->isError()){
			$this->E($oMailConnect->getErrorMessage());
		}

		$this->S(Q::L('邮件已发送到你指定的邮箱','Controller'));
	}

}
