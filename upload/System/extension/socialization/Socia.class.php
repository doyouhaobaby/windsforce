<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   互联入口类($$)*/

!defined('Q_PATH') && exit;

/** 预处理登陆时间 */
$GLOBALS['socia_login_time']=Q::cookie('SOCIA_LOGIN_TIME')?intval(Q::cookie('SOCIA_LOGIN_TIME')):null;

class Socia{
	
	private $_oVendor;
	private $_oLocal;
	protected $_bIsError=false;
	protected $_sErrorMessage;

	public function __construct($sVendor=''){
		Core_Extend::loadCache('sociatype');
		$this->setVendor($sVendor);
		$this->setLocal();
	}

	public function setVendor($sVendor=''){
		if($sVendor){
			$sClass='Vendor'.ucfirst(strtolower($sVendor));
			$this->_oVendor=new $sClass();
		}
	}

	public function setLocal($sLocal='SociauserlocalController'){
		$this->_oLocal=new $sLocal();
	}
	
	static public function setUser($arrUser){
		Q::cookie('SOCIAUSER',serialize($arrUser));
	}

	static public function getUser(){
		$sUser=Q::cookie('SOCIAUSER');
		return !empty($sUser)?unserialize($sUser):FALSE;
	}

	public function login(){
		return $this->gotoLoginPage();
	}

	public function callback(){
		$arrUser=$this->_oVendor->getUser();
	
		if($this->_oVendor->isError()){
			$this->_sErrorMessage=$this->_oVendor->getErrorMessage();
			return false;
		}
		
		if($arrUser){
			self::setUser($arrUser);
		}

		return $arrUser;
	}

	public function gotoLoginPage(){
		$this->_oVendor->gotoLoginPage();

		if($this->_oVendor->isError()){
			$this->_sErrorMessage=$this->_oVendor->getErrorMessage();
			return false;
		}
	}

	public function bind(){
		if(!self::getUser()){
			if(!$this->isError()){
				$this->_sErrorMessage='Can not find userinfo!';
			}
			return false;
		}

		$this->_oLocal->bind();
	}

	static public function clearCookie(){
		Q::cookie('_socia_state_',NULL,-1);
		Q::cookie('SOCIAUSER',NULL,-1);
		Q::cookie("_socia_access_token_",NULL,-1);
		Q::cookie('_socia_openid_',NULL,-1);
	}

	public function isError(){
		return !empty($this->_sErrorMessage);
	}

	public function getErrorMessage(){
		return $this->_sErrorMessage;
	}

}
