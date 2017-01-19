<?php
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   Coll实现了一个类型安全的对象集合（Learn QP!）($$)*/

!defined('Q_PATH') && exit;

class Coll implements Iterator,ArrayAccess,Countable{

	protected $_sType;
	protected $_arrColl=array();
	protected $_bIsValid=false;

	public function __construct($sType){
		$this->_sType=$sType;
	}

	public static function createFromArray(array $arrObjects,$sType,$bKeepKeys=false){
		$oColl=new self($sType);

		if($bKeepKeys){
			foreach($arrObjects as $offset=>$oObject){$arrColl[$offset]=$oObject;}
		}else{
			foreach($arrObjects as $oObject){$arrColl[]=$oObject;}
		}

		return $arrColl;
	}

	public function values($sPropName){
		$arrReturn=array();

		foreach(array_keys($this->_arrColl)as $offset){
			if(isset($this->_arrColl[$offset]->{$sPropName})){
				$arrReturn[]=$this->_arrColl[$offset]->{$sPropName};
			}
		}

		return $arrReturn;
	}

	public function offsetExists($Offset){
		return isset($this->_arrColl[$Offset]);
	}

	public function offsetGet($Offset){
		if(isset($this->_arrColl[$Offset])){
			return $this->_arrColl[$Offset];
		}

		Q::E(sprintf('Invalid key name %s.',$Offset));
	}

	public function offsetSet($Offset,$Value){
		if(is_null($Offset)){
			$Offset=count($this->_arrColl);
		}

		$this->checkType_($Value);
		while(isset($this->_arrColl[$Offset])){
			$Offset++;
		}

		$this->_arrColl[$Offset]=$Value;
	}

	public function offsetUnset($Offset){
		unset($this->_arrColl[$Offset]);
	}

	public function current(){
		return current($this->_arrColl);
	}

	public function key(){
		return key($this->_arrColl);
	}

	public function next(){
		$this->_bIsValid=(false!==next($this->_arrColl));
	}

	public function rewind(){
		$this->_bIsValid=(false!==reset($this->_arrColl));
	}

	public function valid(){
		return $this->_bIsValid;
	}

	public function count(){
		return count($this->_arrColl);
	}

	public function isEmpty(){
		return empty($this->_arrColl);
	}

	public function first(){
		if(count($this->_arrColl)){
			return reset($this->_arrColl);
		}

		Q::E(Q::L('%s 集合中没有任何对象。','__QEEPHP__@Q',null,$this->_sType));
	}

	public function last(){
		if(count($this->_arrColl)){
			$arrKeys=array_keys($this->_arrColl);
			$key=array_pop($arrKeys);
			return $this->_arrColl[$key];
		}

		Q::E(Q::L('%s 集合中没有任何对象。','__QEEPHP__@Q',null,$this->_sType));
	}

	public function append($Data){
		foreach($Data as $oItem){
			$this->offsetSet(null,$oItem);
		}

		return $this;
	}

	public function search($sPropName,$Needle,$bStrict=false){
		foreach($this->_arrColl as $oItem){
			if($bStrict){
				if($oItem->{$sPropName}===$Needle){
					return $oItem;
				}
			}else{
				if($oItem->{$sPropName}==$Needle){
					return $oItem;
				}
			}
		}

		return null;
	}

	public function toHashMap($sKeyName,$sValueName=null){
		$arrRet=array();

		if($sValueName){
			foreach($this->_arrColl as $oObj){
				$arrRet[$oObj[$sKeyName]]=$oObj[$sValueName];
			}
		}else{
			foreach($this->_arrColl as $oObj){
				$arrRet[$oObj[$sKeyName]]=$oObj;
			}
		}

		return $arrRet;
	}

	public function __call($sMethod,$arrArgs){
		$bNotImplement=false;

		$sMethod=strtolower($sMethod);
		if(method_exists($this->_sType,'collCallback_')){
			$arrMap=call_user_func(array($this->_sType,'collCallback_'));
			$arrMap=array_change_key_case($arrMap,CASE_LOWER);
			if(isset($arrMap[$sMethod])){
				array_unshift($arrArgs,$this->_arrColl);
				return call_user_func_array(array($this->_sType,$arrMap[$sMethod]),$arrArgs);
			}
		}

		$arrResult=array();
		foreach($this->_arrColl as $oObj){
			$arrResult[]=call_user_func_array(array($oObj,$sMethod),$arrArgs);
		}

		return $arrResult;
	}

	protected function checkType_($oObject){
		if(is_object($oObject)){
			if($oObject instanceof $this->_sType){
				return;
			}
			$sType=get_class($oObject);
		}else{
			$sType=gettype($oObject);
		}

		Q::E(Q::L('集合只能容纳 %s 类型的对象，而不是 %s 类型的值.','__QEEPHP__@Q',null ,$this->_sType,$sType));
	}

}
