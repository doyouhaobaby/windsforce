<?php
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   数据库备份类（Modify from EC!）($$)*/

class DbBackup{

	protected $_nMaxSize=2097152; // 2M
	protected $_bIsShort=false;
	protected $_nOffset=300;
	protected $_sDumpSql='';
	protected $_nSqlNum=0;
	protected $_sErrorMessage='';
	protected $_oDbConnect;
	protected $_sDbChar='utf8';

	public function __construct(&$oDbConnect,$nMaxSize=0,$sDbChar='utf8'){
		$this->_oDbConnect=$oDbConnect;

		if($nMaxSize>0){
			$this->_nMaxSize=$nMaxSize;
		}

		if($sDbChar){
			$this->_sDbChar=$sDbChar;
		}
	}

	public function setIsShort($bIsShort=true){
		$this->_bIsShort=$bIsShort;
	}

	public function setMaxSize($nMaxSize){
		$this->_nMaxSize=$nMaxSize;
	}

	public function getDumpSql(){
		return $this->_sDumpSql;
	}

	public function getTableDf($sTable,$bAddDrop=false){
		if ($bAddDrop){
			$sTableDf="DROP TABLE IF EXISTS `$sTable`;\r\n";
		}else{
			$sTableDf='';
		}

		$arrTemp=$this->_oDbConnect->getRow("SHOW CREATE TABLE `{$sTable}`",null,false);
		$sTmpSql=$arrTemp[ 'Create Table' ];
		$sTmpSql=substr($sTmpSql,0,strrpos($sTmpSql,")") + 1); //去除行尾定义。

		if($this->_oDbConnect->getVersion()>='4.1'){
			$sTableDf.=$sTmpSql." ENGINE=MyISAM DEFAULT CHARSET=".str_replace('-','',$this->_sDbChar).";\r\n";
		}else{
			$sTableDf.=$sTmpSql. " TYPE=MyISAM;\r\n";
		}

		return $sTableDf;
	}

	public function getTableData($sTable,$nPos){
		$nPostPos=$nPos;

		$nTotal=$this->_oDbConnect->getRow("SELECT COUNT(*) AS count FROM {$sTable}");// 获取数据表记录总数
		$nTotal=$nTotal['count'];

		if($nTotal==0 || $nPos>=$nTotal){// 无须处理
			return -1;
		}

		$nCycleTime=ceil(($nTotal-$nPos)/$this->_nOffset); //确定循环次数&每次取offset条数。需要取的次数
		for($nI=0;$nI<$nCycleTime;$nI++){// 循环查数据表
			$arrData=$this->_oDbConnect->getAllRows("SELECT * FROM {$sTable} LIMIT ".($this->_nOffset*$nI+$nPos).','.$this->_nOffset);// 获取数据库数据
			$nDataCount=count($arrData);
			
			// 不存在数据，则跳出循环
			if(!isset($arrData[0])){
				continue;
			}

			$arrFields=array_keys($arrData[0]);
			$sStartSql="INSERT INTO `{$sTable}` (`".implode("`,`",$arrFields)."`) VALUES ";
			for($nJ=0;$nJ<$nDataCount;$nJ++){// 循环将数据写入
				$sRecord=$this->_oDbConnect->qualifyStr($arrData[$nJ]);//过滤非法字符
				$sRecord=$this->_oDbConnect->dumpNullString($sRecord); //处理null值

				if($this->_bIsShort){// 检查是否能写入，能则写入
					if($nPostPos==$nTotal-1){
						$sTmpDumpSql=" (".implode(",",$sRecord).")".($nDataCount==1?';':',')."\r\n";
					}else{
						if($nJ==$nDataCount-1){
							$sTmpDumpSql="(".implode(",",$sRecord).");\r\n";
						}else{
							$sTmpDumpSql="(".implode(",",$sRecord)."),\r\n";
						}
					}

					if($nPostPos==$nPos){// 第一次插入数据
						$sTmpDumpSql=$sStartSql."\r\n".$sTmpDumpSql;
					}else{
						if($nJ==0){
							$sTmpDumpSql=$sStartSql."\r\n".$sTmpDumpSql;
						}
					}
				}else{
					$sTmpDumpSql=$sStartSql."(".implode(",",$sRecord).");\r\n";
				}

				if(strlen($this->_sDumpSql)+strlen($sTmpDumpSql)>$this->_nMaxSize-32){
					if($this->_nSqlNum==0){
						$this->_sDumpSql.=$sTmpDumpSql;//当是第一条记录时强制写入
						$this->_nSqlNum++;
						$nPostPos++;
						if($nPostPos==$nTotal){// 所有数据已经写完
							return -1;
						}
					}
					return $nPostPos;
				}else{
					$this->_sDumpSql.=$sTmpDumpSql;
					$this->_nSqlNum++;//记录sql条数
					$nPostPos++;
				}
			}
		}

		return -1;// 所有数据已经写完
	}

	public function dumpTable($sPath,$nVol){
		$arrTables=$this->getTablesList($sPath);
		if($arrTables===false){
			return false;
		}

		if(empty($arrTables)){
			return $arrTables;
		}

		$this->_sDumpSql=$this->makeHead($nVol);

		foreach($arrTables as $sTable=>$nPos){
			if($nPos==-1){
				$sTableDf=$this->getTableDf($sTable,true);// 获取表定义，如果没有超过限制则保存

				if(strlen($this->_sDumpSql)+strlen($sTableDf)>$this->_nMaxSize-32){
					if($this->_nSqlNum==0){// 第一条记录，强制写入
						$this->_sDumpSql.=$sTableDf;
						$this->_nSqlNum+=2;
						$arrTables[$sTable]=0;
					}
					break;
				}else{// 已经达到上限
					$this->_sDumpSql.=$sTableDf;
					$this->_nSqlNum+=2;
					$nPos=0;
				}
			}


			$nPostPos=$this->getTableData($sTable,$nPos);// 尽可能多获取数据表数据
			if($nPostPos==-1){// 该表已经完成，清除该表
				unset($arrTables[$sTable]);
			}else{// 该表未完成。说明将要到达上限,记录备份数据位置
				$arrTables[$sTable]=$nPostPos;
				break;
			}
		}

		$this->_sDumpSql.='-- QeePHP Database Backup Program';
		$this->putTablesList($sPath,$arrTables);

		return $arrTables;
	}

	public function makeHead($nVol){
		// 系统信息
		$arrSysInfo['os']=PHP_OS;
		$arrSysInfo['web_server']=Q_PATH;
		$arrSysInfo['php_ver']=PHP_VERSION;
		$arrSysInfo['database_ver']=$this->_oDbConnect->getVersion();
		$arrSysInfo['date']=date('Y-m-d H:i:s');
		$sHead="-- QeePHP Database Backup Program\r\n".
				"-- " .$arrSysInfo['web_server']."\r\n".
				"-- \r\n".
				"-- OS : ".$arrSysInfo["os"]."\r\n".
				"-- DATE : ".$arrSysInfo["date"]."\r\n".
				"-- DATABASE SERVER VERSION : ".$arrSysInfo['database_ver']."\r\n".
				"-- PHP VERSION : ".$arrSysInfo['php_ver']."\r\n".
				"-- Vol : ".$nVol."\r\n";

		return $sHead;
	}

	static public function getHead($sPath){
		// 获取Sql文件头部信息
		$sSqlInfo=array('os'=>'',
			'date'=>'',
			'ver'=>'',
			'php_ver'=>0,
			'vol'=>0,
			'database_ver'=>0
		);

		$hFp=fopen($sPath,'rb');
		$sStr=fread($hFp,500);
		fclose($hFp);

		$arrInfo=explode("\n",$sStr);
		foreach($arrInfo as $sVal){
			$nPos=strpos($sVal,':');
			if($nPos>0){
				$sType=trim(substr($sVal,0,$nPos),"-\n\r\t ");
				$sValue=trim(substr($sVal,$nPos+1),"/\n\r\t ");
				if($sType=='DATE'){
					$arrInfo['date']=$sValue;
				}elseif($sType=='DATABASE SERVER VERSION'){
					$arrInfo['database_ver']=$sValue;
				}elseif($sType=='PHP VERSION'){
					$arrInfo['php_ver']=$sValue;
				}elseif($sType=='Vol'){
					$arrInfo['vol']=$sValue;
				}elseif($sType=='OS'){
					$arrInfo['os']=$sValue;
				}
			}
		}

		return $arrInfo;
	}

	public function getTablesList($sPath){
		if(!is_file($sPath)){
			$this->_sErrorMessage=sprintf('%s is not exists !',$sPath);
			return false;
		}

		$arrData=array();
		$sStr=@file_get_contents($sPath);
		if(!empty($sStr)){
			$arrTmp=explode("\n",$sStr);
			foreach($arrTmp as $sVal){
				$sVal=trim($sVal,"\r;");
				if(!empty($sVal)){
					list($sTable,$nCount)=explode(':',$sVal);
					$arrData[$sTable]=$nCount;
				}
			}
		}

		return $arrData;
	}

	public function putTablesList($sPath,$arrData){
		if(is_array($arrData)){
			$sStr='';
			foreach($arrData as $sKey=>$sVal){
				$sStr.=$sKey.':'.$sVal.";\r\n";
			}

			if(@file_put_contents($sPath,$sStr)){
				return true;
			}else{
				$this->_sErrorMessage=sprintf('Can not write %s',$sPath);
				return false;
			}
		}else{
			$this->_sErrorMessage='Variable $arrData can only be an array!';
			return false;
		}
	}

	static public function getRandomName(){
		$sStr=date('Ymd');
		for($nI=0;$nI<6;$nI++){
			$sStr.=chr(mt_rand(97,122));
		}
		return $sStr;
	}

	public function getErrorMessage(){
		return $this->_sErrorMessage;
	}

}
