<?php
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   所有数据库连接类的基类($$)*/

!defined('Q_PATH') && exit;

abstract class DbConnect{

	static public $_nQueryCount=0;
	protected $_bDebug=false;
	protected $_nFetchMode=Db::FETCH_MODE_ASSOC;
	protected $_sSchema='';
	public $_arrWriteDbConfig=array();
	public $_arrReadDbConfig=array();
	public $_arrCurrentDbConfig=array();
	public $_bSingleHost=true;
	public $_bIsInitConnect=false;
	public $_hWriteConnect=null;
	public $_arrHReadConnect=array();
	public $_arrHConnect=array();
	public $_hCurrentConnect=null;
	protected $_bPConnect =false;
	public $_nVersion;
	protected $_bConnected;
	protected $_bLogEnabled=FALSE;
	protected $_sLastSql='';
	protected $_hQueryResult=null;
	protected $_bIsRuntime=true;
	protected $_nRunTime=0;
	protected $_sDefaultDatabase='';
	protected $_nTransTimes=0;
	protected $_sPrimary;
	protected $_sAuto;
	protected $_bResultFieldNameLower=false;
	protected $_sParamStyle=Db::PARAM_QM;
	protected $_nTrueValue=1; //int
	protected $_nFalseValue=0; //int
	protected $_sNullValue='NULL'; //string
	protected $_bBindEnabled=false;
	protected $_arrComparison=array(
		'eq'=>'=',
		'neq'=>'!=',
		'gt'=>'>',
		'egt'=>'>=',
		'lt'=>'<',
		'elt'=>'<=',
		'notlike'=>'NOT LIKE',
		'like'=>'LIKE'
	);
	protected $_sTableName='';

	public function __construct(){
		if($GLOBALS['_commonConfig_']['APP_DEBUG']){
			$this->_bDebug=true;
		}

		$this->_bLogEnabled=$GLOBALS['_commonConfig_']['LOG_SQL_ENABLED'];
	}

	protected function debug(){
		if($this->_bDebug){// 记录操作结束时间
			Log::R(" RunTime:".$this->getQueryTime()."s SQL=".$this->getLastSql(),Log::SQL,true);
		}
	}

	public function Q($nTimes=''){
		if(empty($nTimes)){
			return self::$_nQueryCount++;
		}else{
			self::$_nQueryCount++;
		}
	}

	public function connect($arrMasterConfig=array(),$arrSlaveConfig=array(),$bSingleHost=true,$bIsInitConnect=false){
		if (is_array($arrMasterConfig) && !empty($arrMasterConfig)){// 配置主服务器数据
			$this->_arrWriteDbConfig=$arrMasterConfig;
		}

		$this->_bSingleHost=$bSingleHost;// 设置初始化
		$this->_bIsInitConnect=$bIsInitConnect;
		if($this->_bIsInitConnect){// 初始化连接
			if(!$this->writeConnect()){// 尝试连接主服务器 < 写 >
				return false;
			}

			if($GLOBALS['_commonConfig_']['DB_RW_SEPARATE'] || !$this->_bSingleHost){
				if(!is_array($arrSlaveConfig) || empty($arrSlaveConfig)){// 其他服务器数据
					$this->_arrReadDbConfig=$arrSlaveConfig;
				}else{
					$this->_arrReadDbConfig=$arrMasterConfig;
				}

				if($this->readConnect()){
					return false;
				}
			}
		}
	}

	abstract public function commonConnect($Config='',$nLinkid=0);

	abstract public function disConnect($hDbConnect=null,$bCloseAll=false);

	public function switchConnect($nLinkNum){
		if(isset($this->_arrHConnect[$nLinkNum])){// 存在指定的数据库连接序号
			$this->_hCurrentConnect=$this->linkids[$nLinkNum];
			return true;
		}else{
			return false;
		}
	}

	public function addConnect($Config,$nLinkNum=null){
		if(!is_array($Config) || empty($Config)){
			return false;
		}

		if(empty($nLinkNum)){
			$nLinkNum=count($this->_arrHConnect);
		}

		if(isset($this->_arrHConnect[$nLinkNum ])){
			return false;
		}

		// 创建新的数据库连接
		$this->_hCurrentConnect=$this->commonConnect($Config,$nLinkNum);
		$this->_arrHReadConnect[$nLinkNum ]=$this->_hCurrentConnect;
		$this->_arrHConnect[$nLinkNum ]=$this->_hCurrentConnect;
		$this->_sDefaultDatabase=$Config['db_name'];

		return $this->_hCurrentConnect;
	}

	public function writeConnect(){
		// 判断是否已经连接
		if($this->_hWriteConnect && is_resource($this->_hWriteConnect)){
			return $this->_hWriteConnect;
		}

		// 没有连接开始请求连接
		$hDb=$this->commonConnect($this->_arrWriteDbConfig);
		if(!$hDb || !is_resource($hDb)){
			return false;
		}

		$this->_hWriteConnect=$hDb;

		return $this->_hWriteConnect;
	}

	public function readConnect(){
		if(!$GLOBALS['_commonConfig_']['DB_RW_SEPARATE']){
			return $this->writeConnect();
		}

		// 如果有可用的Slave连接，随机挑选一台Slave
		if(is_array($this->_arrHReadConnect) && !empty($this->_arrHReadConnect)){
			$nKey=array_rand($this->_arrHReadConnect);
			if(isset($this->_arrHReadConnect[$nKey]) && is_resource($this->_arrHReadConnect[$nKey])){
				return $this->_arrHReadConnect[$nKey];
			}else{
				return false;
			}
		}

		// 连接到所有Slave数据库，如果没有可用的Slave机则调用Master
		if(!is_array($this->_arrReadDbConfig) || empty($this->_arrReadDbConfig)){
			return $this->writeConnect();
		}

		// 读服务器连接
		$this->_arrHReadConnect=array();
		$arrReadDbConfig=$this->_arrReadDbConfig;
		foreach($arrReadDbConfig as $arrRead){
			$hDb=$this->commonConnect($arrRead);
			if($hDb && is_resource($hDb)){
				$this->_arrHReadConnect[]=$hDb;
			}
		}

		// 如果没有一台可用的Slave则调用Master
		if(!is_array($this->_arrHReadConnect) || empty($this->_arrHReadConnect)){
			$this->errorMessage('Not availability slave db connection,call master db connection');
			return $this->writeConnect();
		}

		// 随机在已连接的Slave机中选择一台
		$sKey=array_rand($this->_arrHReadConnect);
		if(isset($this->_arrHReadConnect[$sKey]) && is_resource($this->rdbConn[$sKey])){
			return $this->_arrHReadConnect[$sKey];
		}

		// 如果选择的slave机器是无效的，并且可用的slave机器大于一台则循环遍历所有能用的slave机器
		if(count($this->_arrHReadConnect)>1){
			foreach($this->_arrHReadConnect as $hConnect){
				if(is_resource($hConnect)){
					return $hConnect;
				}
			}
		}

		// 如果没有可用的Slave连接，则继续使用Master连接
		return $this->writeConnect();
	}

	abstract public function query_($sSql,$bIsMaster=false);

	abstract public function selectDb($sDbName,$hDbHandle=null);

	abstract public function databaseVersion($nLinkid=0);

	abstract public function errorMessage($sMsg='',$hConnect=null);

	public function selectLimit($sSql,$nOffset=0,$nLength=30,$arrInput=null,$bLimit=true){
		if($bLimit===true){
			if(!is_null($nOffset)){
				$sSql.=' LIMIT '.(int)$nOffset;
				if(!is_null($nLength)){
					$sSql.=','.(int)$nLength;
				}else{
					$sSql.=',18446744073709551615';
				}
			}elseif(!is_null($nLength)){
				$sSql.=' LIMIT '.(int)$nLength;
			}
		}

		return $this->exec($sSql,$arrInput);
	}

	abstract public function getDatabaseNameList();

	abstract public function getTableNameList($sDbName=null);

	abstract public function getColumnNameList($sTableName,$sDbName=null);

	abstract public function isDatabaseExists($sDbName);

	abstract public function isTableExists($sTableName,$sDbName=null);

	public function getFullTableName($sTableName=''){
		$sSchema=isset($this->_arrCurrentDbConfig['db_schema'])?$this->_arrCurrentDbConfig['db_schema']:'';
		$sPrefix=isset($this->_arrCurrentDbConfig['db_prefix'])?$this->_arrCurrentDbConfig['db_prefix']:'';
		$sName=!empty($sTableName)?$sTableName:(isset($this->_arrCurrentDbConfig['table_name'])?$this->_arrCurrentDbConfig['table_name']:'');

		return(!empty($sSchema)?"`{$sSchema}`." :'')."`{$sPrefix}{$sName}`";
	}

	public function dumpNullString($Value){
		if(is_array($Value)){
			foreach($Value as $sKey=>$sValue){
				$Value[$sKey]=$this->dumpNullString($sValue);
			}
		}else{
			if(!isset($Value) || is_null($Value)){
				 $Value='NULL';
			}
		}

		return $Value;
	}

	public function query($Sql,$sDb=''){
		// 切换到指定数据库
		$sOldDb=$this->getCurrentDb();

		if($sDb and $sDb!=$sOldDb){
			$sOldDB=$this->selectDb($sDb);
		}

		// 执行
		$bRes=$this->query_($Sql);

		// 还原到以前的数据库
		if($sOldDb){
			$this->selectDb($sOldDb);
		}

		// 错误处理
		if($bRes===false){
			Q::E(Q::L('一条 SQL 语句在执行中出错:%s','__QEEPHP__@Q',null,$Sql));
		}

		return $bRes;
	}

	public function exec($sSql,$arrInput=null){
		// 如果有给定占位符，解析SQL
		if(is_array($arrInput)){
			$sSql=$this->fakeBind($sSql,$arrInput);
		}

		$hResult=$this->query_($sSql);
		if(is_resource($hResult)){
			$oDbRecordSet=Db::$_oFactory->createRecordSet($this,$this->_nFetchMode);
			$oDbRecordSet->setQueryResultHandle($hResult);
			return $oDbRecordSet;
		}elseif($hResult){
			return $hResult;
		}else{
			$sMoreMessage='';
			if($this->getErrorCode()==1062){
				$sMoreMessage=Q::L('主键重复','__QEEPHP__@Q').' Error:<br/>'.Q::L('你的操作中出现了重复记录，请修正错误！','__QEEPHP__@Q');
			}
			Q::E($sMoreMessage);
		}
	}

	abstract public function getInsertId();
	abstract function getNumRows($hRes=null);
	abstract public function getAffectedRows();
	abstract public function lockTable($sTableName);
	abstract public function unlockTable($sTableName);
	abstract public function setAutoCommit($bAutoCommit=false);
	abstract public function startTransaction();
	abstract public function endTransaction();
	abstract public function commit();
	abstract public function rollback();

	public function getOne($sSql,$arrInput=null,$bLimit=true){
		$oResult=$this->selectLimit($sSql,0,1,$arrInput,$bLimit);
		if($oResult===false){
			return false;
		}
		return $oResult->getRow(0);
	}

	public function getAllRows($sSql,array $arrInput=null){
		$oResult=$this->exec($sSql,$arrInput);
		if($oResult===false){
			return false;
		}
		return $oResult->getAllRows();
	}

	public function getRow($sSql,array $arrInput=null,$bLimit=true){
		$oResult=$this->selectLimit($sSql,0,1,$arrInput,$bLimit);
		if($oResult===false){
			return false;
		}
		return $oResult->getRow();
	}

	public function getCol($sSql,$nCol=0,array $arrInput=null){
		$oResult=$this->exec($sSql,$arrInput);
		if($oResult===false){
			return false;
		}
		return $oResult->fetchCol($nCol);
	}

	public function getComparison(){
		return $this->_arrComparison;
	}

	public function getBindEnabled(){
		return $this->_bBindEnabled;
	}

	public function getTrueValue(){
		return $this->_nTrueValue;
	}

	public function getFalseValue(){
		return $this->_nFalseValue;
	}

	public function getNullValue(){
		return $this->_sNullValue;
	}

	public function getParamStyle(){
		return $this->_sParamStyle;
	}

	public function getResultFieldNameLower(){
		return $this->_bResultFieldNameLower;
	}

	public function setResultFieldNameLower($bIsLower=true){
		$bOldValue=$this->_bResultFieldNameLower;
		$this->_bResultFieldNameLower=$bIsLower;
		return $bOldValue;
	}

	public function setLogEnabled($bLogEnabled=true){
		$bOldValue=$this->_bLogEnabled;
		$this->_bLogEnabled=$bLogEnabled;
		return $bOldValue;
	}

	public function setConnectHandle($hConnectHandle){
		if(!is_resource($hConnectHandle)){
			Q::E(Q::L('参数 $hConnectHandle 必须是有效的数据库连接','__QEEPHP__@Q'));
		}

		$hOldValue=$this->_hCurrentConnect;
		$this->_hCurrentConnect=$hConnectHandle;
		$this->_hWriteConnect=$hConnectHandle;
		return $hOldValue;
	}

	public function isConnected(){
		return $this->_bConnected;
	}

	public function getCurrentDb(){
		return $this->_sDefaultDatabase;
	}

	public function getQueryResult(){
		return $this->_hQueryResult;
	}

	public function getCurrentConnect(){
		return $this->_hCurrentConnect;
	}

	public function getErrorCode(){
		return $this->_nErrorCode;
	}

	public function getSchema(){
		return !empty($this->_arrCurrentDbConfig['db_schema'])?$this->_arrCurrentDbConfig['db_schema']:$GLOBALS['_commonConfig_']['DB_SCHEMA'];
	}

	public function getTablePrefix(){
		return !empty($this->_arrCurrentDbConfig['db_prefix'])?$this->_arrCurrentDbConfig['db_prefix']:$GLOBALS['_commonConfig_']['DB_PREFIX'];
	}

	protected function setLastSql($Sql){
		$sOldValue=$this->_sLastSql;
		$this->_sLastSql=$Sql;
		return $sOldValue;
	}

	public function getLastSql(){
		return $this->_sLastSql;
	}

	protected function setQueryTime($nSpecSec){
		$nOldValue=$this->_nRunTime;
		$this->_nRunTime=$nSpecSec;
		return $nOldValue;
	}

	public function getQueryTime(){
		return $this->_nRunTime;
	}

	public function getQueryFormatTime(){
		if($this->_nRunTime){
			return sprintf("%.6f sec",$this->_nRunTime);
		}
		return 'NULL';
	}

	public function getTransTimes(){
		return $this->_nTransTimes;
	}

	public function getPrimary(){
		return $this->_sPrimary;
	}

	public function getAuto(){
		return $this->_sAuto;
	}

	protected function setPConnect($bPConnect){
		$bOldValue=$this->_bPConnect;
		$this->_bPConnect=$bPConnect;
		return $bOldValue;
	}

	public function getPConnect(){
		return $this->_bPConnect;
	}

	public function getVersion(){
		return $this->_nVersion;
	}

	public function qualifyId($sName,$sAlias=null,$sAs=null){
		$sName=str_replace('`','',$sName);// 过滤'`'字符

		if(strpos($sName,'.')===false){// 不包含表名字
			$sName=$this->identifier($sName);
		}else{
			$arrArray=explode('.',$sName);
			foreach($arrArray as $nOffset=>$sName){
				if(empty($sName)){
					unset($arrArray[$nOffset]);
				}else{
					$arrArray[$nOffset]=$this->identifier($sName);
				}
			}
			$sName=implode('.',$arrArray);
		}

		if($sAlias){
			return "{$sName} {$sAs} ".$this->identifier($sAlias);
		}else{
			return $sName;
		}
	}

	abstract public function identifier($sName);

	public function qualifySql($sSql,$sTableName,array $arrMapping=null,$hCallback=null){
		if(empty($sSql)){
			return '';
		}

		$arrMatches=null;
		preg_match_all('/\[[a-z][a-z0-9_\.]*\]|\[\*\]/i',$sSql,$arrMatches,PREG_OFFSET_CAPTURE);
		$arrMatches=reset($arrMatches);
		if(!is_array($arrMapping)){
			$arrMapping=array();
		}

		$sOut='';
		$nOffset=0;
		foreach($arrMatches as $arrM){
			$nLen=strlen($arrM[0]);
			$sField=substr($arrM[0],1,$nLen-2);
			$arrArray=explode('.',$sField);
			switch(count($arrArray)){
				case 3:
					$sF=(!empty($arrMapping[$arrArray[2]]))?$arrMapping[$arrArray[2]]:$arrArray[2];
					$sTable="{$arrArray[0]}.{$arrArray[1]}";
					break;
				case 2:
					$sF=(!empty($arrMapping[$arrArray[1]]))?$arrMapping[$arrArray[1]]:$arrArray[1];
					$sTable=$arrArray[0];
					break;
				default:
					$sF=(!empty($arrMapping[$arrArray[0]]))?$arrMapping[$arrArray[0]]:$arrArray[0];
					$sTable=$sTableName;
			}

			if($hCallback){
				$sTable=call_user_func($hCallback,$sTable);
			}

			$sField=$this->qualifyId("{$sTable}.{$sF}");
			$sOut.=substr($sSql,$nOffset,$arrM[1]-$nOffset).$sField;
			$nOffset=$arrM[1]+$nLen;
		}
		$sOut.=substr($sSql,$nOffset);

		return $sOut;
	}

	public function qualifyIds($Names,$sAs=null){
		$arrArray=array();

		$Names=Q::normalize($Names);
		foreach($Names as $sAlias=>$name){
			if(!is_string($sAlias)){
				$sAlias=null;
			}
			$arrArray[]=$this->qualifyId($name,$sAlias,$sAs);
		}

		return $arrArray;
	}

	public function qualifyInto($sSql,array $arrParams=null,$ParamStyle=null,$bReturnParametersCount=false){
		if(is_null($ParamStyle)){
			$ParamStyle=$this->getParamStyle();
		}

		$hCallback=array($this,'qualifyStr');
		switch($ParamStyle){
			case Db::PARAM_QM:
				$arrParts=explode('?',$sSql);
				$sStr=$arrParts[0];
				$nOffset=1;
				foreach($arrParams as $argValue){
					if(!isset($arrParts[$nOffset])){
						break;
					}

					if(is_array($argValue)){
						$argValue=array_unique($argValue);
						$argValue=array_map($hCallback,$argValue);
						$sStr.=implode(',',$argValue).$arrParts[$nOffset];
					}else{
						$sStr.=$this->qualifyStr($argValue).$arrParts[$nOffset];
					}
					$nOffset++;
				}

				if($bReturnParametersCount){
					return array($sStr,count($arrParts));
				}else{
					return $sStr;
				}
			case Db::PARAM_CL_NAMED:
				$arrParts=preg_split('/(:[a-z0-9_\-]+)/i',$sSql,-1,PREG_SPLIT_DELIM_CAPTURE);
				$arrParts=array_filter($arrParts,'strlen');// 过滤空元素
				$nMax=count($arrParts);
				
				$sStr='';

				if($nMax<2){
					$sStr=$sSql;
				}else{
					for($nOffset=1;$nOffset<$nMax;$nOffset+=2){
						$sArgName=substr($arrParts[$nOffset],1);
						if(!isset($arrParams[$sArgName])){
							Q::E(sprintf('Invalid parameter "%s" for "%s"',$sArgName,$sSql));
						}

						if(is_array($arrParams[$sArgName])){
							$argValue=array_map($hCallback,$arrParams[$sArgName]);
							$sStr.=$arrParts[$nOffset-1].$this->qualifyStr(implode(',',$argValue)).' ';
						}else{
							$sStr.=$arrParts[$nOffset-1].$this->qualifyStr($arrParams[$sArgName]);
						}
					}
				}

				if($bReturnParametersCount){
					return array($sStr,intval($nMax/2)-1);
				}else{
					return $sStr;
				}
			default:
				return $sSql;
		}
	}

	abstract public function qualifyStr($Value);
	
	/**
	 * WHERE TP
	 */
	public function qualifyWhere($Where,$sTableName=null,$arrFieldsMapping=null,$hCallback=null){
		$sWhereStr='';

		// 直接使用字符串条件
		if(is_string($Where)){
			$sWhereStr=$Where;
		}else{// 使用数组条件表达式
			if(array_key_exists('logic_',$Where)){
				// 定义逻辑运算规则 例如 OR XOR AND NOT
				$sOperate=' '.strtoupper($Where['logic_']).' ';
				unset($Where['logic_']);
			}else{
				$sOperate=' AND ';// 默认进行 AND 运算
			}

			foreach($Where as $sKey=>$val){
				$sWhereStr.='(';
				if(strlen($sKey)-1===stripos($sKey,'_')){
					$sWhereStr.=$this->qualifyQEEPHPWhere($sKey,$val,$sTableName,$arrFieldsMapping,$hCallback);// 解析特殊条件表达式
				}else{
					if(is_array($val)){
						if(isset($val[0]) && is_string($val[0])){
							if(preg_match('/^(EQ|NEQ|GT|EGT|LT|ELT|NOTLIKE|LIKE)$/i',$val[0])){ // 比较运算
								$arrComparison=$this->getComparison();
								$sWhereStr.=$this->qualifyWhereField($sKey,$sTableName,$arrFieldsMapping,$hCallback).' '.$arrComparison[strtolower($val[0])].
									' '.(isset($val[1])?$this->qualifyStr($val[1]):'');
							}elseif(isset($val[0]) && 'exp'==strtolower($val[0])){ // 使用表达式
								$sWhereStr.='('.$this->qualifyWhereField($sKey,$sTableName,$arrFieldsMapping,$hCallback).' '.(isset($val[1])?$val[1]:'').') ';
							}elseif(isset($val[0]) && preg_match('/IN/i',$val[0])){ // IN 运算
								if(isset($val[1]) && is_string($val[1])){
									$val[1]=explode(',',$val[1]);
								}
								$sZone=implode(',',(isset($val[1])?$this->qualifyStr($val[1]):''));
								$sWhereStr.=$this->qualifyWhereField($sKey,$sTableName,$arrFieldsMapping,$hCallback).' '.strtoupper($val[0]).'('.$sZone.')';
							}elseif(preg_match('/BETWEEN/i',$val[0])){ // BETWEEN运算
								$arrData=isset($val[1]) && is_string($val[1])?explode(',',$val[1]):(isset($val[1])?$this->qualifyStr($val[1]):'');
								$sWhereStr.='('.$this->qualifyWhereField($sKey,$sTableName,$arrFieldsMapping,$hCallback).' BETWEEN '.
									(isset($arrData[0])?$arrData[0]:'').' AND '.(isset($arrData[1])?$arrData[1] :'').')';
							}else{
								Q::E(Q::L('表达式错误%s','__QEEPHP__@Q',null,(isset($arrData[0])?$arrData[0]:'')));
							}
						}else{
							$nCount=count($val);
							$sTemp=strtoupper(trim((isset($val[$nCount-1]))?$val[$nCount-1]:''));
							if(in_array($sTemp,array('AND','OR','XOR'))){
								$sRule=$sTemp;
								$nCount=$nCount-1;
							}else{
								$sRule='AND';
							}

							for($nI=0;$nI<$nCount;$nI++){
								$sData=isset($val[$nI]) && is_array($val[$nI]) && isset($val[$nI][1])?$val[$nI][1]:$val[ $nI ];
								if(isset($val[$nI]) && isset($val[$nI][0]) && 'exp'==strtolower($val[$nI][0])){
									$sWhereStr.='('.$this->qualifyWhereField($sKey,$sTableName,$arrFieldsMapping,$hCallback).' '.$sData.') '.$sRule.' ';
								}else{
									$arrComparison =$this->getComparison();
									$sOp=isset($val[$nI]) && is_array($val[$nI]) && isset($val[$nI][0])?$arrComparison[ strtolower($val[$nI][0]) ]:'=';
									$sWhereStr.='('.$this->qualifyWhereField($sKey,$sTableName,$arrFieldsMapping,$hCallback).' '.$sOp.' '.$this->qualifyStr($sData).') '.$sRule.' ';
								}
							}
							$sWhereStr=substr($sWhereStr,0,-4);
						}
					}else{
						$sWhereStr.=$this->qualifyWhereField($sKey,$sTableName,$arrFieldsMapping,$hCallback).'='.$this->qualifyStr($val);
					}
				}
				$sWhereStr.=')'.$sOperate;
			}

			if($sWhereStr){
				$sWhereStr=substr($sWhereStr,0,-strlen($sOperate));
			}else{
				$sWhereStr=trim($sOperate);
			}
		}

		return empty($sWhereStr) || $sWhereStr=='AND'?'':$sWhereStr;
	}

	public function qualifyQEEPHPWhere($sKey,$val,$sTableName=null,$arrFieldsMapping=null,$hCallback=null){
		$sWhereStr='';
		switch($sKey){
			case 'string_':// 字符串模式查询条件
				$sWhereStr=$val;
				break;
			case 'complex_':// 复合查询条件
				$sWhereStr=$this->qualifyWhere($val);
				break;
			case 'query_':
				$arrWhere=array();
				parse_str($val,$arrWhere);// 字符串模式查询条件
				if(array_key_exists('logic_',$arrWhere)){
					$sOp=' '.strtoupper($arrWhere['logic_']).' ';
					unset($arrWhere['logic_']);
				}else{
					$sOp=' AND ';
				}

				$arrValue=array();
				foreach($arrWhere as $sField=>$data){
					$arrValue[]=$this->qualifyWhereField($sField,$sTableName,$arrFieldsMapping,$hCallback).'='.$this->qualifyStr($data);
				}
				$sWhereStr=implode($sOp,$arrValue);
				break;
		}

		return $sWhereStr;
	}

	public function filterField($sKey,$sTableName){
		if(strpos($sKey,'.')){
			// 如果字段名带有 .，则需要分离出数据表名称和 schema
			$arrKey=explode('.',$sKey);
			switch(count($arrKey)){
				case 3:
					$sField=$this->qualifyId("{$arrKey[0]}.{$arrKey[1]}.{$arrKey[2]}");
					break;
				case 2:
					$sField=$this->qualifyId("{$arrKey[0]}.{$arrKey[1]}");
					break;
			}
		}else{
			$sField=$this->qualifyId("{$sTableName}.{$sKey}");
			$sField=$sKey;
		}

		return $sField;
	}

	public function qualifyWhereField($sField,$sTableName=null,$arrFieldsMapping=null,$hCallback=null){
		$sField=$this->filterField($sField,$sTableName);

		return $this->qualifySql($sField,$sTableName,$arrFieldsMapping,$hCallback);
	}

	public function qualifyTable($sTableName,$sSchema=null,$sAlias=null){
		if(strpos($sTableName,'.')!==false){
			$arrParts=explode('.',$sTableName);
			$sTableName=$arrParts[1];
			$sSchema=$arrParts[0];
		}

		$sTableName=trim($sTableName,'"');
		$sSchema=trim($sSchema,'"');
		// public 是默认的schema
		if(strtoupper($sSchema)=='PUBLIC'){
			$sSchema='';
		}
		$sI=$sSchema !=''?"\"{$sSchema}\".\"{$sTableName}\"":"\"{$sTableName}\"";

		return empty($sAlias)?$sI:$sI." \"{$sAlias}\"";
	}

	public function qualifyField($sFieldName,$sTableName=null,$sSchema=null,$sAlias=null){
		$sFieldName=trim($sFieldName,'"');
		if(strpos($sFieldName,'.')!==false){
			$arrParts=explode('.',$sFieldName);
			if(isset($arrParts[2])){
				$sSchema=$arrParts[0];
				$sTableName=$arrParts[1];
				$sFieldName=$arrParts[2];
			}elseif(isset($arrParts[1])){
				$sTableName=$arrParts[0];
				$sFieldName=$arrParts[1];
			}
		}

		$sFieldName=($sFieldName == '*')?'*':"\"{$sFieldName}\"";
		if(!empty($sTableName)){
			$sFieldName=$this->qualifyTable($sTableName,$sSchema).'.'.$sFieldName;
		}

		return empty($sAlias)?$sFieldName:"{$sFieldName} AS \"{$sAlias}\"";
	}

	public function getPlaceHolder(array $arrInput,array $arrRestrictedFields=null,$sParamStyle=null){
		$arrHolders=array();
		foreach(array_keys($arrInput) as $nOffset=>$sKey){
			if($arrRestrictedFields && !in_array($sKey,$arrRestrictedFields)){
				continue;
			}
			switch($sParamStyle){
				case Db::PARAM_QM:
					$arrHolders[$sKey]=array('?',$this->identifier($sKey));
					break;
				default:
					$arrHolders[$sKey]=array("{$sParamStyle}{$sKey}",$this->identifier($sKey));
			}
		}

		return $arrHolders;
	}

	public function parseSql($sTableName){
		$arrArgs=func_get_args();
		array_shift($arrArgs);
		list($arrWhere)=$this->parseSqlInternal($sTableName,$arrArgs);

		return $arrWhere;
	}

	public function parseSqlInternal($sTableName,array $arrArgs=null){
		if(empty($arrArgs)){
			return array(null,null,null);
		}

		$sSql=array_shift($arrArgs);
		if(is_array($sSql)){
			return $this->parseSqlArray_($sTableName,$sSql,$arrArgs);
		}else{
			return $this->parseSqlString_($sTableName,$sSql,$arrArgs);
		}
	}

	public function parseSqlArray_($sTableName,array $arrValue,array $arrArgs){
		static $arrKeywords=array('('=>true,'AND'=>true,'OR'=>true,'NOT'=>true,
			'BETWEEN'=>true,'CASE'=>true,'&&'=>true,'||'=>true,'='=>true,
			'<=>'=>true,'>='=>true,'>'=>true,'<='=>true,'<'=>true,'<>'=>true,
			'!='=>true,'IS'=>true,'LIKE'=>true
		);

		$arrParts=array();
		$sNextOp='';
		$nArgsCount=0;

		$arrUsedTables=array();
		foreach($arrValue as $sKey=>$value){
			if(is_int($sKey)){
				// 如果键名是整数，则判断键值是否是关键字或 ')' 符号。
				// 如果键值不是关键字，则假定为需要再分析的 SQL，需要再次调用 parseSqlInternal() 进行分析。
				if(is_string($value) && isset($arrKeywords[strtoupper($value)])){
					$sNextOp='';
					$sSql=$value;
				}elseif($value==')'){
					$sNextOp='AND';
					$sSql=$value;
				}else{
					if($sNextOp!=''){
						$arrParts[]=$sNextOp;
					}

					array_unshift($arrArgs,$value);
					list($sSql,$arrUt,$nArgsCount)=$this->parseSqlInternal($sTableName,$arrArgs);
					array_shift($arrArgs);
					if(empty($sSql)){
						continue;
					}

					$arrUsedTables=array_merge($arrUsedTables,$arrUt);
					if($nArgsCount>0){
						$arrArgs=array_slice($arrArgs,$nArgsCount);
					}
					$sNextOp='AND';
				}
				$arrParts[]=$sSql;
			}else{
				if($sNextOp!=''){// 如果键名是字符串，则假定为字段名
					$arrParts[]=$sNextOp;
				}

				if(strpos($sKey,'.')){
					$arrKey=explode('.',$sKey);// 如果字段名带有 .，则需要分离出数据表名称和 schema
					switch(count($arrKey)){
					case 3:
						$arrUsedTables[]="{$arrKey[0]}.{$arrKey[1]}";
						break;
					case 2:
						$arrUsedTables[]=$arrKey[0];
						break;
					}
				}else{
					$sField=$sKey;
				}

				if(is_array($value)){
					if(C::oneImensionArray($value)){// where 条件分析器
						$value=array_unique($value);
					}
					$arrValues=array();
					foreach($value as $v){
						if($v instanceof DbExpression){
							$arrValues[]=$v->makeSql($this,$sTableName);
						}else{
							$arrValues[]=$v;
						}
					}
					$arrParts[]=$this->qualifyWhere(array($sField=>$arrValues),$this->_sTableName,null,null);
					unset($arrValues);
					unset($value);
				}else{
					if($value instanceof DbExpression){
						$value=$this->makeSql($this,$sTableName);
					}else{
						$value=$this->qualifyStr($value);
					}
					$arrParts[]=$sField.'='.$value;
				}
				$sNextOp='AND';
			}
		}
		$arrParts=Q::normalize($arrParts);

		return array(implode(' ',$arrParts),$arrUsedTables,$nArgsCount);
	}

	public function parseSqlString_($sTableName,$sWhere,array $arrArgs){
		$arrMatches=array();

		preg_match_all('/\[[a-z][a-z0-9_\.]*\]/i',$sWhere,$arrMatches,PREG_OFFSET_CAPTURE);
		$arrMatches=reset($arrMatches);
		$sOut='';
		$nOffset=0;

		$arrUsedTables=array();
		foreach($arrMatches as $arrM){
			$nLen=strlen($arrM[0]);
			$sField=substr($arrM[0],1,$nLen-2);
			$arrValue=explode('.',$sField);
			switch(count($arrValue)){
			case 3:
				$sSchema=$arrValue[0];
				$sTable=$arrValue[1];
				$sField=$arrValue[2];
				$arrUsedTables[]=$sSchema.'.'.$sTable;
				break;
			case 2:
				$sSchema=null;
				$sTable=$arrValue[0];
				$sField=$arrValue[1];
				$arrUsedTables[]=$sTable;
				break;
			default:
				$sSchema=null;
				$sTable=$sTableName;
				$sField=$arrValue[0];
			}
			$sField=$this->identifier("{$sSchema}.{$sTable}.{$sField}");
			$sOut.=substr($sWhere,$nOffset,$arrM[1]-$nOffset).$sField;
			$nOffset=$arrM[1]+$nLen;
		}

		$sOut.=substr($sWhere,$nOffset);
		$sWhere=$sOut;

		$nArgsCount=null;// 分析查询条件中的参数占位符
		if(strpos($sWhere,'?')!==false){
			$sRet=$this->qualifyInto($sWhere,$arrArgs,Db::PARAM_QM,true);// 使用 ?作为占位符的情况
		}elseif(strpos($sWhere,':')!==false){
			$sRet=$this->qualifyInto($sWhere,reset($arrArgs),Db::PARAM_CL_NAMED,true);// 使用 : 开头的命名参数占位符
		}else{
			$sRet=$sWhere;
		}

		if(is_array($sRet)){
			list($sWhere,$nArgsCount)=$sRet;
		}else{
			$sWhere=$sRet;
		}

		return array($sWhere,$arrUsedTables,$nArgsCount);
	}

	abstract public function metaColumns($sTableName);

	public function fakeBind($sSql,$arrInput){
		// 分析‘?’ 占位符
		if($arrInput===null){
			$arrInput=array();
		}

		$arrValue=explode('?',$sSql);
		$sSql=array_shift($arrValue);
		foreach($arrInput as $sValue){
			if(isset($arrValue[0])){
				$oDbQualifyId=$this->qualifyStr($sValue);
				$sSql.=$oDbQualifyId->makeSql().array_shift($arrValue);
			}
		}

		return $sSql;
	}

}
