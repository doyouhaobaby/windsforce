<?php
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   SQL Select 子句（Learn QP!）($$)*/

!defined('Q_PATH') && exit;

class DbSelect{

	protected static $_arrOptionsInit=array(
		'distinct'=>false,
		'columns'=>array(),
		'aggregate'=>array(),
		'union'=>array(),
		'from'=>array(),
		'where'=>null,
		'group'=>array(),
		'having'=>null,
		'order'=>array(),
		'limitcount'=>1,
		'limitoffset'=>null,
		'limitquery'=>false,
		'forupdate'=>false
	);
	protected static $_arrAggregateTypes=array(
		'COUNT'=>'COUNT',
		'MAX'=>'MAX',
		'MIN'=>'MIN',
		'AVG'=>'AVG',
		'SUM'=>'SUM'
	);
	protected static $_arrJoinTypes=array(
		'inner join'=>'inner join',
		'left join'=>'left join',
		'right join'=>'right join',
		'full join'=>'full join',
		'cross join'=>'cross join',
		'natural join'=>'natural join'
	);
	protected static $_arrUnionTypes=array(
		'UNION'=>'UNION',
		'UNION ALL'=>'UNION ALL'
	);
	protected static $_arrQueryParamsInit=array(
		'as_array'=>true,
		'as_coll'=>false,
		'recursion'=>1,
		'paged_query'=>false
	);
	protected $_arrOptions=array();
	protected $_arrQueryParams;
	protected $_currentTable;
	protected $_arrJoinedTables=array();
	protected $_arrColumnsMapping=array();
	private static $_nQueryId=0;
	protected $_oMeta;
	protected $_bForUpdate=false;
	private $_oConnect;
	private $_oSubSqlGroup=null;
	private $_sSubSqlGroup=null;
	private $_oSubSqlReturnColumnList=null;
	private $_sSubSqlReturnColumnList=null;
	private $_oSubSqlOn=null;
	private $_sSubSqlOn=null;
	protected $_sLastSql='';

	public function __construct(DbConnect $oConnect=null){
		$this->_oConnect=$oConnect;// 初始化数据

		self::$_nQueryId ++;
		$this->_arrOptions=self::$_arrOptionsInit;
		$this->_arrQueryParams=self::$_arrQueryParamsInit;
	}

	public function setConnect(DbConnect $oConnect){
		$this->_oConnect=$oConnect;
		return $this;
	}

	public function getConnect(){
		return $this->_oConnect;
	}

	public function getLastSql(){
		return $this->_sLastSql;
	}

	public function getCounts($Field='*',$sAlias='row_count'){
		$arrRow=$this->count($Field,$sAlias)->query();
		return $arrRow[$sAlias];
	}

	public function getAvg($Field,$sAlias='avg_value'){
		$arrRow=$this->avg($Field,$sAlias)->query();
		return $arrRow[$sAlias];
	}

	public function getMax($Field,$sAlias='max_value'){
		$arrRow=$this->max($Field,$sAlias)->query();
		return $arrRow[$sAlias];
	}

	public function getMin($Field,$sAlias='min_value'){
		$arrRow=$this->min($Field,$sAlias)->query();
		return $arrRow[$sAlias];
	}

	public function getSum($Field,$sAlias='sum_value'){
		$arrRow=$this->sum($Field,$sAlias)->query();
		return $arrRow[$sAlias];
	}

	public function get($nNum=null,$IncludedLinks=null){
		if(!is_null($nNum)){
			return $this->top($nNum)->query($IncludedLinks);
		}else{
			return $this->query($IncludedLinks);
		}
	}

	public function getById($Id,$IncludedLinks=null){
		if($this->_oMeta->_nIdNameCount!=1){
			Q::E(Q::L('getById 方法只适用于单一主键模型' ,'__QEEPHP__@Q'));
		}

		return $this->where(array(reset($this->_oMeta->_sIdName)=>$Id))->getOne($IncludedLinks);
	}

	public function getOne($IncludedLinks=null){
		return $this->one()->query($IncludedLinks);
	}

	public function getAll($IncludedLinks=null){
		if($this->_arrOptions['limitquery']){
			return $this->query($IncludedLinks);
		}else{
			return $this->all()->query($IncludedLinks);
		}
	}

	public function getColumn($sColumn,$sSepa='-'){
		if(strpos($sColumn,',') || $sSepa===true){// 多个字段
			$this->all();
		}

		$this->setColumns($sColumn);
		$hHandle=$this->getQueryHandle();
		if($hHandle===false){
			return false;
		}

		return $hHandle->getColumn($sColumn,$sSepa);
	}

	public function query($arrIncludedLinks=null){
		$this->_arrQueryParams['non_lazy_query']=Q::normalize($arrIncludedLinks);

		if($this->_arrQueryParams['as_array']){
			return $this->queryArray_(true);
		}else{
			return $this->queryObjects_();
		}
	}

	public function getQueryHandle(){
		$sSql=$this->makeSql();// 构造查询 SQL，并取得查询中用到的关联

		$nOffset=$this->_arrOptions['limitoffset'];
		$nCount=$this->_arrOptions['limitcount'];
		if(is_null($nOffset)&& is_null($nCount)){
			$result=$this->_oConnect->exec($sSql);
			return $result;
		}else{
			$result=$this->_oConnect->selectLimit($sSql,$nOffset,$nCount);
			return $result;
		}
	}

	public function __call($sMethod,array $arrArgs){
		if(strncasecmp($sMethod,'get',3)===0){
			$sMethod=substr($sMethod,3);

			if(strpos(strtolower($sMethod),'start')!==false){//support get10start3 etc.
				$arrValue=explode('start',strtolower($sMethod));
				$nNum=intval(array_shift($arrValue));
				$nOffset=intval(array_shift($arrValue));
				return $this->limit($nOffset - 1,$nNum);
			}elseif(strncasecmp($sMethod,'By',2)===0){// support getByName getByNameAndSex etc.
				$sMethod=substr($sMethod,2);
				$arrKeys=explode('And',$sMethod);
				if(count($arrKeys)!=count($arrArgs)){
					Q::E(Q::L('参数数量不对应','__QEEPHP__@Q'));
				}
				return $this->where(array_change_key_case(array_combine($arrKeys,$arrArgs),CASE_LOWER))->getOne();
			}elseif(strncasecmp($sMethod,'AllBy',5)===0){// support getAllByNameAndSex etc.
				$sMethod=substr($sMethod,5);
				$arrKeys=explode('And',$sMethod);
				if(count($arrKeys)!=count($arrArgs)){
					Q::E(Q::L('参数数量不对应','__QEEPHP__@Q'));
				}
				return $this->where(array_change_key_case(array_combine($arrKeys,$arrArgs),CASE_LOWER))->getAll();
			}
			return $this->top(intval(substr($sMethod,3)));
		}elseif(method_exists($this->_oMeta->_sClassName,'find_'.$sMethod)){// ArticleModel::F()->hot()->getOne()	,static method `find_on_hot` must define in ArticleModel
			array_unshift($arrArgs,$this);
			return call_user_func_array(array($this->_oMeta->_sClassName,'find_'.$sMethod),$arrArgs);
		}

		Q::E(Q::L('DbSelect 没有实现魔法方法 %s.','__QEEPHP__@Q',null,$sMethod));
	}

	protected function queryArray_($bCleanUp=true,$hHandle=null){
		if($hHandle===null){
			$hHandle=$this->getQueryHandle();
			if($hHandle===false){
				return false;
			}
		}

		// 查询
		if($this->_arrOptions['limitcount']==1){
			$arrRow=$hHandle->fetch();
		}else{
			$arrRowset=$hHandle->getAllRows();
		}

		if(count($this->_arrOptions['aggregate'])&& isset($arrRowset)){
			if(empty($this->_arrOptions['group'])){
				return reset($arrRowset);
			}else{
				return $arrRowset;
			}
		}

		if(isset($arrRow)){
			return $arrRow;
		}else{
			if(!isset($arrRowset)){
				$arrRowset=array();
			}
			return $arrRowset;
		}
	}

	protected function queryObjects_(){
		// 执行查询，获得一个查询句柄
		$hHandle=$this->getQueryHandle();
		if($hHandle===false){
			return false;
		}

		// 模型类不存在，直接以数组结果返回
		$sClassName=$this->_oMeta->_sClassName;
		if(!Q::classExists($sClassName)){
			return $this->queryArray_(true,$hHandle);
		}

		$arrRowset=array();
		while(($arrRow=$hHandle->fetch())!==false){
			$oObj=new $sClassName($arrRow,'',true);
			$arrRowset[]=$oObj;
		}

		if(empty($arrRowset)){
			if(!$this->_arrOptions['limitquery']){// 没有查询到数据时，返回 Null 对象或空集合
				return $this->_oMeta->newObj();
			}else{
				if($this->_arrQueryParams['as_coll']){
					return new Coll($this->_oMeta->_sClassName);
				}else{
					return array();
				}
			}
		}

		if(!$this->_arrOptions['limitquery']){
			return reset($arrRowset);// 创建一个单独的对象
		}else{
			if($this->_arrQueryParams['as_coll']){
				return Coll::createFromArray($arrRowset,$this->_oMeta->_sClassName);
			}else{
				return $arrRowset;
			}
		}
	}

	public function distinct($bFlag=true){
		$this->_arrOptions['distinct']=(bool)$bFlag;
		return $this;
	}

	public function from($Table,$Cols='*'){
		$this->_currentTable=$Table;
		return $this->join_('inner join',$Table,$Cols);
	}

	public function columns($Cols='*',$Table=null){
		if(is_null($Table)){
			$Table=$this->getCurrentTableName_();
		}

		$this->addCols_($Table,$Cols);
		return $this;
	}

	public function setColumns($Cols='*',$Table=null){
		if(is_null($Table)){
			$Table=$this->getCurrentTableName_();
		}

		$this->_arrOptions['columns']=array();
		$this->addCols_($Table,$Cols);
		return $this;
	 }

	public function where($Cond /* args */){
		$arrArgs=func_get_args();
		array_shift($arrArgs);
		return $this->addConditions_($Cond,$arrArgs,'where',true);
	}

	public function orWhere($Cond /* args */){
		$arrArgs=func_get_args();
		array_shift($arrArgs);
		return $this->addConditions_($Cond,$arrArgs,'where',false);
	}

	public function join($Table,$Cols='*',$Cond /* args */){
		$arrArgs=func_get_args();
		return $this->join_('inner join',$Table,$Cols,$Cond,array_slice($arrArgs,3));
	}

	public function joinInner($Table,$Cols='*',$Cond){
		$arrArgs=func_get_args();
		return $this->join_('inner join',$Table,$Cols,$Cond,array_slice($arrArgs,3));
	}

	public function joinLeft($Table,$Cols='*',$Cond){
		$arrArgs=func_get_args();
		return $this->join_('left join',$Table,$Cols,$Cond,array_slice($arrArgs,3));
	}

	public function joinRight($Table,$Cols='*',$Cond){
		$arrArgs=func_get_args();
		return $this->join_('right join',$Table,$Cols,$Cond,array_slice($arrArgs,3));
	}

	public function joinFull($Table,$Cols='*',$Cond){
		$arrArgs=func_get_args();
		return $this->join_('full join',$Table,$Cols,$Cond,array_slice($arrArgs,3));
	}

	public function joinCross($Table,$Cols='*'){
		return $this->join_('cross join',$Table,$Cols);
	}

	public function joinNatural($Table,$Cols='*'){
		return $this->join_('natural join',$Table,$Cols);
	}

	public function union($Select=array(),$sType='UNION'){
		if(! is_array($Select)){
			$Select=array($Select);
		}

		if(!isset(self::$_arrUnionTypes[$sType])){
			Q::E(Q::L('无效的 UNION 类型 %s','__QEEPHP__@Q',null,$sType));
		}

		foreach($Select as $Target){
			$this->_arrOptions['union'][]=array($Target,$sType);
		}

		return $this;
	}

	public function group($Expr){
		if(!is_array($Expr)){// 表达式
			$Expr=array($Expr);
		}

		foreach($Expr as $Part){
			if($Part instanceof DbExpression){
				$Part=$Part->makeSql($this->_oConnect,$this->getCurrentTableName_(),$this->_arrColumnsMapping);
			}else{
				$Part=$this->_oConnect->qualifySql($Part,$this->getCurrentTableName_(),$this->_arrColumnsMapping);
			}
			$this->_arrOptions['group'][]=$Part;
		}

		return $this;
	}

	public function having($Cond /* args */){
		$arrArgs=func_get_args();
		array_shift($arrArgs);
		return $this->addConditions_($Cond,$arrArgs,'having',true);
	}

	public function orHaving($Cond){
		$arrArgs=func_get_args();
		array_shift($arrArgs);
		return $this->addConditions_($Cond,$arrArgs,'having',false);
	}

	public function order($Expr){
		if(!is_array($Expr)){// 非数组
			$Expr=array($Expr);
		}

		$arrM=null;
		foreach($Expr as $Val){
			if($Val instanceof DbExpression){
				$Val=$Val->makeSql($this->_oConnect,$this->getCurrentTableName_(),$this->_arrColumnsMapping);
				if(preg_match('/(.*\W)('.'ASC'.'|'.'DESC'.')\b/si',$Val,$arrM)){
					$Val=trim($arrM[1]);
					$sDir=$arrM[2];
				}else{
					$sDir='ASC';
				}
				$this->_arrOptions['order'][]=$Val.' '.$sDir;
			}else{
				$arrCols=explode(',',$Val);
				foreach($arrCols as $Val){
					$Val=trim($Val);
					if(empty($Val)){
						continue;
					}

					$sCurrentTableName=$this->getCurrentTableName_();
					$sDir='ASC';
					$arrM=null;

					if(preg_match('/(.*\W)('.'ASC'.'|'.'DESC'.')\b/si',$Val,$arrM)){
						$Val=trim($arrM[1]);
						$sDir=$arrM[2];
					}

					if(!preg_match('/\(.*\)/',$Val)){
						if(preg_match('/(.+)\.(.+)/',$Val,$arrM)){
							$sCurrentTableName=$arrM[1];
							$Val=$arrM[2];
						}

						if(isset($this->_arrColumnsMapping[$Val])){
							$Val=$this->_arrColumnsMapping[$Val];
						}
						$Val=$this->_oConnect->qualifyId("{$sCurrentTableName}.{$Val}");
					}
					$this->_arrOptions['order'][]=$Val.' '.$sDir;
				}
			}
		}

		return $this;
	}

	public function one(){
		$this->_arrOptions['limitcount']=1;
		$this->_arrOptions['limitoffset']=null;
		$this->_arrOptions['limitquery']=false;
		return $this;
	}

	public function all(){
		$this->_arrOptions['limitcount']=null;
		$this->_arrOptions['limitoffset']=null;
		$this->_arrOptions['limitquery']=true;
		return $this;
	}

	public function limit($nOffset=0,$nCount=30){
		$this->_arrOptions['limitcount']=abs(intval($nCount));
		$this->_arrOptions['limitoffset']=abs(intval($nOffset));
		$this->_arrOptions['limitquery']=true;
		return $this;
	}

	public function top($nCount=30){
		return $this->limit(0,$nCount);
	}

	public function forUpdate($bFlag=true){
		$this->_bForUpdate=(bool)$bFlag;
		return $this;
	}

	public function count($Field='*',$sAlias='row_count'){
		return $this->addAggregate_('COUNT',$Field,$sAlias);
	}

	public function avg($Field,$sAlias='avg_value'){
		return $this->addAggregate_('AVG',$Field,$sAlias);
	}

	public function max($Field,$sAlias='max_value'){
		return $this->addAggregate_('MAX',$Field,$sAlias);
	}

	public function min($Field,$sAlias='min_value'){
		return $this->addAggregate_('MIN',$Field,$sAlias);
	}

	public function sum($Field,$sAlias='sum_value'){
		return $this->addAggregate_('SUM',$Field,$sAlias);
	}

	public function asObj($sClassName){
		$this->_oMeta=ModelMeta::instance($sClassName);
		$this->_arrQueryParams['as_array']=false;
		return $this;
	}

	public function asArray(){
		$this->_oMeta=null;
		$this->_arrQueryParams['as_array']=true;
		return $this;
	}

	public function asColl($bAsColl=true){
		$this->_arrQueryParams['as_coll']=$bAsColl;
		return $this;
	}

	public function columnMapping($Name,$sMappingTo=NULL){
		if(is_array($Name)){
			$this->_arrColumnsMapping=array_merge($this->_arrColumnsMapping,$Name);
		}else{
			if(empty($sMappingTo)){
				unset($this->_arrColumnsMapping[$Name]);
			}else{
				$this->_arrColumnsMapping[$Name]=$sMappingTo;
			}
		}

		return $this;
	}

	public function getOption($sOption){
		$sOption=strtolower($sOption);
		if(!array_key_exists($sOption,$this->_arrOptions)){
			Q::E(Q::L('无效的部分名称 %s' ,'__QEEPHP__@Q',null,$sOption));
		}

		return $this->_arrOptions[$sOption];
	}

	public function reset($sOption=null){
		if($sOption==null){// 设置整个配置
			$this->_arrOptions=self::$_arrOptionsInit;
			$this->_arrQueryParams=self::$_arrQueryParamsInit;
		}elseif(array_key_exists($sOption,self::$_arrOptionsInit)){
			$this->_arrOptions[$sOption]=self::$_arrOptionsInit[$sOption];
		}

		return $this;
	}

	public function makeSql(){
		$arrSql=array(
			'SELECT'
		);

		foreach(array_keys(self::$_arrOptionsInit)as $sOption){
			if($sOption=='from'){
				$arrSql['from']='';
			}else{
				$sMethod='parse'.ucfirst($sOption).'_';
				if(method_exists($this,$sMethod)){
					$arrSql[$sOption]=$this->$sMethod();
				}
			}
		}

		$arrSql['from']=$this->parseFrom_();
		foreach($arrSql as $nOffset=>$sOption){// 删除空元素
			if(trim($sOption)==''){
				unset($arrSql[$nOffset]);
			}
		}

		$this->_sLastSql=implode(' ',$arrSql);
		return $this->_sLastSql;
	}

	protected function parseDistinct_(){
		if($this->_arrOptions['distinct']){
			return 'DISTINCT';
		}else{
			return '';
		}
	}

	protected function parseColumns_(){
		if(empty($this->_arrOptions['columns'])){
			return '';
		}

		if($this->_arrQueryParams['paged_query']){
			return 'COUNT(*)';
		}

		$arrColumns=array();// $this->_arrOptions['columns'] 每个元素的格式
		foreach($this->_arrOptions['columns'] as $arrEntry){
			list($sTableName,$Col,$sAlias)=$arrEntry;// array($currentTableName,$Col,$sAlias | null)
			if($Col instanceof DbExpression){// $Col 是一个字段名或者一个 DbExpression 对象
				$arrColumns[]=$Col->makeSql($this->_oConnect,$sTableName,$this->_arrColumnsMapping);
			}else{
				if(isset($this->_arrColumnsMapping[$Col])){
					$Col=$this->_arrColumnsMapping[$Col];
				}
				$Col=$this->_oConnect->qualifyId("{$sTableName}.{$Col}");

				if($Col!='*' && $sAlias){
					$arrColumns[]=$this->_oConnect->qualifyId($Col,$sAlias,'AS');
				}else{
					$arrColumns[]=$Col;
				}
			}
		}

		return implode(',',$arrColumns);
	}

	protected function parseAggregate_(){
		$arrColumns=array();

		foreach($this->_arrOptions['aggregate'] as $arrAggregate){
			list(,$sField,$sAlias)=$arrAggregate;
			if($sAlias){
				$arrColumns[]=$sField.' AS '.$sAlias;
			}else{
				$arrColumns[]=$sField;
			}
		}

		return(empty($arrColumns))?'':implode(',',$arrColumns);
	}

	protected function parseFrom_(){
		$arrFrom=array();

		foreach($this->_arrOptions['from'] as $sAlias=>$arrTable){
			$sTmp='';
			if(!empty($arrFrom)){// 如果不是第一个 FROM，则添加 JOIN
				$sTmp.=' '.strtoupper($arrTable['join_type']).' ';
			}

			if($sAlias==$arrTable['table_name']){
				$sTmp.=$this->_oConnect->qualifyId("{$arrTable['schema']}.{$arrTable['table_name']}");
			}else{
				$sTmp.=$this->_oConnect->qualifyId("{$arrTable['schema']}.{$arrTable['table_name']}",$sAlias);
			}

			if(!empty($arrFrom) && !empty($arrTable['join_cond'])){// 添加 JOIN 查询条件
				$sTmp.="\n ON ".$arrTable['join_cond'];
			}
			$arrFrom[]=$sTmp;
		}

		if(!empty($arrFrom)){
			return "\n FROM ".implode("\n",$arrFrom);
		}else{
			return '';
		}
	}

	protected function parseUnion_(){
		$sSql='';

		if($this->_arrOptions['union']){
			$nOptions=count($this->_arrOptions['union']);
			foreach($this->_arrOptions['union'] as $nCnt=>$arrUnion){
				list($oTarget,$sType)=$arrUnion;
				if($oTarget instanceof DbRecordSet){
					$oTarget=$oTarget->makeSql();
				}
				$sSql.=$oTarget;
				if($nCnt<$nOptions-1){
					$sSql.=' '.$sType.' ';
				}
			}
		}

		return $sSql;
	}

	protected function parseWhere_(){
		$sSql='';

		if(!empty($this->_arrOptions['from']) && !is_null($this->_arrOptions['where'])){
			$sWhere=$this->_arrOptions['where']->makeSql($this->_oConnect,$this->getCurrentTableName_(),null,array($this,'parseTableName_'));
			if(!empty($sWhere)){
				$sSql.="\n WHERE ".$sWhere;
			}
		}

		return $sSql;
	}

	protected function parseGroup_(){
		if(!empty($this->_arrOptions['from']) && !empty($this->_arrOptions['group'])){
			return "\n GROUP BY ".implode(",\n\t",$this->_arrOptions['group']);
		}

		return '';
	}

	protected function parseHaving_(){
		if(!empty($this->_arrOptions['from']) && !empty($this->_arrOptions['having'])){
			return "\n HAVING ".implode(",\n\t",$this->_arrOptions['having']);
		}

		return '';
	}

	protected function parseOrder_(){
		if(!empty($this->_arrOptions['order'])){
			return "\n ORDER BY ".implode(',',array_unique($this->_arrOptions['order']));
		}

		return '';
	}

	protected function parseForUpdate_(){
		if($this->_arrOptions['forupdate']){
			return "\n FOR UPDATE";
		}

		return '';
	}

	protected function join_($sJoinType,$Name,$Cols,$Cond=null,$arrCondArgs=null){
		if(!isset(self::$_arrJoinTypes[$sJoinType])){
			Q::E(Q::L('无效的 JOIN 类型 %s','__QEEPHP__@Q',null,$sJoinType));
		}

		// 不能在使用 UNION 查询的同时使用 JOIN 查询.
		if(count($this->_arrOptions['union'])){
			Q::E(Q::L('不能在使用 UNION 查询的同时使用 JOIN 查询','__QEEPHP__@Q'));
		}

		// 根据 $Name 的不同类型确定数据表名称、别名
		$arrM=array();

		if(empty($Name)){// 没有指定表，获取默认表
			$Table=$this->getCurrentTableName_();
			$sAlias='';
		}elseif(is_array($Name)){// $Name为数组配置
			foreach($Name as $sAlias=>$Table){
				if(!is_string($sAlias)){
					$sAlias='';
				}
				break;
			}
		}elseif($Name instanceof DbTableEnter){// 如果为DbTableEnter的实例
			$Table=$Name;
			$sAlias=$Name->_sAlias;
		}elseif(preg_match('/^(.+)\s+AS\s+(.+)$/i',$Name,$arrM)){// 字符串指定别名
			$Table=$arrM[1];
			$sAlias=$arrM[2];
		}else{
			$Table=$Name;
			$sAlias='';
		}

		// 确定 table_name 和 schema
		if($Table instanceof DbTableEnter){
			$sSchema=$Table->_sSchema;
			$sTableName=$Table->_sPrefix.$Table->_sName;
		}else{
			$arrM=explode('.',$Table);
			if(isset($arrM[1])){
				$sSchema=$arrM[0];
				$sTableName=$arrM[1];
			}else{
				$sSchema=null;
				$sTableName=$Table;
			}
		}

		$sAlias=$this->uniqueAlias_(empty($sAlias)?$sTableName:$sAlias);// 获得一个唯一的别名
		if(!($Cond instanceof DbCond)){// 处理查询条件
			$Cond=DbCond::createByArgs($Cond,$arrCondArgs);
		}

		$sWhereSql=$Cond->makeSql($this->_oConnect,$sAlias,$this->_arrColumnsMapping);
		$this->_arrOptions['from'][$sAlias]=array(// 添加一个要查询的数据表
			'join_type'=>$sJoinType,'table_name'=>$sTableName,'schema'=>$sSchema,'join_cond'=>$sWhereSql
		);
		$this->addCols_($sAlias,$Cols);// 添加查询字段

		return $this;
	}

	protected function addCols_($sTableName,$Cols){
		$Cols=Q::normalize($Cols);

		if(is_null($sTableName)){
			$sTableName='';
		}

		$arrM=null;
		if(is_object($Cols)&&($Cols instanceof DbExpression)){// Cols为对象
			$this->_arrOptions['columns'][]=array($sTableName,$Cols,null);
		}else{
			// 没有字段则退出
			if(empty($Cols)){
				return;
			}
			
			foreach($Cols as $sAlias=>$Col){
				if(is_string($Col)){
					foreach(Q::normalize($Col)as $sCol){// 将包含多个字段的字符串打散
						$currentTableName=$sTableName;
						if(preg_match('/^(.+)\s+'.'AS'.'\s+(.+)$/i',$sCol,$arrM)){// 检查是不是 "字段名 AS 别名"这样的形式
							$sCol=$arrM[1];
							$sAlias=$arrM[2];
						}

						if(preg_match('/(.+)\.(.+)/',$sCol,$arrM)){// 检查字段名是否包含表名称
							$currentTableName=$arrM[1];
							$sCol=$arrM[2];
						}

						if(isset($this->_arrColumnsMapping[$sCol])){
							$sCol=$this->_arrColumnsMapping[$sCol];
						}

						$this->_arrOptions['columns'][]=array(
							$currentTableName,$sCol,is_string($sAlias)?$sAlias:null
						);
					}
				}else{
					$this->_arrOptions['columns'][]=array($sTableName,$Col,is_string($sAlias)?$sAlias:null);
				}
			}
		}
	}

	protected function addConditions_($Cond,array $arrArgs,$sPartType,$bBool){
		// DbCond对象
		if(!($Cond instanceof DbCond)){
			if(empty($Cond)){
				return $this;
			}
			$Cond=DbCond::createByArgs($Cond,$arrArgs,$bBool);
		}

		// 空，直接创建DbCond
		if(is_null($this->_arrOptions[$sPartType])){
			$this->_arrOptions[$sPartType]=new DbCond();
		}

		if($bBool){// and类型
			$this->_arrOptions[$sPartType]->andCond($Cond);
		}else{// or类型
			$this->_arrOptions[$sPartType]->orCond($Cond);
		}

		return $this;
	}

	protected function getCurrentTableName_(){
		if(is_array($this->_currentTable)){// 数组
			while((list($sAlias,)=each($this->_currentTable))!==false){
				$this->_currentTable=$sAlias;
				return $sAlias;
			}
		}elseif(is_object($this->_currentTable)){
			return $this->_currentTable->_sPrefix.$this->_currentTable->_sName;
		}else{
			return $this->_currentTable;
		}
	}

	public function parseTableName_($sTableName){
		if(strpos($sTableName,'.')!==false){// 获取表模式
			list($sSchema,$sTableName)=explode('.',$sTableName);
		}else{
			$sSchema=null;
		}

		return $sTableName;
	}

	protected function addAggregate_($sType,$Field,$sAlias){
		$this->_arrOptions['columns']=array();

		$this->_arrQueryParams['recursion']=0;
		if($Field instanceof DbExpression){
			$Field=$Field->makeSql($this->_oConnect,$this->getCurrentTableName_(),$this->_arrColumnsMapping);
		}else{
			if(isset($this->_arrColumnsMapping[$Field])){
				$Field=$this->_arrColumnsMapping[$Field];
			}

			$Field=$this->_oConnect->qualifySql($Field,$this->getCurrentTableName_(),$this->_arrColumnsMapping);
			$Field="{$sType}($Field)";
		}

		$this->_arrOptions['aggregate'][]=array(
			$sType,$Field,$sAlias
		);

		$this->_arrQueryParams['as_array']=true;

		return $this;
	}

	private function uniqueAlias_($Name){
		if(empty($Name)){
			return '';
		}

		if(is_array($Name)){// 数组，返回最后一个元素
			$sC=end($Name);
		}else{// 字符串
			$nDot=strrpos($Name,'.');
			$sC=($nDot===false)?$Name:substr($Name,$nDot+1);
		}

		for($nI=2; array_key_exists($sC,$this->_arrOptions['from']);++$nI){
			$sC=$Name.'_'.(string)$nI;
		}

		return $sC;
	}

}
