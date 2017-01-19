<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   用户申诉第四步($$)*/

!defined('Q_PATH') && exit;

class Step4_C_Controller extends InitController{

	public function index(){
		if($GLOBALS['___login___']!==false){
			$this->U('home://ucenter/index');
		}

		$nEmaillink=intval(Q::G('emaillink'));
		if($nEmaillink!=1){
			$this->_oParent->check_seccode(true);
		}
		
		$sRealname=trim(Q::G('real_name'));
		$sAddress=trim(Q::G('address'));
		$sIdnumber=trim(Q::G('id_number'));
		$sAppealemail=trim(Q::G('appeal_email'));
		$sUserid=trim(Q::G('user_id'));
		$sHashcode=trim(Q::G('hashcode','P'));
		$sOldHashcode=trim(Q::G('old_hashcode','P'));

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

		if($nEmaillink!=1){
			if(empty($sHashcode)){
				$this->E(Q::L('申诉验证码不能为空','Controller'));
			}

			$sOldHashcode=C::authcode($sOldHashcode);
			if(empty($sOldHashcode)){
				$this->E(Q::L('申诉验证码已过期','Controller'));
			}

			if($sOldHashcode!=$sHashcode){
				$this->E(Q::L('申诉验证码错误','Controller'));
			}
		}

		$sReceiptnumber=C::randString(32);

		// 将申诉信息保存到数据库
		$oAppeal=new AppealModel();
		$oAppeal->user_id=intval($sUserid);
		$oAppeal->appeal_realname=$sRealname;
		$oAppeal->appeal_address=$sAddress;
		$oAppeal->appeal_idnumber=$sIdnumber;
		$oAppeal->appeal_email=$sAppealemail;
		$oAppeal->appeal_receiptnumber=$sReceiptnumber;
		$oAppeal->save();
		if($oAppeal->isError()){
			$this->E($oAppeal->getErrorMessage());
		}
	
		$sUserid=C::authcode($oAppeal['user_id'],false,null,$GLOBALS['_option_']['appeal_expired']);
		
		Core_Extend::getSeo($this,array('title'=>Q::L('获取申诉回执编号','Controller')));
		
		$this->assign('sUserid',$sUserid);
		$this->assign('oAppeal',$oAppeal);
		$this->display('userappeal+step4');
	}

}
