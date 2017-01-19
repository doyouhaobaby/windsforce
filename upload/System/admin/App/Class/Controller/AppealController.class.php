<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   用户申诉管理控制器($$)*/

!defined('Q_PATH') && exit;

class AppealController extends AController{

	public function filter_(&$arrMap){
		$arrMap['A.appeal_email']=array('like',"%".Q::G('appeal_email')."%");
		$arrMap['A.appeal_realname']=array('like',"%".Q::G('appeal_realname')."%");

		$nType=Q::G('appeal_progress');
		if($nType!==null && $nType!=''){
			$arrMap['A.appeal_progress']=$nType;
			$this->assign('nType',$nType);
		}

		// 添加时间
		$this->getTime_('A.create_dateline',$arrMap);
	}

	public function show(){
		$nAppealId=intval(Q::G('id','G'));

		if(!empty($nAppealId)){
			$oAppeal=AppealModel::F('appeal_id=?',$nAppealId)->getOne();
			$oUser=UserModel::F('user_id=?',$oAppeal->user_id)->getOne();
			if($oAppeal->appeal_progress==0){
				$oAppeal->appeal_progress=1;
				$oAppeal->save('update');
				if($oAppeal->isError()){
					$this->E(Q::L('用户信息更新错','Controller'));
				}
			}

			$this->assign('oAppeal',$oAppeal);
			$this->assign('oUser',$oUser);
			$this->display();
		}else{
			$this->E(Q::L('无法获取用户申诉信息','Controller'));
		}
	}

	public function pass(){
		$nAppealId=intval(Q::G('id','G'));

		if(!empty($nAppealId)){
			$oAppeal=AppealModel::F('appeal_id=?',$nAppealId)->getOne();
			$oAppeal->appeal_progress=2;
			$oAppeal->appeal_reason='';
			$oAppeal->save('update');
			if(!$oAppeal->isError()){
				$sEmail=$oAppeal->appeal_email;
				$oUser=UserModel::F('user_id=?',$oAppeal->user_id)->getOne();
				$sTemppassword=md5(C::randString(32));
				$oUser->user_temppassword=$sTemppassword;
				$oUser->save('update');
				if($oUser->isError()){
					$this->E($oUser->getErrorMessage());
				}
				
				$sGetPasswordUrl=Core_Extend::windsforceOuter('app=home&c=getpassword&a=reset&email='.urlencode($sEmail).'&appeal=1'.'&hash='.urlencode(C::authcode($sTemppassword,false,null,$GLOBALS['_option_']['appeal_expired'])));

				$oMailModel=Q::instance('MailModel');
				$oMailConnect=$oMailModel->getMailConnect();

				$sEmailSubject=Q::L('会员申诉密码重置','Controller');
				$sNlbr=$oMailConnect->getIsHtml()===true?'<br/>':"\r\n";
				$sEmailContent='';
				$sEmailContent.=Q::L('尊敬的','Controller').$oUser->user_name.Q::L('用户','Controller').':'.$sNlbr.$sNlbr;
				$sEmailContent.=Q::L('您的申诉已通过','Controller').$sNlbr.$sNlbr;
				$sEmailContent.=Q::L('您可以点击下面链接重置密码','Controller').':'.$sNlbr;
				$sEmailContent.="<a href=\"{$sGetPasswordUrl}\">{$sGetPasswordUrl}</a>".$sNlbr.$sNlbr;
				$sEmailContent.="-----------------------------------------------------".$sNlbr;
				$sEmailContent.=Q::L('这是系统用于重置密码的邮件，请勿回复','Controller').$sNlbr;
				$sEmailContent.=Q::L('链接过期时间','Controller').$GLOBALS['_option_']['getpassword_expired'].
					Q::L('秒','Controller').$sNlbr;

				$oMailConnect->setEmailTo($sEmail);
				$oMailConnect->setEmailSubject($sEmailSubject);
				$oMailConnect->setEmailMessage($sEmailContent);
				$oMailConnect->send();
				if($oMailConnect->isError()){
					$this->E($oMailConnect->getErrorMessage());
				}

				$this->S(Q::L('审核通过','Controller'));
			}else{
				$this->E(Q::L('审核失败','Controller'));
			}
		}else{
			$this->E(Q::L('无法获取用户申诉信息','Controller'));
		}
	}

	public function reject(){
		$nAppealId=intval(Q::G('id','G'));
		$sAppealReason=trim(Q::G('appeal_reason','P'));

		if(!empty($nAppealId)){
			$oAppeal=AppealModel::F('appeal_id=?',$nAppealId)->getOne();
			$oAppeal->appeal_progress=3;
			$oAppeal->appeal_reason=$sAppealReason;
			$oAppeal->save('update');
			if(!$oAppeal->isError()){
				$sEmail=$oAppeal->appeal_email;
				$sAppealUrl=Core_Extend::windsforceOuter('app=home&c=userappeal&a=index');

				$oMailModel=Q::instance('MailModel');
				$oMailConnect=$oMailModel->getMailConnect();

				$sEmailSubject=Q::L('会员申诉驳回','Controller');
				$sNlbr=$oMailConnect->getIsHtml()===true?'<br/>':"\r\n";
				$sEmailContent='';
				$sEmailContent.=Q::L('驳回理由','Controller').':'.$sAppealReason.$sNlbr.$sNlbr;
				$sEmailContent.=Q::L('申诉页面链接','Controller').':'.$sNlbr;
				$sEmailContent.="<a href=\"{$sAppealUrl}\">{$sAppealUrl}</a>".$sNlbr.$sNlbr;
				$sEmailContent.="-----------------------------------------------------".$sNlbr;
				$sEmailContent.=Q::L('这是系统用于申诉驳回的邮件，请勿回复','Controller').$sNlbr;

				$oMailConnect->setEmailTo($sEmail);
				$oMailConnect->setEmailSubject($sEmailSubject);
				$oMailConnect->setEmailMessage($sEmailContent);
				$oMailConnect->send();
				if($oMailConnect->isError()){
					$this->E($oMailConnect->getErrorMessage());
				}

				$this->assign('__JumpUrl__',Q::U('Appeal/index'));

				$this->S(Q::L('审核驳回','Controller'));
			}else{
				$this->E(Q::L('驳回失败','Controller'));
			}
		}else{
			$this->E(Q::L('无法获取用户申诉信息','Controller'));
		}
	}

}
