<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   用户登录显示($$)*/

!defined('Q_PATH') && exit;

class Login_C_Controller extends InitController{

	public function index(){
		$sReferer=trim(Q::G('referer'));
		$nRbac=intval(Q::G('rbac','G'));
		$nLoginview=intval(Q::G('loginview','G'));
		if($GLOBALS['___login___']!==false){
			$this->_oParent->wap_mes(Q::L('你已经登录','Controller'),Q::U('wap://public/index'),0);
		}

		Core_Extend::getSeo($this,array('title'=>Q::L('用户登录','Controller')));

		$this->assign('sReferer',$sReferer);
		$this->assign('nRbac',$nRbac);
		$this->assign('nLoginview',$nLoginview);
		$this->display('public+login');
	}

	public function check(){
		$sUserName=Q::G('user_name','P');
		$sPassword=Q::G('user_password','P');

		if(empty($sUserName)){
			$this->_oParent->wap_mes(Q::L('帐号或者E-mail不能为空','Controller'),Q::U('wap://public/login'),0);
		}elseif(empty($sPassword)){
			$this->_oParent->wap_mes(Q::L('密码不能为空','Controller'),Q::U('wap://public/login'),0);
		}

		Check::RUN();
		if(Check::C($sUserName,'email')){
			$bEmail=true;
			unset($_POST['user_name']);
		}else{
			$bEmail=false;
		}

		$oUserModel=Q::instance('UserModel');
		$oUserModel->checkLoginCommon($sUserName,$sPassword,$bEmail,'wap');
		if($oUserModel->isError()){
			$this->_oParent->wap_mes($oUserModel->getErrorMessage(),Q::U('wap://public/login'),0);
		}else{
			if(Q::G('windsforce_referer')){
				$sUrl=Q::G('windsforce_referer');
			}else{
				$sUrl=Q::U('wap://ucenter/index');
			}

			$oLoginUser=UserModel::F('user_name=?',$sUserName)->getOne();

			Core_Extend::updateCreditByAction('daylogin',$oLoginUser['user_id']);

			$this->_oParent->wap_mes(Q::L('Hello %s,你成功登录','Controller',null,$sUserName),$sUrl);
		}
	}

	public function out(){
		$nReferer=intval(Q::G('referer','G'));
		if($GLOBALS['___login___']!==FALSE){
			if(!Q::classExists('Auth')){
				require_once(Core_Extend::includeFile('class/Auth'));
			}
			
			Auth::loginOut();

			if($nReferer==1 && !empty($_SERVER['HTTP_REFERER'])){
				$sJumpUrl=$_SERVER['HTTP_REFERER'];
			}else{
				$sJumpUrl=Q::U('wap://public/login');
			}

			$this->_oParent->wap_mes(Q::L('登出成功','Controller'),$sJumpUrl);
		}else{
			$this->_oParent->wap_mes(Q::L('已经登出','Controller'),'',0);
		}
	}

}
