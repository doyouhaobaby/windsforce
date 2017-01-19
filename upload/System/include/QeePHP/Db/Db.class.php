<?php
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   数据库访问统一入口，可以通过这个接口与数据库进行交互($$)*/

!defined('Q_PATH') && exit;

class Db{

	static public $_sDefaultFactoryName='DbFactoryMysql';
	public $_sFactoryName='';
	static public $_oFactory;
	public $_oConnect=null;
	static public $_arrWriteDbConfig=array();
	static  public $_arrReadDbConfig=array();
	public $_hWriteConnect=null;
	public $_arrHReadConnect=array();
	static public $_bSingleHost=true;
	static public $_bIsInitConnect=true;
	static private $_oDefaultDbIns;
	const PARAM_QM='?';// 问号作为参数占位符
	const PARAM_CL_NAMED=':';// 冒号开始的命名参数
	const FETCH_MODE_ARRAY=1;// 返回的每一个记录就是一个索引数组
	const FETCH_MODE_ASSOC=2;// 返回的每一个记录就是一个以字段名作为键名的数组
	static public $_arrDsn=array();

	public function __construct($Dsn=null){
		self::$_arrDsn=$arrDsn=$this->parseConfig($Dsn);

		if(!$GLOBALS['_commonConfig_']['DB_DISTRIBUTED']){
			self::$_arrWriteDbConfig=self::$_arrDsn;
			self::$_arrReadDbConfig=array();
			self::$_bSingleHost=true;
		}else{
			$arrReadWrite=$this->parseReadWrite();
			self::$_arrWriteDbConfig=$arrReadWrite['master'];
			self::$_arrReadDbConfig=$arrReadWrite['slave'];
			self::$_bSingleHost=$arrReadWrite['single_host'];
		}

		$sFactoryName=isset($arrDsn['db_type'])?'DbFactory'.ucfirst(strtolower($arrDsn['db_type'])):self::$_sDefaultFactoryName;
		$this->_sFactoryName=$sFactoryName;

		// 创建工厂&开始连接
		self::$_oFactory=new $sFactoryName();
		$this->_oConnect=self::$_oFactory->createConnect();
	}

	static public function createDbInstance($Dsn=null,$sId=null ,$bDefaultIns=true,$bConnect=true){
		// 如果默认数据库对象存在，又选用默认的，则直接返回
		if($bDefaultIns and self::$_oDefaultDbIns){
			return self::$_oDefaultDbIns;
		}

		// 创建一个数据库Db对象
		$oDb=new self($Dsn);
		if($bConnect){
			$oDb->connect(self::$_arrWriteDbConfig,self::$_arrReadDbConfig,self::$_bSingleHost,self::$_bIsInitConnect);
		}

		// 设置全局对象
		if($bDefaultIns){
			self::$_oDefaultDbIns=$oDb;
		}

		return $oDb;
	}

	public function getFactory(){
		return self::$_oFactory;
	}

	protected function parseConfig($Config=''){
		$arrDsn=array();

		if(is_array($Config) && !C::oneImensionArray($Config)){
			$arrDsn=$Config;
		}elseif(!empty($Config['db_dsn'])){// 如果DSN字符串则进行解析
			$arrDsn[]=$this->parseDsn($Config['db_dsn']);
		}elseif(is_array($Config) && C::oneImensionArray($Config)){
			$arrDsn[]=$Config;
		}elseif(!empty($GLOBALS['_commonConfig_']['DB_GLOBAL_DSN'])){
			$arrDsn=$GLOBALS['_commonConfig_']['DB_GLOBAL_DSN'];
		}

		if(!$GLOBALS['_commonConfig_']['DB_DISTRIBUTED']){
			$arrDsn=$this->fillFull(isset($arrDsn[0])?$arrDsn[0]:array());
			return $arrDsn;
		}else{
			foreach($arrDsn as $nKey=>&$arrValue){
				$arrValue=$this->fillFull($arrValue);
			}
			return $arrDsn;
		}
	}

	protected function fillFull($arrConfig=array()){
		return array_merge($arrConfig,
			array('db_type'=>$GLOBALS['_commonConfig_']['DB_TYPE'],'db_schema'=>$GLOBALS['_commonConfig_']['DB_SCHEMA'],
				'db_user'=>$GLOBALS['_commonConfig_']['DB_USER'],
				'db_password'=>$GLOBALS['_commonConfig_']['DB_PASSWORD'],
				'db_host'=>$GLOBALS['_commonConfig_']['DB_HOST'],
				'db_port'=>$GLOBALS['_commonConfig_']['DB_PORT'],
				'db_name'=>$GLOBALS['_commonConfig_']['DB_NAME'],
				'db_prefix'=>$GLOBALS['_commonConfig_']['DB_PREFIX'],
				'db_dsn'=>$GLOBALS['_commonConfig_']['DB_DSN'],
				'db_params'=>$GLOBALS['_commonConfig_']['DB_PARAMS']
			)
		);
	}

	protected function parseDsn($sDsn){
		// dsn为空，直接返回
		if(empty($sDsn)){
			return false;
		}

		// 分析dsn参数
		$arrInfo=parse_url($sDsn);
		if($arrInfo['scheme']){
			$arrDsn=array(
				'db_type'=>$arrInfo['scheme'],
				'db_schema'=>$arrInfo['scheme'],
				'db_user'=>isset($arrInfo['user'])?$arrInfo['user']:'',
				'db_password'=>isset($arrInfo['pass'])?$arrInfo['pass']:'',
				'db_host'=>isset($arrInfo['host'])?$arrInfo['host']:'',
				'db_port'=>isset($arrInfo['port'])?$arrInfo['port']:'',
				'db_name'=>isset($arrInfo['path'])?substr($arrInfo['path'],1):'',
				'db_prefix'=>$GLOBALS['_commonConfig_']['DB_PREFIX']
			);
		}else{
			preg_match('/^(.*?)\:\/\/(.*?)\:(.*?)\@(.*?)\:([0-9]{1,6})\/(.*?)$/',trim($sDsn),$arrMatches);
			$arrDsn=array(
				'db_type'=>$arrMatches[1],
				'db_schema'=>$arrMatches[1],
				'db_user'=>$arrMatches[2],
				'db_password'=>$arrMatches[3],
				'db_host'=>$arrMatches[4],
				'db_port'=>$arrMatches[5],
				'db_name'=>$arrMatches[6],
				'db_prefix'=>$GLOBALS['_commonConfig_']['DB_PREFIX']
			);
		}

		return $arrDsn;
	}

	public function parseReadWrite(){
		$arrDsn=self::$_arrDsn;
		$bSingleHost=true;

		if($GLOBALS['_commonConfig_']['DB_RW_SEPARATE']){
			$arrMaster=array_shift($arrDsn);
		}else{
			$arrMaster=$arrDsn[floor(mt_rand(0,count($arrDsn)-1))];
		}

		$arrSlave=array();
		if(count($arrDsn)>0){
			$arrSlave=$arrDsn;
			$bSingleHost=false;
		}

		$arrResult=array('master'=>$arrMaster,'slave'=>$arrSlave,'single_host'=>$bSingleHost);
		self::$_arrDsn=null;

		return $arrResult;
	}

	public function connect($arrMasterConfig=array(),$arrSlaveConfig=array(),$bSingleHost=true,$bIsInitConnect=false,$sId=null){
		return $this->_oConnect->connect($arrMasterConfig,$arrSlaveConfig,$bSingleHost,$bIsInitConnect,$sId);
	}

	public function disConnect($hDbConnect=null,$bCloseAll=false){
		return $this->_oConnect->disConnect($hDbConnect ,$bCloseAll);
	}

	static public function RUN($Dsn=null,$sId=null ,$bDefaultIns=true,$bConnect=true){
		return self::createDbInstance($Dsn,$sId,$bDefaultIns ,$bConnect);
	}

	public function addConnect($Config,$nLinkNum=null){
		$arrDsn=$this->parseConfig($Config);
		$arrReadWrite=$this->parseReadWrite();
		$arrReadDbConfig=$arrReadWrite['slave'];

		return $this->_oConnect->addConnect($arrReadDbConfig,$nLinkNum);
	}

	public function switchConnect($nLinkNum){
		return $this->_oConnect->switchConnect($nLinkNum);
	}

	public function setConnect(DbConnect $oConnect){ }

	public function getConnect(){
		return $this->_oConnect;
	}

	public function selectDb($sDbName,$hDbHandle=null){
		return $this->_oConnect->selectDb($sDbName,$hDbHandle);
	}

	public function query($Sql,$sDb=''){
		return $this->_oConnect->query($Sql ,$sDb);
	}

	public function insert(array $arrData,$sTableName='',array $RstrictedFields=null,$bReplace=false){
		$sType=$bReplace?'REPLACE':'INSERT';

		$arrHolders=$this->_oConnect->getPlaceHolder($arrData,$RstrictedFields);
		$sSql=$sType.' INTO '.$this->_oConnect->qualifyId($sTableName).'(';

		if($this->_oConnect->getBindEnabled()){
			$arrFields=array();// 使用参数绑定
			$arrValues=array();
			foreach($arrHolders as $sKey=>$arrH){
				list($sHolder,$sFieldName)=$arrH;
				$arrFields[]=$sFieldName;
				$arrValues[$sKey]=$sHolder;
			}

			$sSql.=implode(',',$arrFields).')VALUES('.implode(',',$arrValues).')';
			$oStmt=$this->_oConnect->prepare($sSql);
			foreach($arrValues as $sKey=>$arrHolder){
				if($arrData[$sKey] instanceof DbExpression){
					$oStmt->bindParam($arrHolder,$arrData[$sKey]->makeSql($this->_oConnect,$sTableName));
				}else{
					$oStmt->bindParam($arrHolder,$arrData[$sKey]);
				}
			}
			return $oStmt->exec();
		}else{
			$arrFields=array();
			$arrValues=array();

			foreach($arrHolders as $sKey=>$arrH){
				list(,$sFieldName)=$arrH;
				if($arrData[$sKey] instanceof DbExpression){
					$sValue=$this->_oConnect->qualifyStr($arrData[$sKey]->makeSql($this->_oConnect,$sTableName));
					if(strtolower($sValue)!=='null'){
						$arrFields[]=$sFieldName;
						$arrValues[]=$sValue;
					}
				}else{
					$sValue=$this->_oConnect->qualifyStr($arrData[$sKey]);
					if(strtolower($sValue)!=='null'){
						$arrFields[]=$sFieldName;
						$arrValues[]=$sValue;
					}
				}
				unset($arrData[$sKey]);
			}
			$sSql.=implode(',',$arrFields).')VALUES('.implode(',',$arrValues).')';
			return $this->_oConnect->exec($sSql);
		}
	}

	public function delete($sTableName,$arrWhere=null,$Order=null,$Limit=null){
		list($arrWhere)=$this->_oConnect->parseSqlInternal($sTableName,$arrWhere);

		$sSql='DELETE FROM '.$this->_oConnect->qualifyId($sTableName);
		if($arrWhere){
			$sSql.=' WHERE '.$arrWhere;
		}

		if($Order){
			$sSql.='ORDER BY '.$Order;
		}

		if($Limit){
			$sSql.='LIMIT '.$Limit;
		}

		$this->_oConnect->exec($sSql);
	}

	public function update($sTableName,$Row,array $Where=null,$Limit='',$Order='',array $RstrictedFields=null){
		list($Where)=$this->_oConnect->parseSqlInternal($sTableName,$Where);

		if($Where){
			$Where=' WHERE '.$Where;
		}

		if(!is_array($Row) && !($Row instanceof DbExpression)){
			Q::E(Q::L('$arrRow的格式只能是数组和DbException的实例。','__QEEPHP__@Q'));
		}

		if(!is_array($Row)){
			$Row=$Row->makeSql($this->_oConnect,$sTableName);
		}

		if($Order){
			$Order='ORDER BY '.$Order;
		}

		if($Limit){
			$Limit='LIMIT '.$Limit;
		}

		$sSql='UPDATE '.$this->_oConnect->qualifyId($sTableName).' SET ';
		$arrHolders=$this->_oConnect->getPlaceHolder($Row,$RstrictedFields);

		if($this->_oConnect->getBindEnabled()){
			$arrPairs=array();// 使用参数绑定
			$arrValues=array();
			foreach($arrHolders as $sKey=>$arrH){
				list($sHolder,$sFieldName)=$arrH;
				$arrPairs[]=$sFieldName.'='.$sHolder;
				$arrValues[$sKey]=$sHolder;
			}
			$sSql.=implode(',',$arrPairs);
			$sSql.="{$Where}{$Order}{$Limit};";
			$oStmt=$this->_oConnect->prepare($sSql);
			foreach($arrValues as $sKey=>$sHolder){
				if($Row[$sKey] instanceof DbExpression){
					$oStmt->bindParam($sHolder,$Row[$sKey]->makeSql($this->_oConnect,$this->_sTableName));
				}else{
					$oStmt->bindParam($sHolder,$Row[$sKey]);
				}
			}
			$oStmt->exec();
		}else{
			$arrPairs=array();
			foreach($arrHolders as $sKey=>$arrH){
				list($sHolder,$sFieldName)=$arrH;
				$sPair=$sFieldName.'=';
				if($Row[$sKey] instanceof DbExpression){
					$sPair.=$this->_oConnect->qualifyStr($Row[$sKey]->makeSql($this->_oConnect,$this->_sTableName));
				}else{
					$sPair.=$this->_oConnect->qualifyStr($Row[$sKey]);
				}
				$arrPairs[]=$sPair;
			}
			$sSql.=implode(',',$arrPairs);
			$sSql.="{$Where}{$Order}{$Limit};";
			$this->_oConnect->exec($sSql);
		}
	}

	public function select($TableName){
		$oSelect=new DbSelect($this->_oConnect);
		$oSelect->from($TableName);
		$arrArgs=func_get_args();
		if(!empty($arrArgs)){
			call_user_func_array(array($oSelect,'where'),$arrArgs);
		}

		return $oSelect;
	}

	public function getFullTableName($sTableName=''){
		return $this->getConnect()->getFullTableName($sTableName);
	}

	public function getOne($sSql,$arrInput=null){
		$oResult=$this->_oConnect->selectLimit($sSql,0,1,$arrInput);
		return $oResult->getRow(0);
	}

	public function getAllRows($sSql,array $arrInput=null){
		$oResult=$this->_oConnect->exec($sSql,$arrInput);
		return $oResult->getAllRows();
	}

	public function getRow($sSql,array $arrInput=null){
		$oResult=$this->_oConnect->selectLimit($sSql,0,1,$arrInput);
		return $oResult->getRow();
	}

	public function getCol($sSql,$nCol=0,array $arrInput=null){
		$oResult=$this->_oConnect->exec($sSql,$arrInput);
		return $oResult->fetchCol($nCol);
	}

}
