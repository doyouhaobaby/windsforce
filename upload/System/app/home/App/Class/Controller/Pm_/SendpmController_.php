<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   发送短消息处理逻辑($$)*/

!defined('Q_PATH') && exit;

class Sendpm_C_Controller extends InitController{

	public function index(){
		$this->_oParent->check_pm();
		
		$arrOptionData=$GLOBALS['_option_'];
		if($arrOptionData['pmsend_seccode']==1){
			$this->_oParent->check_seccode(true);
		}
		
		$sMessageto=trim(Q::G('messageto'));
		$sPmMessage=trim(Q::G('pm_message'));
		$sPmSubject=trim(Q::G('pm_subject'));

		if(empty($sMessageto)){
			$this->E(Q::L('收件用户不能为空','Controller'));
		}
		
		$arrUsers=Core_Extend::segmentUsername($sMessageto);
		
		$oLastPmModel=null;
		foreach($arrUsers as $sUser){
			if(empty($sUser)){
				continue;
			}
				
			if($sUser==$GLOBALS['___login___']['user_name']){
				$this->E(Q::L('收件用户中不能有自己','Controller'));
			}
			
			if(!preg_match("/[^\d-.,]/",$sUser)){
				$arrTryUser=Model::F_('user','user_id=? AND user_status=1',$sUser)->setColumns('user_id,user_name')->getOne();
			}else{
				$arrTryUser=Model::F_('user','user_name=? AND user_status=1',$sUser)->setColumns('user_id,user_name')->getOne();
			}

			if(empty($arrTryUser['user_id'])){
				$this->E(Q::L('用户 %s 不存在或者尚未审核通过','Controller',null,$arrTryUser['user_name']));
			}

			$oPmModel=Q::instance('PmModel');
			$oLastPmModel=$oPmModel->sendAPm($sUser,$GLOBALS['___login___']['user_id'],$GLOBALS['___login___']['user_name'],$sPmSubject,'home');
			if($oPmModel->isError()){
				$this->E($oPmModel->getErrorMessage());
			}
		}

		// 更新积分
		Core_Extend::updateCreditByAction('sendpm',$GLOBALS['___login___']['user_id']);
		
		// 成功消息
		if(Q::G('type')=='back'){
			$arrData=$oLastPmModel->toArray();
			$arrData['jumpurl']=($GLOBALS['_commonConfig_']['URL_MODEL'] && $GLOBALS['_commonConfig_']['URL_MODEL']!=3?'?':'&').
				'extra=new'.$arrData['pm_id'].'#pm-'.$arrData['pm_id'];

			$this->A($arrData,Q::L('发送短消息成功','Controller'),1);
		}else{
			$arrData=$oLastPmModel->toArray();
			$arrData['jumpurl']=Q::U('home://pm/show?id='.$arrData['pm_id'].'&muid='.$arrData['pm_msgfromid']);
			
			$this->A($arrData,Q::L('发送短消息成功','Controller'),1);
		}
	}

}
