<?php
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   全局数据验证器（Learn QP!）($$)*/

!defined('Q_PATH') && exit;

class Check{

	const SKIP_ON_FAILED='skip_on_failed';
	const SKIP_OTHERS='skip_others';
	const PASSED=true;
	const FAILED=false;
	const CHECK_ALL=true;
	protected static $_sErrorMessage;
	protected static $_oDefaultDbIns=null;

	private function __construct(){}

	public static function RUN($bDefaultIns=true){
		if($bDefaultIns and self::$_oDefaultDbIns){
			return self::$_oDefaultDbIns;
		}

		$oCheck=new self();
		if($bDefaultIns){
			self::$_oDefaultDbIns=$oCheck;
		}

		return $oCheck;
	}

	public static function C($Data,$Check){
		$arrArgs=func_get_args();
		unset($arrArgs[1]);
		$bResult=self::checkByArgs($Check,$arrArgs);
		return(bool)$bResult;
	}

	public static function checkBatch($Data,array $arrChecks,$bCheckAll=true,&$arrFailed=null){
		$bResult=true;
		$arrFailed=array();
		foreach($arrChecks as $arrV){
			$sVf=$arrV[0];
			$arrV[0]=$Data;

			$bRet=self::checkByArgs($sVf,$arrV);
			if($bRet===self::SKIP_OTHERS){// 跳过余下的验证规则
				return $bResult;
			}

			if($bRet===self::SKIP_ON_FAILED){
				$bCheckAll=false;
				continue;
			}

			if($bRet){
				continue;
			}

			$arrFailed[]=$arrV;
			$bResult=$bResult && $bRet;
			if(!$bResult && !$bCheckAll){
				return false;
			}
		}

		return(bool)$bResult;
	}

	public static function checkByArgs($Check,array $arrArgs){
		static $arrInternalFuncs=null;
		
		if(is_null($arrInternalFuncs)){
			$arrInternalFuncs=array(
				'between','date','datetime','digit','double','email','english',
				'equal','eq','float','greater_or_equal','egt','gt','in','integer',
				'int','ip','ipv4','less_or_equal','elt','less_than','lt','lower',
				'max','min','mobile','not_empty','not_null','not_same','num',
				'number','number_underline_english','regex','require','same',
				'empty','error','null','strlen','time','type','upper','url',
				'max_len','max_length','min_len','min_length');
			$arrInternalFuncs=array_flip($arrInternalFuncs);
		}

		self::$_sErrorMessage='';// 验证前还原状态

		if(!is_array($Check) && isset($arrInternalFuncs[$Check])){// 内置验证方法
			$bResult=call_user_func_array(array(__CLASS__,$Check.'_'),$arrArgs);
		}elseif(is_array($Check) || function_exists($Check)){// 使用回调处理
			$bResult=call_user_func_array($Check,$arrArgs);
		}elseif(strpos($Check,'::')){// 使用::回调处理
			$bResult=call_user_func_array(explode('::', $Check),$arrArgs);
		}else{// 错误的验证规则
			self::$_sErrorMessage=Q::L('不存在的验证规则','__QEEPHP__@Q');
			return false;
		}

		if($bResult===false){
			self::$_sErrorMessage=Q::L('验证数据出错','__QEEPHP__@Q');
		}

		return $bResult;
	}

	public static function between_($Data,$Min,$Max,$bInclusive=true){
		if($bInclusive){
			return $Data>=$Min && $Data<=$Max;
		}else{
			return $Data>$Min && $Data<$Max;
		}
	}

	public static function date_($Data){
		if(strpos($Data,'-')!==false){// 分析数据中关键符号
			$sP='-';
		}elseif(strpos($Data,'/')!==false){
			$sP='\/';
		}else{
			$sP=false;
		}

		if($sP!==false and  preg_match('/^\d{4}'.$sP.'\d{1,2}'.$sP.'\d{1,2}$/',$Data)){
			$arrValue=explode($sP,$Data);
			if(count($Data)>=3){
				list($nYear,$nMonth,$nDay)=$arrValue;
				if(checkdate($nMonth,$nDay,$nYear)){
					return true;
				}
			}
		}

		return false;
	}

	public static function datetime_($Data){
		$test=@strtotime($Data);
		if($test!==false && $test!==-1){
			return true;
		}

		return false;
	}

	public static function digit_($Data){
		return ctype_digit($Data);
	}

	public static function double_($Data){
		return preg_match('/^[-\+]?\d+(\.\d+)?$/',$Data);
	}

	public static function email_($Data){
		return preg_match('/([a-z0-9]*[-_\.]?[a-z0-9]+)*@([a-z0-9]*[-_]?[a-z0-9]+)+[\.][a-z]{2,3}([\.][a-z]{2})?/i',$Data);
	}

	public static function english_($Data){
		return preg_match('/^[A-Za-z]+$/',$Data);
	}

	public static function equal_($Data,$Test){
		return $Data==$Test;
	}

	public static function eq_($Data,$Test){
		return self::equal_($Data,$Test);
	}

	public static function float_($Data){
		static $arrLocale=null;
		
		if(is_null($arrLocale)){
			$arrLocale=localeconv();
		}
		$Data=str_replace($arrLocale['decimal_point'],'.',$Data);
		$Data=str_replace($arrLocale['thousands_sep'],'',$Data);

		if(strval(floatval($Data))==$Data){
			return true;
		}

		return false;
	}

	public static function greater_or_equal_($Data,$Test,$bInclusive=true){
		if($bInclusive){
			return $Data>=$Test;
		}else{
			return $Data>$Test;
		}
	}

	public static function egt_($Data,$Test,$bInclusive=true){
		return self::greater_or_equal_($Data,$Test,$bInclusive);
	}

	public static function gt_($Data,$Test){
		return self::greater_or_equal_($Data,$Test,false);
	}

	public static function in_($Data,$arrIn){
		return is_array($arrIn) and in_array($Data,$arrIn);
	}

	public static function integer_($Data){
		return preg_match('/^[-\+]?\d+$/',$Data);
	}

	public static function int_($Data,$Test){
		return self::integer_($Data);
	}

	public static function ip_($Data){
		return preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/',$Data);
	}

	public static function ipv4_($Data){
		$test=@ip2long($Data);
		if($test!==-1 and $test!==false){
			return true;
		}

		return false;
	}

	public static function less_or_equal_($Data,$Test,$bInclusive=true){
		if($bInclusive){
			return $Data<=$Test;
		}else{
			return $Data<$Test;
		}
	}
	public static function elt_($Data,$Test,$bInclusive=true){
		return self::less_or_equal_($Data,$Test,$bInclusive);
	}
	public static function less_than_($Data,$Test){
		return self::less_or_equal_($Data,$Test,false);
	}
	public static function lt_($Data,$Test){
		return self::less_or_equal_($Data,$Test,false);
	}

	public static function lower_($Data){
		return ctype_lower($Data);
	}

	public static function max_($Data,$Test){
		return $Data<=$Test;
	}

	public static function min_($Data,$Test){
		return $Data>=$Test;
	}

	public static function mobile_($Data){
		return preg_match("/1[3458]{1}\d{9}$/",$Data);
	}

	public static function not_empty_($Data){
		return !empty($Data);
	}

	public static function not_equal_($Data,$Test){
		return $Data!=$Test;
	}
	public static function neq_($Data,$Test){
		return self::not_equal_($Data,$Test);
	}

	public static function not_null_($Data){
		return !is_null($Data);
	}

	public static function not_same_($Data,$Test){
		return $Data!==$Test;
	}

	public static function num_($Data){
		return ($Data && preg_match('/\d+$/',$Data)) || !preg_match("/[^\d-.,]/",$Data) || $Data==0;
	}
	public static function number_($Data){
		return self::num_($Data);
	}

	public static function number_underline_english_($Data){
		return preg_match('/^[a-z0-9\-\_]*[a-z\-_]+[a-z0-9\-\_]*$/i',$Data);
	}

	public static function regex_($Data,$sRegex){
		return preg_match($sRegex,$Data)>0;
	}

	public static function require_($Data){
		return preg_match('/.+/',$Data);
	}

	public static function same_($Data,$Test){
		return $Data===$Test;
	}

	public static function empty_($Data){
		return (strlen($Data)==0)?self::SKIP_OTHERS:true;
	}

	public static function error_($Data){
		return self::SKIP_ON_FAILED;
	}

	public static function null_($Data){
		return (is_null($Data))?self::SKIP_OTHERS:true;
	}

	public static function strlen_($Data,$nLen){
		return strlen($Data)==(int)$nLen;
	}

	public static function time_($Data){
		$arrParts=explode(':',$Data);
		$nCount=count($arrParts);
		if($nCount==2 || $nCount==3){
			if($nCount==2){
				$arrParts[2]='00';
			}
			$test=@strtotime($arrParts[0].':'.$arrParts[1].':'.$arrParts[2]);
			if($test!==-1 && $test!==false && date('H:i:s')==$Data){
				return true;
			}
		}

		return false;
	}

	public static function type_($Data,$Test){
		return gettype($Data)==$Test;
	}

	public static function upper_($Data){
		return ctype_upper($Data);
	}

	public static function url_($Data){
		return preg_match('/^(https?):\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"\"])*$/',$Data);
	}

	public static function min_length_($Data,$nLen){
		return iconv_strlen($Data,'utf-8')>=$nLen;
	}

	public static function min_len_($Data,$nLen){
		return self::min_length_($Data,$nLen);
	}

	public static function max_length_($Data,$nLen){
		return iconv_strlen($Data,'utf-8')<=$nLen;
	}

	public static function max_len_($Data,$nLen){
		return self::max_length_($Data,$nLen);
	}

	public static function isError(){
		return !empty(self::$_sErrorMessage);
	}

	public static function getErrorMessage(){
		return self::$_sErrorMessage;
	}

}
