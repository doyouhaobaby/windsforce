<?php
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   数据库 记录集($$)*/

!defined('Q_PATH') && exit;

abstract class DbRecordSet{

	public $_nFetchMode;
	public $_bResultFieldNameLower=false;
	private $_arrData=array();
	protected $_nCount=0;
	protected $_oConnect;
	protected $_runSelectSql='';
	private $_hResult=null;

	public function __construct(DbConnect $oConnect,$nFetchMode=Db::FETCH_MODE_ARRAY){
		$this->_oConnect=$oConnect;
		$this->_nFetchMode=$nFetchMode;
	}

	public function __destruct(){
		$this->free();
	}

	public function setConnect(DbConnect $oConnect){
		$this->_oConnect=$oConnect;

		return $this;
	}

	public function getConnect(){
		return $this->_oConnect;
	}

	public function valid(){
		return $this->_hHandle!=null;
	}

	abstract public function free();

	public function reset($sOption=null){
		if($sOption==null){
			$this->_arrOptions=self::$_arrOptionsInit;
			$this->_arrQueryParams=self::$_arrQueryParamsInit;
		}elseif(array_key_exists($sOption,self::$_arrOptionsInit)){
			$this->_arrOptions[$sOption]=self::$_arrOptionsInit[$sOption];
		}

		return $this;
	}

	public function query($Sql){
		$this->_runSelectSql=$Sql;

		$oConnect=$this->getConnect();// 执行查询
		$Res=$oConnect->query($Sql);

		$this->_nCount=-1;// 重置
		$this->_arrData=array();
		if(!$Res){
			return false;
		}
		$this->setQueryResultHandle($Res);

		return true;
	}

	public function setQueryResultHandle($hRes){
		$hOldValue=$this->_hResult;
		$this->_hResult=$hRes;

		return $hOldValue;
	}

	public function getQueryResultHandle(){
		return $this->_hResult;
	}

	abstract public function fetch();

	public function getColumn($sColumn,$sSepa='-'){
		if(strpos($sColumn,',')){// 多个字段
			$arrRes=$this->getAllRows();
			if(!empty($arrRes)){
				$sColumn=explode(',',$sColumn);
				$sKey=array_shift($sColumn);
				$arrCols=array();
				foreach($arrRes as $arrVal){
					$sName=$arrVal[$sKey];
					$arrCols[$sName]='';
					foreach($sColumn as $sVal){
						$arrCols[$sName].=$arrVal[$sVal].$sSepa;
					}
					$arrCols[$sName]=substr($arrCols[$sName],0,-strlen($sSepa));
				}
				return $arrCols;
			}
		}else{
			if($sSepa===true){
				$arrRes=$this->getAllRows();
				if(!empty($arrRes)){
					$arrCols=array();
					foreach($arrRes as $arrVal){
						$arrCols[]=reset($arrVal);
					}

					return $arrCols;
				}
			}else{
				$arrResult=$this->fetch();
				if(!empty($arrResult)){
					return reset($arrResult);
				}
			}
		}

		return null;
	}

	public function getRow($nRow=null){
		$arrRow=$this->fetch();

		if($nRow===null){
			return $arrRow;
		}

		if(isset($arrRow[$nRow])){
			return $arrRow[$nRow];
		}else{
			return null;
		}
	}

	public function getAllRows(){
		$arrRet=array();
		while(($arrRow=$this->fetch())!==false){
			$arrRet[]=$arrRow;
		}

		return $arrRet;
	}

	public function fetchCol($nCol=0){
		$nOldValue=$this->_nFetchMode;
		$this->_nFetchMode=Db::FETCH_MODE_ARRAY;

		$arrCols=array();
		while(($arrRow=$this->fetch())!==false){
			$arrCols[]=$arrRow[$nCol];
		}
		$this->_nFetchMode=$nOldValue;

		return $arrCols;
	}

	public function fetchAllRefby(array $arrFields,&$arrFieldsValue,&$arrRef,$bCleanUp){
		// 初始化查询参数
		$arrRef=$arrFieldsValue=$arrData=array();

		$nOffset=0;
		if ($bCleanUp){// 获取结果后释放内存
			while(($arrRow=$this->fetch())!==false){
				$arrData[$nOffset]=$arrRow;
				foreach($arrFields as $sField){
					$sFieldValue=$arrRow[$sField];
					$arrFieldsValue[$sField][$nOffset]=$sFieldValue;
					$arrRef[$sField][$sFieldValue][]=&$arrData[$nOffset];
					unset($arrData[$nOffset][$sField]);
				}
				$nOffset++;
			}
		}else{
			while(($arrRow=$this->fetch())!==false){
				$arrData[$nOffset]=$arrRow;
				foreach($arrFields as $sField){
					$sFieldValue=$arrRow[$sField];
					$fields_value[$sField][$nOffset]=$sFieldValue;
					$arrRef[$sField][$sFieldValue][]=&$arrData[$nOffset];
				}
				$nOffset++;
			}
		}

		return $arrData;
	}

	public function fetchObject($sClassName,$bReturnFirst=false){
		$arrObjs=array();
		$bIsAr=is_subclass_of($sClassName,'Model');
		while(($arrRow=$this->fetch())!==false){
			$oObj=$bIsAr?new $sClassName($arrRow,'',true):new $sClassName($arrRow);
			if($bReturnFirst){
				return $oObj;
			}
			$arrObjs[]=$oObj;
		}

		return Coll::createFromArray($arrObjs,$sClassName);
	}

}
