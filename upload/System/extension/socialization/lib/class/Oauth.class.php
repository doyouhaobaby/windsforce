<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   互联接口基类($$)*/

!defined('Q_PATH') && exit;

class Oauth{

	protected $_bIsError=false;
	protected $_sErrorMessage;

	public function __construct(){}

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

	public function file_get_contents($sUrl){
		$hCh=curl_init();
		curl_setopt($hCh,CURLOPT_RETURNTRANSFER,TRUE);
		curl_setopt($hCh,CURLOPT_POST,FALSE); 
		curl_setopt($hCh,CURLOPT_URL,$sUrl);
		$sResult=curl_exec($hCh);

		return $sResult;
	}

}
