<?php
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   SQL表达式封装（Learn QP!）($$)*/

!defined('Q_PATH') && exit;

class DbExpression{

	protected $_sExpr;

	public function __construct($sExpr){
		$this->_sExpr=$sExpr;
	}

	public function __toString(){
		return $this->_sExpr;
	}

	public function makeSql($oConnect,$sTableName=null,array $arrMapping=null,$hCallback=null){
		if(!is_array($arrMapping)){
			$arrMapping=array();
		}

		return $oConnect->qualifySql($this->_sExpr,$sTableName,$arrMapping,$hCallback);
	}

}
