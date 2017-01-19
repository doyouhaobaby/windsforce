<?php
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   数据库表入口（Learn QP!）($$)*/

!defined('Q_PATH') && exit;

class DbTableEnter{

	public $_sSchema;
	public $_sName;
	public $_sPrefix;
	public $_sAlias;
	protected $_pk;
	protected static $_arrMeta=array();
	protected static $_arrFields=array();
	protected $_bInited;
	protected $_arrCurrentDbConfig;
	protected $_oConnect;
	protected $_oDb;
	protected static $_arrDsn=array();

	public function __construct(array $arrConfig=null){
		$this->_arrConfig=$arrConfig;

		$arrConfig=array_shift($arrConfig);
		if(!empty($arrConfig['db_schema'])){
			$this->_sSchema=$arrConfig['db_schema'];
		}

		if(!empty($arrConfig['table_name'])){
			$this->_sName=$arrConfig['table_name'];
		}

		if(!empty($arrConfig['db_prefix'])){
			$this->_sPrefix=$arrConfig['db_prefix'];
		}

		if(!empty($arrConfig['pk'])){
			$this->_pk=$arrConfig['pk'];
		}

		if(!empty($arrConfig['connect'])){
			$this->setConnect($arrConfig['connect']);
		}
	}

	public function insert(array $arrRow){
		if(!$this->_bInited){
			$this->init();
		}
		$this->getDb()->insert($arrRow,$this->getFullTableName(),self::$_arrMeta[$this->_sCacheId]['field']);
		$arrPkValue=array();
		if(self::$_arrMeta[$this->_sCacheId]['auto_'] && self::$_arrMeta[$this->_sCacheId]['pk_']){
			$arrPkValue[self::$_arrMeta[$this->_sCacheId]['pk_'][0]]=$this->_oConnect->getInsertId();
		}
		return $arrPkValue;
	}

	public function delete($Where /* 最后两个参数为order,limit,如果没有这个条件，请务必在后面添加上null,或者‘’占位 */){
		if(!$this->_bInited){
			$this->init();
		}

		if(is_int($Where) || ((int)$Where==$Where && $Where>0)){
			if(count(self::$_arrMeta[$this->_sCacheId]['pk_'])>1){// 如果 $Where 是一个整数，则假定为主键字段值
				Q::E(Q::L('使用复合主键时，不允许通过直接指定主键值来删除记录。' ,'__QEEPHP__@Q'));
			}else{
				$Where=array(array(self::$_arrMeta[$this->_sCacheId]['pk_'][0]=>(int)$Where));
			}
		}else{
			$Where=func_get_args();
		}

		if(count($Where)>=3){
			$limit=array_pop($Where);// Limit
			$order=array_pop($Where);// Order
		}else{
			$limit='';// Limit
			$order='';// Order
		}

		if($limit===null){
			$limit='';
		}
		if($order===null){
			$order ='';
		}

		$this->getDb()->delete($this->getFullTableName(),$Where,$order,$limit);
		return $this->_oConnect->getAffectedRows();
	}

	public function update($Row,$Where=null/* 最后两个参数为order,limit,如果没有这个条件，请务必在后面添加上null,或者‘’占位 */){
		if(!$this->_bInited){
			$this->init();
		}

		if(is_null($Where)){
			if(is_array($Row)){
				$Where=array();
				foreach(self::$_arrMeta[$this->_sCacheId]['pk_'] as $sPk){
					if(!isset($Row[$sPk]) || strlen($Row[$sPk]==0)){
						$Where=array();
						break;
					}
					$Where[$sPk]=$Row[$sPk];
				}
				$Where=array($Where);
			}else{
				$Where=null;
			}
		}elseif($Where){
			$Where=func_get_args();
			array_shift($Where);
		}

		if(count($Where)>=3){
			$limit=array_pop($Where);// Limit
			$order=array_pop($Where);// Order
		}else{
			$limit='';// Limit
			$order='';// Order
		}

		if($limit===null){
			$limit='';
		}
		if($order===null){
			$order ='';
		}

		$this->getDb()->update($this->getFullTableName(),$Row,$Where,$order,$limit,self::$_arrMeta[$this->_sCacheId]['field']);
		return $this->_oConnect->getAffectedRows();
	}

	public function tableSelect(){
		if(!$this->_bInited){
			$this->init();
		}
		$oSelect=$this->_oDb->select($this);
		return $oSelect;
	}

	public function getDb(){
		if(!$this->_bInited){
			$this->init();
		}

		return $this->_oDb;
	}

	public function setDb($oDb){
		if(!$this->_bInited){
			$this->init();
		}
		$this->_oDb=$oDb;
	}

	public function getConnect(){
		if(!$this->_bInited){
			$this->init();
		}
		return $this->_oConnect;
	}

	public function setConnect(DbConnect $oConnect){
		static $oDbObjParseDsn=null;

		$this->_oConnect=$oConnect;
		if(empty($this->_sSchema)){
			$this->_sSchema=$oConnect->getSchema();
		}

		if(empty($this->_sPrefix)){
			$this->_sPrefix=$oConnect->getTablePrefix();
		}
	}

	public function getFullTableName(){
		if(!$this->_bInited){
			$this->setupConnect_();
		}
		return (!empty($this->_sSchema)?"`{$this->_sSchema}`.":'')."`{$this->_sPrefix}{$this->_sName}`";
	}

	public function columns(){
		if(!$this->_bInited){
			$this->init();
		}
		return self::$_arrMeta[$this->_sCacheId];
	}

	public function init(){
		if($this->_bInited){
			return;
		}

		$this->_bInited=true;
		$this->setupConnect_();
		$this->setupTableName_();
		$this->setupMeta_();
	}

	protected function setupConnect_(){
		if(!is_null($this->_oConnect)){
			return;
		}

		$oDb=Db::RUN($this->_arrConfig);
		$this->setConnect($oDb->getConnect());
		$this->setDb($oDb);
	}

	protected function setupTableName_(){
		if(empty($this->_sName)){
			$this->_sName=substr($this->_sName,0,-2);
		}elseif(strpos($this->_sName,'.')){
			list($this->_sChema,$this->_sName)=explode('.',$this->_sName);
		}
	}

	protected function setupMeta_(){
		$sTableName=$this->getFullTableName();
		$this->_sCacheId=trim($sTableName,'`');
		if(isset(self::$_arrMeta[$this->_sCacheId])){
			return;
		}

		$bCached=$GLOBALS['_commonConfig_']['DB_META_CACHED'];
		if($bCached){
			$arrData=Q::cache($this->_sCacheId.'$','',
				array('encoding_filename'=>false,
					'cache_path'=>(defined('DB_META_CACHED_PATH')?DB_META_CACHED_PATH:APP_RUNTIME_PATH.'/Data/DbMeta')
				)
			);

			if($arrData!==false){
				self::$_arrMeta[$this->_sCacheId]=$arrData;
				return;
			}
		}

		$arrFields=array(
			'pk_'=>array(),
			'auto_'=>false,
			'field'=>array(),
			'default'=>array(),
		);
		$arrMeta=$this->_oConnect->metaColumns($sTableName);
		foreach($arrMeta as $arrValue){
			$arrFields['field'][]=$arrValue['name'];
			if($arrValue['auto_incr']){
				$arrFields['auto_']=true;
			}
			if($arrValue['pk']){
				$arrFields['pk_'][]=$arrValue['name'];
			}
			if($arrValue['default']!==null){
				$arrFields['default'][$arrValue['name']]=$arrValue['default'];
			}
		}

		self::$_arrMeta[$this->_sCacheId]=$arrFields;
		if($bCached){
			Q::cache($this->_sCacheId.'$',$arrFields,
				array('encoding_filename'=>false,
					'cache_path'=>(defined('DB_META_CACHED_PATH')?DB_META_CACHED_PATH:APP_RUNTIME_PATH.'/Data/DbMeta')
				)
			);
		}
	}

}
