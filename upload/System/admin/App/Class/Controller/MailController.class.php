<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   邮件管理控制器($$)*/

!defined('Q_PATH') && exit;

class MailController extends AController{

	public function bInsert_(){
		// 处理checkbox
		if(!isset($_POST['mail_level'])){
			$_POST['mail_level']=1;
		}

		if(!isset($_POST['mail_htmlon'])){
			$_POST['mail_htmlon']=1;
		}
	}

	public function filter_(&$arrMap){
		$arrMap['A.mail_subject']=array('like',"%".Q::G('mail_subject')."%");
		$arrMap['A.mail_tomail']=array('like',"%".Q::G('mail_tomail')."%");

		// 添加时间
		$this->getTime_('A.create_dateline',$arrMap);
	}

	public function bUpdate_(){
		$this->bInsert_();
	}

	public function send(){
		$nMailId=intval(Q::G('id','G'));
		if(empty($nMailId)){
			$this->E(Q::L('操作项不存在','Controller'));
		}

		$oMail=MailModel::F('mail_id=?',$nMailId)->query();
		if(empty($oMail['mail_id'])){
			$this->E(Q::L('数据库中并不存在该项，或许它已经被删除','Controller'));
		}

		// 发送邮件
		$oMailObject=Q::instance('MailModel');
		$oMailConnect=$oMailObject->getMailConnect();
		$oMailObject->sendAEmail($oMailConnect,$oMail['mail_tomail'],$oMail['mail_subject'],($oMail['mail_htmlon']==0?strip_tags($oMail['mail_message']):$oMail['mail_message']),'admin',false);
		if($oMailObject->isError()){
			$this->E($oMailObject->getErrorMessage());
		}

		$this->S(Q::L('邮件发送成功','Controller'));
	}

}
