<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   前台用户注册($$)*/

!defined('Q_PATH') && exit;

// 导入社会化登录组件
Q::import(WINDSFORCE_PATH.'/System/extension/socialization');

class Register_C_Controller extends InitController{

	public function index(){
		$nInajax=intval(Q::G('inajax','G'));
		$sReferer=trim(Q::G('referer','G'));
		
		if($GLOBALS['___login___']!==false){
			$this->assign('__JumpUrl__',__APP__);
			$this->E(Q::L('你已经登录','Controller'));
		}

		if($GLOBALS['_option_']['disallowed_register']){
			$this->E(Q::L('系统关闭了用户注册','Controller'));
		}

		Core_Extend::getSeo($this,array('title'=>Q::L('注册','Controller')));

		$this->assign('nDisplaySeccode',$GLOBALS['_option_']['seccode_register_status']);
		$this->assign('sReferer',$sReferer);

		if($nInajax==1){
			$this->display('public+ajaxregister');
		}else{
			if($GLOBALS['_option_']['only_login_viewsite']==1){
				$this->display('public+registerview');
			}else{
				$this->display('public+register');
			}
		}
	}

	public function check_user(){
		$sUserName=trim(strtolower(Q::G('user_name')));
		if(!$sUserName || Model::F_('user','user_name=?',$sUserName)->setColumns('user_name')->getOne()){
			exit('false');
		}else{
			exit('true');
		}
	}

	public function check_email(){
		$sUserEmail=trim(strtolower(Q::G('user_email')));
		if(!$sUserEmail || Model::F_('user','user_email=?',$sUserEmail)->setColumns('user_email')->getOne()){
			exit('false');
		}else{
			exit('true');
		}
	}

	public function register_user(){
		$sReferer=trim(Q::G('referer','P'));
		
		if($GLOBALS['___login___']!==false){
			$this->E(Q::L('你已经登录会员,不能重复注册','Controller'));
		}

		if($GLOBALS['_option_']['disallowed_register']){
			$this->E(Q::L('系统关闭了用户注册','Controller'));
		}

		if($GLOBALS['_option_']['seccode_register_status']==1){
			$this->_oParent->check_seccode(true);
		}

		$sPassword=trim(Q::G('user_password','P'));
		if(!$sPassword || $sPassword !=C::addslashes($sPassword)){
			$this->E(Q::L('密码空或包含非法字符','Controller'));
		}
		if(strpos($sPassword,"\n")!==false || strpos($sPassword,"\r")!==false || strpos($sPassword,"\t")!==false){
			$this->E(Q::L('密码包含不可接受字符','Controller'));
		}

		$sUsername=trim(Q::G('user_name','P'));
		$sDisallowedRegisterUser=trim($GLOBALS['_option_']['disallowed_register_user']);
		$sDisallowedRegisterUser='/^('.str_replace(array('\\*',"\r\n",' '),array('.*','|',''),preg_quote(($sDisallowedRegisterUser=trim($sDisallowedRegisterUser)),'/')).')$/i';
		if($sDisallowedRegisterUser && @preg_match($sDisallowedRegisterUser,$sUsername)){
			$this->E(Q::L('用户名包含被系统屏蔽的字符','Controller'));
		}

		$arrNameKeys=array("\\",'&',' ',"'",'"','/','*',',','<','>',"\r","\t","\n",'#','$','(',')','%','@','+','?',';','^');
		foreach($arrNameKeys as $sNameKeys){
			if(strpos($sUsername,$sNameKeys)!==false){
				$this->E(Q::L('此用户名包含不可接受字符或被管理员屏蔽,请选择其它用户名','Controller'));
			}
		}

		$sUseremail=trim(Q::G('user_email','P'));
		$sDisallowedRegisterEmail=trim($GLOBALS['_option_']['disallowed_register_email']);
		if($sDisallowedRegisterEmail){
			$arrDisallowedRegisterEmail=explode("\n",$sDisallowedRegisterEmail);
			$arrDisallowedRegisterEmail=Q::normalize($arrDisallowedRegisterEmail);
			if(in_array($sUseremail,$arrDisallowedRegisterEmail)){
				$this->E(Q::L('你注册的邮件地址%s已经被官方屏蔽','Controller',null,$sUseremail));
			}
		}

		$sAllowedRegisterEmail=trim($GLOBALS['_option_']['disallowed_register_email']);
		if($sAllowedRegisterEmail){
			$arrAllowedRegisterEmail=explode("\n",$sAllowedRegisterEmail);
			$arrAllowedRegisterEmail=Q::normalize($arrAllowedRegisterEmail);
			if(!in_array($sUseremail,$arrAllowedRegisterEmail)){
				$this->E(Q::L('你注册的邮件地址%s不在系统允许的邮件之列','Controller',null,$sUseremail));
			}
		}

		$oUser=new UserModel();
		if($GLOBALS['_option_']['audit_register']==0){
			$oUser->user_status=1;
		}
		$oUser->save();
		if($oUser->isError()){
			$this->E($oUser->getErrorMessage());
		}else{
			// 保存扩展信息
			$oUserprofile=new UserprofileModel();
			$oUserprofile->user_id=$oUser->user_id;
			$oUserprofile->save('create');
			if($oUserprofile->isError()){
				$this->E($oUserprofile->getErrorMessage());
			}

			$oUserCount=new UsercountModel();
			$oUserCount->user_id=$oUser->user_id;
			$oUserCount->save('create');
			if($oUserCount->isError()){
				$this->E($oUserCount->getErrorMessage());
			}

			// 将用户加入注册会员角色
			$oUserrole=new UserroleModel();
			$oUserrole->role_id=5;
			$oUserrole->user_id=$oUser['user_id'];
			$oUserrole->save();
			if($oUserrole->isError()){
				$this->E($oUserrole->getErrorMessage());
			}

			$this->cache_site_();

			// 注册推广
			$nCookiepromotion=Q::cookie('_promotion_');
			if(!empty($nCookiepromotion) && $oUser['user_id']!=$nCookiepromotion){
				Core_Extend::updateCreditByAction('promotion_register',$nCookiepromotion);
				Q::cookie('_promotion_',null,-1);
			}

			// 发送注册提醒
			$sNoticetemplate='<div class="notice_register"><div class="notice_content">'.str_replace(array('{static_time}','{static_user_name}'),array(date('Y-m-d H:i:s',CURRENT_TIMESTAMP),$oUser['user_name']),$GLOBALS['_option_']['register_welcome']).'</div></div>';
			$arrNoticedata=array();

			try{
				Core_Extend::addNotice($sNoticetemplate,$arrNoticedata,$oUser['user_id'],'system',0,0,'');
			}catch(Exception $e){
				$this->E($e->getMessage());
			}
			
			// 判断是否绑定社会化帐号
			if(Q::G('sociabind','P')==1){
				// 绑定社会化登录数据，以便于下次直接调用
				$oSociauser=Q::instance('SociauserModel');
				$oSociauser->processBind($oUser['user_id']);
				if($oSociauser->isError()){
					$this->E($oSociauser->getErrorMessage());
				}

				$arrData=$oUser->toArray();
				$arrData['jumpurl']=Q::U('home://public/sociabind_again');

				$arrSociauser=SociauserModel::F('user_id=?',$arrData['user_id'])->asArray()->getOne();
				Socia::setUser($arrSociauser);

				$this->A($arrData,Q::L('绑定成功','Controller'),1);
				exit();
			}
			
			if($sReferer==1 && !empty($_SERVER['HTTP_REFERER'])){
				$sJumpUrl=$_SERVER['HTTP_REFERER'];
			}elseif($sReferer){
				$sJumpUrl=$sReferer;
			}else{
				$sJumpUrl=Q::U('home://ucenter/index');
			}

			// 注册成功后登录
			$oUserModelLogin=new UserModel();
			$oUserModelLogin->checkLoginCommon($oUser['user_name'],$sPassword,false,'home');
			if($oUserModelLogin->isError()){
				$this->E($oUserModelLogin->getErrorMessage());
			}

			Core_Extend::updateCreditByAction('daylogin',$oUser['user_id']);
			
			$arrData=$oUser->toArray();
			$arrData['jumpurl']=$sJumpUrl;
			$this->A($arrData,Q::L('注册成功','Controller'),1);
		}
	}

	protected function cache_site_(){
		if(!Q::classExists('Cache_Extend')){
			require_once(Core_Extend::includeFile('function/Cache_Extend'));
		}
		Cache_Extend::updateCache('site');

		// 保存home今日数据
		Core_Extend::updateOption(array('todayusernum'=>$GLOBALS['_option_']['todayusernum']+1));

		// 保存home今日数据
		Core_Extend::updateOption(
			array(
				'todayusernum'=>$GLOBALS['_option_']['todayusernum']+1,
				'todaytotalnum'=>$GLOBALS['_option_']['todaytotalnum']+1,
			)
		);
	}

}
