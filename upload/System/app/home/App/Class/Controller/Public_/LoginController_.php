<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   前台登陆($$)*/

!defined('Q_PATH') && exit;

// 导入社会化登录组件
Q::import(WINDSFORCE_PATH.'/System/extension/socialization');

class Login_C_Controller extends InitController{

	public function index(){
		$nInajax=intval(Q::G('inajax','G'));
		$sReferer=trim(Q::G('referer'));
		$nRbac=intval(Q::G('rbac','G'));
		$nLoginview=intval(Q::G('loginview','G'));
		
		if($GLOBALS['___login___']!==false){
			$this->assign('__JumpUrl__',__APP__);
			$this->E(Q::L('你已经登录','Controller'));
		}

		Core_Extend::loadCache('sociatype');

		$this->assign('nDisplaySeccode',$GLOBALS['_option_']['seccode_login_status']);
		$this->assign('nRememberTime',$GLOBALS['_option_']['remember_time']);
		$this->assign('arrBindeds',$GLOBALS['_cache_']['sociatype']);
		$this->assign('sReferer',$sReferer);
		$this->assign('nRbac',$nRbac);
		$this->assign('nLoginview',$nLoginview);

		Core_Extend::getSeo($this,array('title'=>Q::L('登录','Controller')));

		if($nInajax==1){
			$this->display('public+ajaxlogin');
		}else{
			if($GLOBALS['_option_']['only_login_viewsite']==1){
				$this->display('public+loginview');
			}else{
				$this->display('public+login');
			}
		}
	}

	public function socia(){
		$sVendor=trim(Q::G('vendor','G'));
		$oSocia=Q::instance('Socia',$sVendor);
		$oSocia->login();
		if($oSocia->isError()){
			$this->E($oSocia->getErrorMessage());
		}
	}

	public function callback(){
		$sVendor=trim(Q::G('vendor','G'));
		$oSocia=Q::instance('Socia',$sVendor);
		$arrUser=$oSocia->callback();
		$oSocia->bind();
		if($oSocia->isError()){
			$this->E($oSocia->getErrorMessage());
		}
	}

	public function bind(){
		$arrUser=Socia::getUser();
		if(empty($arrUser)){
			$this->assign('__JumpUrl__',Q::U('home://public/login'));
			$this->E(Q::L('你尚未登录社会化帐号','Controller'));
		}

		Core_Extend::getSeo($this,array('title'=>Q::L('社会化绑定','Controller')));

		$this->assign('arrUser',$arrUser);
		$this->assign('sRandPassword',C::randString(10));
		$this->display('public+sociabind');
	}

	public function unbind(){
		$sVendor=trim(Q::G('vendor','G'));

		$oSociauserMeta=SociauserModel::M();
		$oSociauserMeta->deleteWhere(array('sociauser_vendor'=>$sVendor,'user_id'=>$GLOBALS['___login___']['user_id']));
		if($oSociauserMeta->isError()){
			$this->E($oSociauserMeta->getErrorMessage());
		}

		$this->assign('__JumpUrl__',Q::U('home://ucenter/index'));
		$this->S(Q::L('帐号解除绑定成功','Controller'));
	}

	public function bind_again(){
		$arrUser=Socia::getUser();
		if(empty($arrUser)){
			$this->E(Q::L('你尚未登录社会化帐号','Controller'));
		}

		$oSocia=Q::instance('Socia',$arrUser['sociauser_vendor']);
		$oSocia->bind();
	}

	public function check_login(){
		if($GLOBALS['_option_']['seccode_login_status']==1){
			$this->_oParent->check_seccode(true);
		}

		$sUserName=Q::G('user_name','P');
		$sPassword=Q::G('user_password','P');

		if(empty($sUserName)){
			$this->E(Q::L('帐号或者E-mail不能为空','Controller'));
		}elseif(empty($sPassword)){
			$this->E(Q::L('密码不能为空','Controller'));
		}

		Check::RUN();
		if(Check::C($sUserName,'email')){
			$bEmail=true;
			unset($_POST['user_name']);
		}else{
			$bEmail=false;
		}

		$oUserModel=Q::instance('UserModel');
		$oUserModel->checkLoginCommon($sUserName,$sPassword,$bEmail,'home',Socia::getUser()?$GLOBALS['socia_login_time']:null);
		if($oUserModel->isError()){
			$this->E($oUserModel->getErrorMessage());
		}else{
			if(Q::G('windsforce_referer')){
				$sUrl=Q::G('windsforce_referer');
			}else{
				$sUrl=Q::U('home://ucenter/index');
			}

			$oLoginUser=UserModel::F('user_name=?',$sUserName)->getOne();

			Core_Extend::updateCreditByAction('daylogin',$oLoginUser['user_id']);

			// 如果第三方网站已登录，则进行绑定
			if(Socia::getUser()){
				// 绑定社会化登录数据，以便于下次直接调用
				$oSociauser=Q::instance('SociauserModel');
				$oSociauser->processBind($oLoginUser['user_id']);
				if($oSociauser->isError()){
					$this->E($oSociauser->getErrorMessage());
				}
			}

			$this->A(array('url'=>$sUrl),Q::L('Hello %s,你成功登录','Controller',null,$sUserName),1);
		}
	}

	public function logout(){
		$nReferer=intval(Q::G('referer','G'));

		if(!empty($GLOBALS['___login___'])){
			if(!Q::classExists('Auth')){
				require_once(Core_Extend::includeFile('class/Auth'));
			}

			Auth::loginOut();
			Socia::clearCookie();

			if($nReferer==1 && !empty($_SERVER['HTTP_REFERER'])){
				$sJumpUrl=$_SERVER['HTTP_REFERER'];
			}else{
				$sJumpUrl=Q::U('home://public/login');
			}
	
			$this->assign("__JumpUrl__",$sJumpUrl);
			$this->S(Q::L('登出成功','Controller'));
		}else{
			$this->E(Q::L('已经登出','Controller'));
		}
	}

	public function clear(){
		if(!Q::classExists('Auth')){
			require_once(Core_Extend::includeFile('class/Auth'));
		}

		Auth::loginOut();
		Socia::clearCookie();
		$this->S(Q::L('清理登录痕迹成功','Controller'));
	}

}
