<?php
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   DbCond类封装复杂的查询条件（Learn QP!）($$)*/

!defined('Q_PATH') && exit;

class DbCond{

	const BEGIN_GROUP='(';
	const END_GROUP=')';
	protected $_arrOptions=array();

	public function __construct(){
		$arrArgs=func_get_args();
		if(!empty($arrArgs)){
			$this->_arrOptions[]=array($arrArgs,true);
		}
	}

	public static function create(){
		$oCond=new DbCond();
		$arrArgs=func_get_args();
		if(!empty($arrArgs)){
			$oCond->appendDirect($arrArgs);
		}

		return $oCond;
	}

	public static function createByArgs($Cond,array $arrCondArgs=null,$bBool=true){
		if(!is_array($arrCondArgs)){
			$arrCondArgs=array();
		}

		$oCond=new DbCond();
		if(!empty($Cond)){
			array_unshift($arrCondArgs,$Cond);
			$oCond->appendDirect($arrCondArgs,$bBool);
		}

		return $oCond;
	}

	public function appendDirect(array $arrArgs,$bBool=true){
		$this->_arrOptions[]=array($arrArgs,$bBool);
		return $this;
	}

	public function andCond(){
		$this->_arrOptions[]=array(func_get_args(),true);
		return $this;
	}

	public function orCond(){
		$this->_arrOptions[]=array(func_get_args(),false);
		return $this;
	}

	public function andGroup(){
		$this->_arrOptions[]=array(self::BEGIN_GROUP,true);
		$this->_arrOptions[]=array(func_get_args(),true);
		return $this;
	}

	public function orGroup(){
		$this->_arrOptions[]=array(self::BEGIN_GROUP,false);
		$this->_arrOptions[]=array(func_get_args(),false);
		return $this;
	}

	public function endGroup(){
		$this->_arrOptions[]=array(self::END_GROUP,null);
		return $this;
	}

	public function makeSql($oConnect,$sTableName=null,array $arrFieldsMapping=null,$hCallback=null){
		if(empty($this->_arrOptions)){
			return '';
		}

		if(is_null($arrFieldsMapping)){
			$arrFieldsMapping=array();
		}

		$sSql='';
		$bSkipCondLink=true;
		$bBool=true;
		$arrBigSql=array();

		// $this->_arrOptions 的存储结构是一个二维数组
		// 数组的每一项如下：
		// - 要处理的查询条件
		// - 该查询条件与其他查询条件是 AND 还是 OR 关系
		foreach($this->_arrOptions as $arrOption){
			list($arrArgs,$bBoolItem)=$arrOption;
			if(empty($arrArgs)){
				$bSkipCondLink=true;// 如果查询条件为空，忽略该项
				continue;
			}

			if(!is_null($bBoolItem)){
				$bBool=$bBoolItem;// 如果该项查询条件没有指定 AND/OR 关系，则不改变当前的 AND/OR 关系状态
			}

			if(!is_array($arrArgs)){
				if($arrArgs==self::BEGIN_GROUP){// 查询如果不是一个数组，则判断是否是特殊占位符
					if(!$bSkipCondLink){
						$sSql.=($bBool)?' AND ':' OR ';
					}
					$sSql.=self::BEGIN_GROUP;
					$bSkipCondLink=true;
				}else{
					$sSql.=self::END_GROUP;
				}
				continue;
			}else{
				if($bSkipCondLink){
					$bSkipCondLink=false;
				}else{
					$sSql.=($bBool)?' AND ':' OR ';// 如果 $bSkipCondLink 为 false，表示前一个项目是一个查询条件&因此需要用 AND/OR 来连接多个查询条件。
				}
			}

			$cond=reset($arrArgs);// 剥离出查询条件，$arrArgs 剩下的内容是查询参数
			array_shift($arrArgs);

			// 如果是这样的数组 array(0=>array('hello','world','ye'))，那么取第一个元素为数组
			if(isset($arrArgs[0]) && is_array($arrArgs[0]) && !isset($arrArgs[1])){
				$arrArgs=array_shift($arrArgs);
			}

			if($cond instanceof DbCond || $cond instanceof DbExpression){
				$sOption=$cond->makeSql($oConnect,$sTableName,$arrFieldsMapping,$hCallback);// 使用 DbCond 作为查询条件
			}elseif(is_array($cond)){
				$arrOptions=array();// 使用数组作为查询条件
				foreach($cond as $field=>$value){
					if(!is_string($field)){
						if(is_null($value)){// 如果键名不是字符串，说明键值是一个查询条件
							continue;
						}
						if($value instanceof DbCond || $cond instanceof DbExpression){
							$value=$value->makeSql($oConnect,$sTableName,$arrFieldsMapping,$hCallback);// 查询条件如果是 DbCond 或 DbExpr，则格式化为字符串
						}
						$value=$oConnect->qualifySql($value,$sTableName,$arrFieldsMapping,$hCallback);
						$style=(strpos($value,'?')===false)?Db::PARAM_CL_NAMED:Db::PARAM_QM;
						$arrOptions[]=$oConnect->qualifyInto($value,$arrArgs,$style);
					}else{
						$arrOptions[]=$oConnect->qualifyWhere(array($field=>$value),$sTableName,$arrFieldsMapping,$hCallback);// 转义查询值
					}
				}

				foreach($arrOptions as $sK=>$sV){
					if($sV=='OR'){
						$bBool=false;
						unset($arrOptions[$sK]);
					}
					if($sV=='AND'){
						unset($arrOptions[$sK]);
					}
				}
				$sAndOr=$bBool?' AND ':' OR ';// 用 AND or OR 连接多个查询条件
				$sOption=implode(' '.$sAndOr.' ',$arrOptions);
			}else{
				$sOption=$oConnect->qualifySql($cond,$sTableName,$arrFieldsMapping,$hCallback);// 使用字符串做查询条件
				$style=(strpos($sOption,'?')===false)?Db::PARAM_CL_NAMED:Db::PARAM_QM;
				$sOption=$oConnect->qualifyInto($sOption,$arrArgs,$style);
			}

			if((empty($sOption) || $sOption=='()')){
				$bSkipCondLink=true;
				continue;
			}

			$arrBigSql[]=$sOption;
			unset($sOption);
		}

		$arrBigSql=array_unique($arrBigSql);// 过滤空值和重复值
		$arrBigSql=Q::normalize($arrBigSql);
		if(empty($arrBigSql)){
			return '';
		}

		return implode(($sSql!='' && !$bSkipCondLink)?$sSql:' ',$arrBigSql);
	}

}
