<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   用户申诉重发($$)*/

!defined('Q_PATH') && exit;

class Retrieve_C_Controller extends InitController{

	public function index(){
		$nAppealId=intval(Q::G('id','G'));

		if(!empty($nAppealId)){
			$oAppeal=AppealModel::F('appeal_id=?',$nAppealId)->getOne();

			$sEmail=$oAppeal->appeal_email;
			$oUser=UserModel::F('user_id=?',$oAppeal->user_id)->getOne();
			$sTemppassword=md5(C::randString(32));
			$oUser->user_temppassword=$sTemppassword;
			$oUser->setAutofill(false);
			$oUser->save('update');
			if($oUser->isError()){
				$this->E($oUser->getErrorMessage());
			}
			
			$sGetPasswordUrl=Core_Extend::windsforceOuter('app=home&c=getpassword&a=reset&email='.urlencode($sEmail).'&appeal=1'.'&hash='.urlencode(C::authcode($sTemppassword,false,null,$GLOBALS['_option_']['appeal_expired'])));

			$oMailModel=Q::instance('MailModel');
			$oMailConnect=$oMailModel->getMailConnect();

			$sEmailSubject=$GLOBALS['_option_']['site_name'].Q::L('会员申诉密码重置','Controller');
			$sNlbr=$oMailConnect->getIsHtml()===true?'<br/>':"\r\n";
			$sEmailContent='';
			$sEmailContent.=Q::L('重置密码链接','Controller').':'.$sNlbr;
			$sEmailContent.="<a href=\"{$sGetPasswordUrl}\">{$sGetPasswordUrl}</a>".$sNlbr.$sNlbr;
			$sEmailContent.="-----------------------------------------------------".$sNlbr;
			$sEmailContent.=Q::L('这是系统用于重置密码的邮件，请勿回复','Controller').$sNlbr;
			$sEmailContent.=Q::L('链接过期时间','Controller').$GLOBALS['_option_']['appeal_expired'].
				Q::L('秒','Controller').$sNlbr;

			$oMailConnect->setEmailTo($sEmail);
			$oMailConnect->setEmailSubject($sEmailSubject);
			$oMailConnect->setEmailMessage($sEmailContent);
			$oMailConnect->send();
			if($oMailConnect->isError()){
				$this->E($oMailConnect->getErrorMessage());
			}

			$this->S(Q::L('发送成功,请注意查收','Controller'));
		}else{
			$this->E(Q::L('读取数据失败','Controller'));
		}
	}

}
