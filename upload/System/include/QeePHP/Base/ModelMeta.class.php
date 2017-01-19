<?php
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   ModelMeta元模式（Learn QP!）($$)*/

!defined('Q_PATH') && exit;

class ModelMeta{

	protected static $_arrMeta=array();
	public $_oTable;
	public $_arrTableMeta;
	public $_arrCheck=array();
	public $_arrAutofill=array();
	public $_bInitClass=false;
	protected static $_arrCheckOptions=array(
		'allow_null'=>false,
		'check_all_rules'=>false
	);
	protected $_ErrorMessage='';

	protected function __construct($sClass,$bInitClass=false){
		$this->_bInitClass=$bInitClass;
		if($bInitClass===false){
			$this->init_($sClass);
		}else{
			$this->initSimple_($sClass);
		}
	}

	static public function instance($sClass,$bInitClass=false){
		$sKeyClass=$bInitClass===true?ucfirst($sClass).'Model':$sClass;

		if (!isset(self::$_arrMeta[$sKeyClass])){
			self::$_arrMeta[$sKeyClass]=new self($sClass,$bInitClass);
		}else{
			self::$_arrMeta[$sKeyClass]->_bInitClass=$bInitClass;
		}

		return self::$_arrMeta[$sKeyClass];
	}

	public function find(){
		return $this->findByArgs(func_get_args());
	}

	public function findByArgs(array $arrArgs=array()){
		if(!empty($arrArgs[0]) && is_string($arrArgs[0]) && strpos($arrArgs[0],'@')===0){
			$this->_oTable->_sAlias=ltrim($arrArgs[0],'@');
			array_shift($arrArgs);
		}else{
			$this->_oTable->_sAlias='';
		}
		
		$oSelect=new DbSelect($this->_oTable->getConnect());
		$oSelect->asColl()->from($this->_oTable);
		if($this->_bInitClass===true){
			$oSelect->asArray();
		}else{
			$oSelect->asObj($this->_sClassName);
		}

		$nC=count($arrArgs);
		if($nC>0){
			if($nC==1 && is_int($arrArgs[0]) && count($this->_arrTableMeta['pk_'])==1){
				$oSelect->where(array(reset($this->_arrTableMeta['pk_'])=>$arrArgs[0]));
			}else{
				call_user_func_array(array($oSelect,'where'),$arrArgs);
			}
		}

		return $oSelect;
	}

	public function insertWhere(){
		$arrArgs=func_get_args();
		call_user_func_array(array($this->_oTable,'insert'),$arrArgs);
	}

	public function updateWhere(){
		$arrArgs=func_get_args();
		call_user_func_array(array($this->_oTable,'update'),$arrArgs);
	}

	public function deleteWhere(){
		$arrArgs=func_get_args();
		call_user_func_array(array($this->_oTable,'delete'),$arrArgs);
	}

	public function newObj(array $Data=null){
		return new $this->_sClassName($Data,'',true);
	}

	private function init_($sClass){
		$this->_sClassName=$sClass;
		$arrRef=(array)call_user_func(array($sClass,'init__'));
		
		$arrTableConfig=!empty($arrRef['table_config'])?(array)$arrRef['table_config']:array();// 设置表数据入口对象
		$this->_oTable=$this->tableByName_($arrRef['table_name'],$arrTableConfig);
		$this->_arrTableMeta=$this->_oTable->columns();

		if(!empty($arrRef['autofill']) && is_array($arrRef['autofill'])){
			$this->_arrAutofill=$arrRef['autofill'];
		}

		// 准备验证规则
		if(empty($arrRef['check']) || ! is_array($arrRef['check'])){
			$arrRef['check']=array();
		}
		$this->_arrCheck=$this->prepareCheckRules_($arrRef['check']);
	}

	private function initSimple_($sClass){
		$this->_sClassName=ucfirst($sClass).'Model';
		$this->_oTable=$this->tableByName_($sClass,array());
		$this->_arrTableMeta=$this->_oTable->columns();
	}

	protected function prepareCheckRules_($arrPolicies,array $arrRef=array(),$bSetPolicy=true){
		$arrCheck=$this->_arrCheck;
		foreach($arrPolicies as $sPropName=>$arrPolicie){
			if(!is_array($arrPolicie)){
				continue;
			}

			$arrCheck[$sPropName]=array('check'=>self::$_arrCheckOptions,'rules'=>array());
			if(isset($this->_arrPropsToField[$sPropName])){
				$sFn=$this->_arrPropsToField[$sPropName];
				if(isset($this->_arrTableMeta[ $sFn ])){
					$arrCheck[$sPropName]['check']['allow_null']=!$this->_arrTableMeta[$sFn]['not_null'];
				}
			}

			if(!$bSetPolicy){
				unset($arrCheck[$sPropName]['check']);
			}

			foreach($arrPolicie as $sOption=>$rule){
				if(isset($arrCheck[$sPropName]['policy'][$sOption])){
					$arrCheck[$sPropName]['policy'][$sOption]=$rule;
				}elseif($sOption==='on_create' || $sOption==='on_update'){// 解析 on_create 和 on_update 规则
					$rule=array($sOption=>(array)$rule);
					$arrRet=$this->prepareCheckRules_($rule,$arrCheck[$sPropName]['rules'],false);
					$arrCheck[$sPropName][$sOption]=$arrRet[$sOption];
				}elseif($sOption==='include'){
					$arrInclude=Q::normalize($rule);
					foreach($arrInclude as $sRuleName){
						if(isset($arrRef[$sRuleName])){
							$arrCheck[$sPropName]['rules'][$sRuleName]=$arrRef[$sRuleName];
						}
					}
				}elseif(is_array($rule)){
					if(is_string($sOption)){
						$sRuleName=$sOption;
					}else{
						$sRuleName=$rule[0];
						if(is_array($sRuleName)){
							$sRuleName=$sRuleName[count($sRuleName)-1];
						}

						if(isset($arrCheck[$sPropName]['rules'][$sRuleName])){
							$sRuleName.='_'.($sOption+1);
						}
					}
					$arrCheck[$sPropName]['rules'][$sRuleName]=$rule;
				}else{
					Q::E(Q::L('指定了无效的验证规则 %s.','__QEEPHP__@Q',null,$sOption.' - '.$rule));
				}

			}
		}

		return $arrCheck;
	}

	protected function tableByName_($sTableName,array $arrTableConfig=array()){
		$arrTableConfig=$this->parseDsn($arrTableConfig,$sTableName);
		$oTable=Q::instance('DbTableEnter',$arrTableConfig);
		return $oTable;
	}

	protected function parseDsn($arrTableConfig,$sTableName,$bByClass=false){
		if (is_array($arrTableConfig) && C::oneImensionArray($arrTableConfig)){
			if($bByClass===false){
				$arrTableConfig['table_name']=$sTableName;
			}
			$arrDsn[]=$arrTableConfig;
		}else{
			if($bByClass===false){
				foreach($arrTableConfig as $nKey=>$arrValue){
					if($bByClass===false){
						$arrTableConfig[$nKey]['table_name']=$sTableName;
					}
				}
			}
			$arrDsn=$arrTableConfig;
		}

		return $arrDsn;
	}

	public function check(array $arrData,$arrProps=null,$sMode='all'){
		if(!is_null($arrProps)){
			$arrProps=Q::normalize($arrProps,',',true);// 这里不过滤空值
		}else{
			$arrProps=$this->_arrPropToField;
		}

		$arrError=array();

		if(empty($sMode)){// 初始化模式
			$sMode='';
		}
		$sMode='on_'.strtolower($sMode);
		foreach($this->_arrCheck as $sProp=>$arrPolicy){
			if(!isset($arrProps[$sProp])){
				continue;
			}
			if(!isset($arrData[$sProp])){
				$arrData[$sProp]=null;
			}
			if(empty($arrPolicy['rules'])){
				continue;
			}
			if(isset($arrPolicy[$sMode])){
				$arrPolicy=$arrPolicy[$sMode];
			}
			if(is_null($arrData[$sProp])){
				if(isset($this->_autofill[$sProp])){// 对于 null 数据，如果指定了自动填充，则跳过对该数据的验证
					continue 2;
				}
				if (isset($arrPolicy['policy'])&& !$arrPolicy['policy']['allow_null']){// allow_null 为 false 时，如果数据为 null，则视为验证失败
					$arrError[$sProp]['not_null']='not null';
				}elseif(empty($arrPolicy['rules'])){
					continue;
				}
			}

			foreach($arrPolicy['rules'] as $sIndex => $arrRule){// 验证规则
				$sExtend='';// 附加规则
				if(array_key_exists('extend',$arrRule)){
					$sExtend=strtolower($arrRule['extend']);
					unset($arrRule['extend']);
				}

				$sCondition='';// 验证条件
				if(array_key_exists('condition',$arrRule)){
	 				$sCondition=strtolower($arrRule['condition']);
	 				unset($arrRule['condition']);
				}

				$sTime='';// 验证时间
				if(array_key_exists('time',$arrRule)){
	 				$sTime=strtolower($arrRule['time']);
	 				unset($arrRule['time']);
				}

				$sCheck=array_shift($arrRule);// 验证规则
				$sMsg=array_pop($arrRule);// 验证消息
				array_unshift($arrRule,$arrData[$sProp]);
				$arrCheckInfo=array('field'=>$sProp,'extend'=>$sExtend,'message'=>$sMsg,'check'=>$sCheck,'rule'=>$arrRule);// 组装成验证信息
				if($sTime!='' and $sTime!='all' and $sMode!='on_'.$sTime){// 如果设置了验证时间，且验证时间不为all，而且验证时间不合模式相匹配，那么路过验证
					continue;
				}

				$bResult=true;
				switch($sCondition){// 判断验证条件
					case 'must':// 必须验证不管表单是否有设置该字段
						$bResult=$this->checkField_($arrData,$arrCheckInfo);
						break;
					case 'notempty':// 值不为空的时候才验证
						if(isset($arrData[$sProp]) and ''!=trim($arrData[$sProp]) and 0!=trim($arrData[$sProp])){
							$bResult=$this->checkField_($arrData,$arrCheckInfo);
						}
						break;
					default:// 默认表单存在该字段就验证
						if(isset($arrData[$sProp])){
							$bResult=$this->checkField_($arrData,$arrCheckInfo);
						}
						break;
				}

				if($bResult===Check::SKIP_OTHERS){
					break;
				}elseif(!$bResult){
					$arrError[$sProp][$sIndex]=$this->getErrorMessage();
					$this->_sLastErrorMessage='';
					if(isset($arrPolicy['policy']) && !$arrPolicy['policy']['check_all_rules']){
						break;
					}
				}
			}
		}

		return $arrError;
	}

	private function checkField_($arrData,$arrCheckInfo){
		$bResult=true;
		switch($arrCheckInfo['extend']){
			case 'function':// 使用函数进行验证
			case 'callback':// 调用方法进行验证
				$arrArgs=isset($arrCheckInfo['rule'])?$arrCheckInfo['rule']:array();
				if(isset($arrData['field'])){
					array_unshift($arrArgs,$arrData['field']);
				}
				if('function'==$arrCheckInfo['extend']){
					if(function_exists($arrCheckInfo['extend'])){
						$bResult=call_user_func_array($arrCheckInfo['check'],$arrArgs);
					}else{
						Q::E('Function is not exist');
					}
				}else{
					if(is_array($arrCheckInfo['check'])){// 如果$sContent为数组，那么该数组为回调，先检查一下
						if(!is_callable($arrCheckInfo['check'],false)){// 检查是否为有效的回调
							C::E('Callback is not exist');
						}
					}else{// 否则使用模型中的方法进行填充
						$oModel=null;
						eval('$oModel=new '.$this->_sClassName.'();');
						$bResult = call_user_func_array(array($oModel,$arrCheckInfo['check']),$arrArgs);
					}
				}

				if($bResult===false){
					if(empty($arrCheckInfo['message'])){
						$arrCheckInfo['message']=Q::L('模型回调验证失败','__QEEPHP__@Q');
					}
					$this->_sErrorMessage=$arrCheckInfo['message'];
				}
				return $bResult;
				break;
			case 'confirm': // 验证两个字段是否相同
				$bResult=$arrData[$arrCheckInfo['field']]==$arrData[$arrCheckInfo['check']];
				if($bResult===false){
					if(empty($arrCheckInfo['message'])){
						$arrCheckInfo['message']=Q::L('模型验证两个字段是否相同失败','__QEEPHP__@Q');
					}
					$this->_sErrorMessage=$arrCheckInfo['message'];
				}
				return $bResult;
				break;
			case 'in': // 验证是否在某个数组范围之内
				$bResult=in_array($arrData[$arrCheckInfo['field']],$arrData[$arrCheckInfo['check']]);
				if($bResult===false){
					if(empty($arrCheckInfo['message'])){
						$arrCheckInfo['message']=Q::L('模型验证是否在某个范围失败','__QEEPHP__@Q');
					}
					$this->_sErrorMessage=$arrCheckInfo['message'];
				}
				return $bResult;
				break;
			case 'equal': // 验证是否等于某个值
				$bResult= $arrData[$arrCheckInfo['field']]==$arrCheckInfo['check'];
				if($bResult===false){
					if(empty($arrCheckInfo['message'])){
						$arrCheckInfo['message']=Q::L('模型验证是否等于某个值失败','__QEEPHP__@Q');
					}
					$this->_sErrorMessage=$arrCheckInfo['message'];
				}
				return $bResult;
				break;
			case 'regex':
			default: // 默认使用正则验证 可以使用验证类中定义的验证名称
				$oCheck=Check::RUN();
				$bResult=Check::checkByArgs($arrCheckInfo['check'],$arrCheckInfo['rule']);
				if($bResult===Check::SKIP_OTHERS){
					break;
				}

				if(!$bResult){
					if(empty($arrCheckInfo['message'])){
						$arrCheckInfo['message']=$oCheck->getErrorMessage();
					}
					$this->_sErrorMessage=$arrCheckInfo['message'];
					return $bResult;
				}
				break;
		 }

		 return $bResult;
	}

	public function isError(){
		return !empty($this->_sErrorMessage);
	}

	public function getErrorMessage(){
		return $this->_sErrorMessage;
	}

}
