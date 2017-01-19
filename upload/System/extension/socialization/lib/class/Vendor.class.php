<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   互联处理基类($$)*/

!defined('Q_PATH') && exit;

class Vendor{

	public $_arrConfig=array();
	public $_sAppid;
	public $_sSecid;
	public $_sVendor='';
	public $_sCallback;
	protected $_bIsError=false;
	protected $_sErrorMessage;

	public function __construct($sVendor){
		$this->_arrConfig=$GLOBALS['_cache_']['sociatype'][$sVendor];

		// get KEYID from config
		$this->_sAppid=$this->_arrConfig['sociatype_appid'];
		if(!$this->_sAppid){
			$this->setErrorMessage($sVendor.' KEYID NOT EXISTS');
			return false;
		}

		// get SECID from config
		$this->_sSecid=$this->_arrConfig['sociatype_appkey'];
		if(!$this->_sSecid){
			$this->setErrorMessage($sVendor.' SECID NOT EXISTS');
			return false;
		}

		if(!$this->_sAppid || !$this->_sSecid){
			$this->setErrorMessage($sVendor.' KEYID OR SECID NOT EXISTS');
			return false;
		}

		// set vendor
		$this->_sVendor=$sVendor;

		// get CallBack from config
		$this->_sCallback=$this->_arrConfig['sociatype_callback'];
	}

	public function getUser(){
		return $this->showUser();
	}

	protected function setIsError($bIsError=false){
		$bOldValue=$this->_bIsError;
		$this->_bIsError=$bIsError;
		return $bOldValue;
	}

	protected function setErrorMessage($sErrorMessage=''){
		$this->setIsError(true);
		$sOldValue=$this->_sErrorMessage;
		$this->_sErrorMessage=$sErrorMessage;
		return $sOldValue;
	}

	public function isError(){
		return $this->_bIsError;
	}

	public function getErrorMessage(){
		return $this->_sErrorMessage;
	}

}
