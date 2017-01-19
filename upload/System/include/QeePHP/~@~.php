<?php
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   QeePHP 基础初始化文件($$)*/
/** 防止乱码 */
header("Content-type:text/html;charset=utf-8");
/** QeePHP系统目录定义 */
define('Q_PATH',str_replace('\\','/',dirname(__FILE__)));
/** 系统初始化相关 */
$GLOBALS['_beginTime_']=microtime(TRUE);
define('IS_WIN',DIRECTORY_SEPARATOR=='\\'?1:0);
/** 全局错误函数 */
function E($sMessage){
	require_once(Q_PATH.'/Resource_/Template/Error.template.php');
	exit();
}
/** 应用路径定义 */
if(!defined('APP_PATH')){
	define('APP_PATH',dirname($_SERVER['SCRIPT_FILENAME']));
}
if(!defined('APP_RUNTIME_PATH')){
	define('APP_RUNTIME_PATH',APP_PATH.'/App/~Runtime');
}
if(!defined('DIS_RUNTIME_PATH')){
	define('DIS_RUNTIME_PATH',APP_RUNTIME_PATH.'/Dis');
}
/** 系统异常和错误处理 */
set_exception_handler(array('Q','exceptionHandler'));
if(!defined('QEEPHP_DEBUG')){
	define('QEEPHP_DEBUG',FALSE);
}
if(QEEPHP_DEBUG===TRUE){
	set_error_handler(array('Q','errorHandel'));
	register_shutdown_function(array('Q','shutdownHandel'));
}
/** 自动载入 */
if(function_exists('spl_autoload_register')) {
	spl_autoload_register(array('Q','autoload'));
}else{
	function __autoload($sClassName){
		Q::autoLoad($sClassName);
	}
}
/** 编译锁定文件 */
if(!defined('APP_RUNTIME_LOCK')){
	define('APP_RUNTIME_LOCK',APP_RUNTIME_PATH.'/~Runtime.inc.lock');
}
if(!is_file(APP_RUNTIME_LOCK)){
	require(Q_PATH.'/Common_/InitRuntime.inc.php');
}
/** QeePHP框架定义 | 本版本于2014/10/07发布 */
define('QEEPHP_VERSION','3.0');
/** 定义内存 */
define('MEMORY_LIMIT_ON',function_exists('memory_get_usage'));
if(MEMORY_LIMIT_ON){
	$GLOBALS['_startUseMems_']=memory_get_usage();
}
/** CURRENT_TIMESTAMP 定义为当前时间，减少框架调用 time()的次数 */
define('CURRENT_TIMESTAMP',time());
/** PHP魔术方法 */
if(version_compare(PHP_VERSION,'6.0.0','<')){
	if(version_compare(PHP_VERSION,'5.3.0','<')){
		@set_magic_quotes_runtime(0);
	}
	define('MAGIC_QUOTES_GPC',get_magic_quotes_gpc()?TRUE:FALSE);
}
class Q{
	static private $INSTANCES=array();
	static private $OBJECTS=array();
	static private $_arrClassRegex=array('/^(.+)\.class\.php$/i','/^(.+)\.interface\.php$/i');
	static private $_arrClassFilePat=array('%DirPath%/%ClassName%.class.php');
	static private $_arrInterPat=array('%DirPath%/%ClassName%.interface.php');
	static private $CLASS_PATH='Q.inc';
	static private $_arrImportedPackDir=array();
	static private $_bAutoLoad=true;
	static private $_sPackagePath='';
	static private $_arrConfig=array();
	static function G($sKey,$sVar='R'){
		$sVar=strtoupper($sVar);
		switch($sVar){
			case 'G':$sVar=&$_GET;break;
			case 'P':$sVar=&$_POST;break;
			case 'C':$sVar=&$_COOKIE;break;
			case 'S':$sVar=&$_SESSION;break;
			case 'R':$sVar=&$_REQUEST;break;
			case 'F':$sVar=&$_FILES;break;
		}
		return isset($sVar[$sKey])?$sVar[$sKey]:NULL;
	}
	static public function import($sPackage,$bForce=false){
		if(!is_dir($sPackage)){
			Q::E("Package:'{$sPackage}' does not exists.");
		}
		// 包路径
		self::$_sPackagePath=$sPackagePath=realpath($sPackage).'/';
		$sClassPathFile=$sPackagePath.self::$CLASS_PATH;
		if($bForce || !is_file($sClassPathFile)){
			$arrFileStores=array();
			// 扫描类
			$arrClassPath=self::viewClass($sPackagePath);
			foreach($arrClassPath as $arrMap){
				$arrFileStores[$arrMap['class']]=$arrMap['file'];
				$arrKeys[]=$arrMap['class'];
			}
			// 检查是否有重复的类
			if(!empty($arrKeys) && count($arrKeys)!=count(array_unique($arrKeys))){
				$arrDiffKeys=array();
				$arrDiffUnique=array_unique($arrKeys);
				foreach($arrDiffUnique as $nKey=>$sValue){
					if(in_array($sValue,$arrKeys)){
						unset($arrKeys[$nKey]);
					}
				}
				E(sprintf('Same class %s exists',implode(',',$arrKeys)));
			}
			foreach($arrFileStores as $nKeyFileStore=>$sFileStoreValue){
				if(in_array(Q_PATH.'/'.$sFileStoreValue,(array)(include Q_PATH.'/Common_/Paths.inc.php'))){
					unset($arrFileStores[$nKeyFileStore]);
				}
			}
			$sFileContents=serialize($arrFileStores);
			// 类路径文件
			if(!is_file($sClassPathFile)){
				if(($hFile=fopen($sClassPathFile,'a'))!==false){
					fclose($hFile);
					chmod($sClassPathFile,0666);
				}else{
					return false;
				}
			}
			// 写入文件
			if(!file_put_contents($sClassPathFile,$sFileContents)){
				E(sprintf('Can not create Class Path File: %s',$sClassPathFile));
			}
		}
		// 读取Classes Path文件
		self::$OBJECTS=array_merge(self::$OBJECTS,array_map(array('Q','reallyPath'),self::readCache($sClassPathFile)));
	}
	static private function reallyPath($sValue){
		return self::$_sPackagePath.$sValue;
	}
	static private function readCache($sCacheFile){
		return unserialize(file_get_contents($sCacheFile));
	}
	static public function regClass($sClass,$sPath){
		if(isset(self::$OBJECTS[$sClass])){
			E(sprintf('Class %s already exist in %s and unable to repeat the register',$sClass,$sPath));
		}
		self::$OBJECTS[$sClass]=$sPath;
	}
	static public function setAutoload($bAutoload){
		if(!is_bool($bAutoload)){
			$bAutoload=$bAutoload?true:false;
		}else{
			$bAutoload=&$bAutoload;
		}
		$bOldValue=self::$_bAutoLoad;
		self::$_bAutoLoad=$bAutoload;
		return $bOldValue;
	}
	static public function autoLoad($sClassName){
		if(isset(self::$OBJECTS[$sClassName]) && !self::classExists($sClassName) && !self::classExists($sClassName,true)){
			require(self::$OBJECTS[$sClassName]);
		}
	}
	static public function classExists($sClassName,$bInter=false,$bAutoload=false){
		$bAutoloadOld=self::setAutoload($bAutoload);
		$sFuncName=$bInter?'interface_exists':'class_exists';
		$bResult=$sFuncName($sClassName);
		self::setAutoload($bAutoloadOld);
		return $bResult;
	}
	static private function viewClass($sDirectory,$sPreFilename=''){
		$arrReturnClass=array();
		$sDirectoryPath=realpath($sDirectory).'/';
		$hDir=opendir($sDirectoryPath);
		while(($sFilename=readdir($hDir))!==false){
			$sPath=$sDirectoryPath.$sFilename;
			if(is_file($sPath)){// 文件
				foreach(self::$_arrClassRegex as $sRegexp){
					$arrRes=array();// 找到类文件
					if(preg_match($sRegexp,$sFilename,$arrRes)){
						$sClassName=isset($arrRes[1])?$arrRes[1]:null;
						if($sClassName){
							$arrReturnClass[]=array('class'=>$sClassName,'file'=>$sPreFilename.$sFilename);
						}
					}
				}
			}else if(is_dir($sPath)){// 目录
				$sSpecialDir=array('.','..','.svn','#note');
				if(in_array($sFilename,$sSpecialDir)){// 排除特殊目录
					unset($sSpecialDir);
					continue;
				}else{// 递归子目录
					$arrReturnClass=array_merge($arrReturnClass,self::viewClass($sPath,$sPreFilename.$sFilename.'/'));
				}
			}else{
				Q::E(sprintf("\$sPath:%s is not a valid path",$sPath));
			}
		}
		return $arrReturnClass;
	}
	static public function instance($sClass,$Args=null,$sMethod=null,$MethodArgs=null){
		$sIdentify=$sClass.serialize($Args).$sMethod.serialize($MethodArgs);// 惟一识别号
		if(!isset(self::$INSTANCES[$sIdentify])){
			if(class_exists($sClass)){
				$oClass=$Args===null?new $sClass():new $sClass($Args);
				if(!empty($sMethod) && method_exists($oClass,$sMethod)){
					self::$INSTANCES[$sIdentify]=$MethodArgs===null?call_user_func(array(&$oClass,$sMethod)):call_user_func_array(array(&$oClass,$sMethod),array($MethodArgs));
				}else{
					self::$INSTANCES[$sIdentify]=$oClass;
				}
			}else{
				Q::E(sprintf('class %s is not exists',$sClass));
			}
		}
		return self::$INSTANCES[$sIdentify];
	}
	static public function cache($sId,$Data='',array $arrOption=null,$sBackendClass=null){
		static $oObj=null;
		$nCacheTime=self::cacheTime_($sId);
		$arrDefaultOption=array(
			'cache_time'=>$nCacheTime?$nCacheTime:$GLOBALS['_commonConfig_']['RUNTIME_CACHE_TIME'],
			'cache_prefix'=>$GLOBALS['_commonConfig_']['RUNTIME_CACHE_PREFIX'],
			'cache_backend'=>$GLOBALS['_commonConfig_']['RUNTIME_CACHE_BACKEND'],
		);
		if(is_array($arrOption)){
			$arrOption=array_merge($arrDefaultOption,$arrOption);
		}else{
			$arrOption=$arrDefaultOption;
		}
		if(is_null($sBackendClass)){
			$sBackendClass=$arrOption['cache_backend'];
		}
		if(is_null($oObj)){
			$oObj=self::instance($sBackendClass);
		}
		if($Data===''){
			// 强制刷新页面数据
			if(Q::G($GLOBALS['_commonConfig_']['CACHE_FORCE_NAME'])==1){
				return false;
			}
			return $oObj->getCache($sId,$arrOption);
		}
		if($Data===null){
			return $oObj->deleleCache($sId,$arrOption);
		}
		return $oObj->setCache($sId,$Data,$arrOption);
	}
	static private function cacheTime_($sId){
		$nCacheTime=0;
		if(!empty($GLOBALS['_commonConfig_']['RUNTIME_CACHE_TIMES'][$sId])){
			$nCacheTime=intval($GLOBALS['_commonConfig_']['RUNTIME_CACHE_TIMES'][$sId]);
			return $nCacheTime;
		}
		foreach($GLOBALS['_commonConfig_']['RUNTIME_CACHE_TIMES'] as $sKey=>$nValue){
			$sKeyCache=str_replace('_*','',$sKey);
			if($sKeyCache==$sId){
				return $GLOBALS['_commonConfig_']['RUNTIME_CACHE_TIMES'][$sKey];
				break;
			}
		}
		return $nCacheTime;
	}
	public static function normalize($Input,$sDelimiter=',',$bAllowedEmpty=false){
		if(is_array($Input) || is_string($Input)){
			if(!is_array($Input)){
				$Input=explode($sDelimiter,$Input);
			}
			$Input=array_filter($Input);// 过滤null
			if($bAllowedEmpty===true){
				return $Input;
			}else{
				$Input=array_map('trim',$Input);
				return array_filter($Input,'strlen');
			}
		}else{
			return $Input;
		}
	}
	static public function exceptionHandler(Exception $oE){
		$sErrstr=$oE->getMessage();
		$sErrfile=$oE->getFile();
		$nErrline=$oE->getLine();
		$nErrno=$oE->getCode();
		$sErrorStr="[$nErrno] $sErrstr ".basename($sErrfile).self::L(" 第 %d 行。",'__QEEPHP__@Q',null,$nErrline);
		if($GLOBALS['_commonConfig_']['LOG_RECORD'] && self::C('LOG_MUST_RECORD_EXCEPTION')){
			Log::W($sErrstr,Log::EXCEPTION);
		}
		if(method_exists($oE,'formatException')){
			self::halt($oE->formatException());
		}else{
			self::halt($oE->getMessage());
		}
	}
	static public function errorHandel($nErrorNo,$sErrStr,$sErrFile,$nErrLine){
		if($nErrorNo){
			E("<b>[{$nErrorNo}]:</b> {$sErrStr}<br><b>File:</b> {$sErrFile}<br><b>Line:</b> {$nErrLine}");
		}
	}
	static public function shutdownHandel(){
		if(($arrError=error_get_last()) && $arrError['type']){
			E("<b>[{$arrError['type']}]:</b> {$arrError['message']}<br><b>File:</b> {$arrError['file']}<br><b>Line:</b> {$arrError['line']}");
		}
	}
	static public function C($sName='',$Value=NULL,$Default=null){
		// 时返回配置数据
		if(is_string($sName) && !empty($sName) && $Value===null){
			if(!strpos($sName,'.')){
				return array_key_exists($sName,self::$_arrConfig)?self::$_arrConfig[$sName]:$Default;
			}
			$arrParts=explode('.',$sName);
			$arrConfig=&self::$_arrConfig;
			foreach($arrParts as $sPart){
				if(!isset($arrConfig[$sPart])){
					return $Default;
				}
				$arrConfig=&$arrConfig[$sPart];
			}
			return $arrConfig;
		}
		// 返回所有配置值
		if($sName==='' && $Value===null){
			return self::$_arrConfig;
		}
		// 设置值
		if(is_array($sName)){
			foreach($sName as $sKey=>$value){
				self::C($sKey,$value,$Default);
			}
			return self::$_arrConfig;
		}else{
			if(!strpos($sName,'.')){
				self::$_arrConfig[$sName]=$Value;
				return;
			}
			$arrParts=explode('.',$sName);
			$nMax=count($arrParts)-1;
			$arrConfig=&self::$_arrConfig;
			for($nI=0;$nI<=$nMax;$nI++){
				$sPart=$arrParts[$nI];
				if($nI<$nMax){
					if(!isset($arrConfig[$sPart])){
						$arrConfig[$sPart]=array();
					}
					$arrConfig=&$arrConfig[$sPart];
				}else{
					$arrConfig[$sPart]=$Value;
				}
			}
			return self::$_arrConfig;
		}
		// 删除值
		if($sName===null){
			self::$_arrConfig=array();
		}elseif(!strpos($sName,'.')){
			unset(self::$_arrConfig[$sName]);
		}else{
			$arrParts=explode('.',$sName);
			$nMax=count($arrParts)-1;
			$arrConfig=&self::$_arrConfig;
			for($nI=0;$nI<=$nMax;$nI++){
				$sPart=$arrParts[$nI];
				if($nI<$nMax){
					if(!isset($arrConfig[$sPart])){
						$arrConfig[$sPart]=array();
					}
					$arrConfig=&$arrConfig[$sPart];
				}else{
					unset($arrConfig[$sPart]);
				}
			}
		}
		return self::$_arrConfig;
	}
	static public function throwException($sMsg,$sType='QException',$nCode=0){
		if($sType==''||$sType===null){
			$sType='QException';
		}
		if(Q::classExists($sType)){
			throw new $sType($sMsg,$nCode);
		}else{
			self::halt($sMsg);// 异常类型不存在则输出错误信息字串
		}
	}
	static public function E($sMsg,$sType='QException',$nCode=0){
		self::throwException($sMsg,$sType,$nCode);
	}
	static public function L($sValue,$Package=null,$Lang=null/*Argvs*/){
		if(!$GLOBALS['_commonConfig_']['LANG_ON']){
			if(func_num_args()>3){// 代入参数
				$arrArgs=func_get_args();
				$arrArgs[0]=$sValue;
				unset($arrArgs[1],$arrArgs[2]);
				$sValue=call_user_func_array('sprintf',$arrArgs);
			}
			return $sValue;
		}
		
		$arrArgvs=func_get_args();
		if(!isset($arrArgvs[1]) OR empty($Package)){
			$arrArgvs[1]='app';
		}
		if(!isset($arrArgvs[2])){
			if(!defined('LANG_NAME')){
				$arrArgvs[2]='Zh-cn';
			}else{
				$arrArgvs[2]=LANG_NAME;
			}
		}
		$sValue=call_user_func_array(array('Lang','setEx'),$arrArgvs);
		return $sValue;
	}
	static public function cookie($sName,$Value='',$nLife=0,$sCookieDomain=null,$bPrefix=true,$bHttponly=false,$bOnlyDeletePrefix=true){
		// 清除指定前缀的所有cookie
		if(is_null($sName)){
			if(empty($_COOKIE)){ 
				return;
			}
			Cookie::clearCookie($bOnlyDeletePrefix);
			return;
		}
		// 如果值为null，则删除指定COOKIE
		if($nLife<0 || $Value===null){
			Cookie::deleteCookie($sName,$sCookieDomain,$bPrefix);
		}elseif($Value=='' && $nLife>=0){// 如果值为空，则获取cookie
			return Cookie::getCookie($sName,$bPrefix);
		}else{// 设置COOKIE
			Cookie::setCookie($sName,$Value,$nLife,$sCookieDomain,$bPrefix,$bHttponly);
		}
	}
	static private function parseU_($sValue){
		if(strpos($sValue,':')!==false){
			$sValue=explode(':',$sValue);
			return Q::G($sValue[0])!==null?Q::G($sValue[0]):$sValue[1];
		}else{
			return Q::G($sValue);
		}
	}
	static public function U_($sDomain='',$sHttpPrefix='',$sHttpSuffix=''){
		static $sHttpPrefix='',$sHttpSuffix='';
		if(!$sHttpPrefix){
			$sHttpPrefix=C::isSsl()?'https://':'http://';
			$sHttpSuffix=$GLOBALS['_commonConfig_']['DOMAIN_TOP'].$GLOBALS['_commonConfig_']['DOMAIN_SUFFIX'];
		}
		return $sHttpPrefix.($sDomain && $sDomain!='*'?$sDomain.'.':'').$sHttpSuffix;
	}
	static public function U($sUrl,$arrParams=array(),$bNormalurl=false,$bRedirect=false,$bSuffix=true){
		// URL支持[var]风格模式替换
		$sUrl=@preg_replace("/\[([0-9a-zA-Z\_\-\:\.\/]+)\]/e",'Q::parseU_(\'\1\')',$sUrl);
		// 剔除受保护的额外参数
		if($GLOBALS['_commonConfig_']['U_PRO_VAR']){
			foreach(explode(',',$GLOBALS['_commonConfig_']['U_PRO_VAR']) as $sTempVar){
				if(isset($arrParams[$sTempVar])){
					unset($arrParams[$sTempVar]);
				}
			}
		}
		
		// 剥离子域名
		$sDomainUrl='';
		if(strpos($sUrl,'~@')!==false){
			$sUrl=explode('~@',$sUrl);
			$sDomainUrl=$sUrl[0];
			$sUrl=$sUrl[1];
		}else{
			$sDomainUrl=false;
		}
		
		// 以“/”开头的为自定义URL
		$bCustom=false;
		if(0===strpos($sUrl,'/')){
			$bCustom=true;
		}else{
			if(!strpos($sUrl,'://')){
				$sUrl=APP_NAME.'://'.$sUrl;
			}
			if(stripos($sUrl,'@?')){
				$sUrl=str_replace('@?','@qeephp?',$sUrl);
			}elseif(stripos($sUrl,'@')){
				$sUrl=$sUrl.MODULE_NAME;
			}
			// app && 路由
			$arrArray=parse_url($sUrl);
			$sApp=isset($arrArray['scheme'])?$arrArray['scheme']:APP_NAME;// APP
			$sRoute=isset($arrArray['user'])?$arrArray['user']:'';// 路由
			// 分析获取模块和操作(应用)
			if(!empty($arrParams['app'])){
				$sApp=strtolower($arrParams['app']);
				unset($arrParams['app']);
			}
			if(!empty($arrParams['c'])){
				$sModule=strtolower($arrParams['c']);
				unset($arrParams['c']);
			}
			if(!empty($arrParams['a'])){
				$sAction=strtolower($arrParams['a']);
				unset($arrParams['a']);
			}
			if(isset($arrArray['path'])){
				if(!isset($sModule)){
					if(!isset($arrArray['host'])){
						$sModule=MODULE_NAME;
					}else{
						$sModule=$arrArray['host'];
					}
				}
				
				if(!isset($sAction)){
					$sAction=substr($arrArray['path'],1);
				}
			}else{
				if(!isset($sModule)){
					$sModule=MODULE_NAME;
				}
				if(!isset($sAction)){
					$sAction=$arrArray['host'];
				}
			}
			// 如果指定了查询参数
			if(isset($arrArray['query'])){
				$arrQuery=array();
				parse_str($arrArray['query'],$arrQuery);
				$arrParams=array_merge($arrQuery,$arrParams);
			}
		}
		
		// 如果开启了URL解析，则URL模式为非普通模式
		if(($GLOBALS['_commonConfig_']['URL_MODEL']>0 && $bNormalurl===false) || $bCustom===true){
			$sDepr=$GLOBALS['_commonConfig_']['URL_PATHINFO_MODEL']==2?$GLOBALS['_commonConfig_']['URL_PATHINFO_DEPR']:'/';
			if(!empty($sRoute)){
				// 匹配路由参数
				if(isset($GLOBALS['_commonConfig_']['_ROUTER_'][$sRoute])){
					$arrRouters=$GLOBALS['_commonConfig_']['_ROUTER_'][$sRoute];
					if(!empty($arrRouters[1])){
						$arrRoutervalue=explode(',',$arrRouters[1]);
						foreach($arrRoutervalue as $sRoutervalue){
							if(array_key_exists($sRoutervalue,$arrParams)){
								$sRoute.=$sDepr.urlencode($arrParams[$sRoutervalue]);
								unset($arrParams[$sRoutervalue]);
							}
						}
					}
				}
				$sStr=$sDepr;
				foreach($arrParams as $sVar=>$sVal){
					$sStr.=$sVar.$sDepr.urlencode($sVal).$sDepr;
				}
				$sStr=substr($sStr,0,-1);
				$sUrl=(__APP__!=='/'?__APP__:'').($GLOBALS['_commonConfig_']['DEFAULT_APP']!=$sApp?'/app'.$sDepr.$sApp.$sDepr:'/').$sRoute.$sStr;
			}else{
				$sStr=$sDepr;
				foreach($arrParams as $sVar=>$sVal){
					$sStr.=$sVar.$sDepr.urlencode($sVal).$sDepr;
				}
				$sStr=substr($sStr,0,-1);
				if(!$bCustom){
					$sUrl=(__APP__!=='/'?__APP__:'').($GLOBALS['_commonConfig_']['DEFAULT_APP']!=$sApp?'/app'.$sDepr.$sApp.$sDepr:'/');
					if($sStr){
						$sUrl.=$sModule.$sDepr.$sAction.$sStr;
					}else{
						$sTemp='';
						if($GLOBALS['_commonConfig_']['DEFAULT_CONTROL']!=$sModule || $GLOBALS['_commonConfig_']['DEFAULT_ACTION']!=$sAction){
							$sTemp.=$sModule;
						}
						if($GLOBALS['_commonConfig_']['DEFAULT_ACTION']!=$sAction){
							$sTemp.=$sDepr.$sAction;
						}
						if($sTemp==''){
							$sUrl=rtrim($sUrl,'/'.$sDepr);
						}else{
							$sUrl.=$sTemp;
						}
					}
				}else{
					$sUrl.=$sStr;
				}
			}
			if($bSuffix && $sUrl && $GLOBALS['_commonConfig_']['URL_HTML_SUFFIX']){
				$sUrl.=$GLOBALS['_commonConfig_']['URL_HTML_SUFFIX'];
			}
		}else{
			$sStr='';
			foreach($arrParams as $sVar=>$sVal){
				$sStr.=$sVar.'='.urlencode($sVal).'&';
			}
			$sStr=rtrim($sStr,'&');
			if(empty($sRoute)){
				$sTemp='';
				if($bNormalurl===true || $GLOBALS['_commonConfig_']['DEFAULT_APP']!=$sApp){
					$sTemp[]='app='.$sApp;
				}
				if($GLOBALS['_commonConfig_']['DEFAULT_CONTROL']!=$sModule){
					$sTemp[]='c='.$sModule;
				}
				if($GLOBALS['_commonConfig_']['DEFAULT_ACTION']!=$sAction){
					$sTemp[]='a='.$sAction;
				}
				if($sStr){
					$sTemp[]=$sStr;
				}
				if(!empty($sTemp)){
					$sTemp='?'.implode('&',$sTemp);
				}
				$sUrl=($bNormalurl===true || __APP__!=='/'?__APP__:'').$sTemp;
			}else{
				$sUrl=($bNormalurl===true || __APP__!=='/'?__APP__:'').($bNormalurl===true || $GLOBALS['_commonConfig_']['DEFAULT_APP']!=$sApp?'?app='.$sApp.'&':'?').($sRoute?'r='.$sRoute:'').($sStr?'&'.$sStr:'');
			}
		}
		// 子域名支持
		if($GLOBALS['_commonConfig_']['DOMAIN_ON']===true){
			if($sDomainUrl===false){
				$sDomainUrl='www';
			}elseif($sDomainUrl==''){
				$sDomainUrl='*';
			}elseif($sDomainUrl=='*'){
				$sDomainUrl='';
			}
			if($sDomainUrl){
				$sDomainUrl=self::U_($sDomainUrl);
			}
		}elseif($GLOBALS['_commonConfig_']['URL_DOMAIN_ON']===true){// URL加上域名
			$sDomainUrl=$GLOBALS['_commonConfig_']['URL_DOMAIN'];
		}else{
			$sDomainUrl='';
		}
		$sUrl=$sDomainUrl.$sUrl;
		if($bRedirect){
			C::urlGo($sUrl);
		}else{
			return $sUrl;
		}
	}
	static public function halt($Error){
		$arrError=array();
		if(is_array($Error)){
			$arrError=array_merge($arrError,$Error);
		}
		// 否则定向到错误页面
		if(!empty($GLOBALS['_commonConfig_']['ERROR_PAGE']) && QEEPHP_DEBUG===FALSE){
			C::urlGo(self::U($GLOBALS['_commonConfig_']['ERROR_PAGE']));
		}else{
			if($GLOBALS['_commonConfig_']['SHOW_ERROR_MSG']){
				$arrError['message']=is_array($Error)?$Error['message']:$Error;
			}else{
				$arrError['message']='Error';
			}
			include(Q_PATH.'/Resource_/Template/QException.template.php');// 包含异常页面模板
		}
		exit;
	}
	static public function tag($sTag){
		if(array_key_exists($sTag,$GLOBALS['_commonConfig_']['GLOBALS_TAGS'])){
			if(is_array($GLOBALS['_commonConfig_']['GLOBALS_TAGS'][$sTag])){
				$arrOption=$GLOBALS['_commonConfig_']['GLOBALS_TAGS'][$sTag];
			}else{
				$arrOption=array($GLOBALS['_commonConfig_']['GLOBALS_TAGS'][$sTag],array());
			}
			$sTag=ucfirst($arrOption[0]).'Behavior';
			if(Q::classExists($sTag)){
				$oBehavior=new $sTag();
				$oBehavior->RUN($arrOption[1]);
			}
		}
	}
}
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   异常捕获($$)*/
class QException extends Exception{
	private $_sType;
	public function __construct($sMessage,$nCode=0){
		parent::__construct($sMessage,$nCode);
	}
	public function __toString(){
		return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
	}
	public function formatException(){
		$arrError=array();
		
		$arrTrace=array_reverse($this->getTrace());
		$this->class=isset($arrTrace['0']['class'])?$arrTrace['0']['class']:'';
		$this->function=isset($arrTrace['0']['function'])?$arrTrace['0']['function']:'';
		
		$sTraceInfo='';
		if($GLOBALS['_commonConfig_']['APP_DEBUG']){
			foreach($arrTrace as $Val){
				$sClass=isset($Val['class'])?$Val['class']:'';
				$this->_sType=$sType=isset($Val['type'])?$Val['type']:'';
				$sFunction=isset($Val['function'])?$Val['function']:'';
				$sFile=isset($Val['file'])?$Val['file']:'';
				$sLine=isset($Val['line'])?$Val['line']:'';
				$args=isset($Val['args'])?$Val['args']:'';
				$sArgsInfo='';
				if(is_array($args)){
					foreach($args as $sK=>$V){
						$sArgsInfo.=($sK!=0?',':'').(is_scalar($V)?strip_tags(var_export($V,true)):gettype($V));
					}
				}
				$sFile=$this->safeFile($sFile);
				$sTraceInfo.="<li>[Line: {$sLine}] {$sFile} - {$sClass}{$sType}{$sFunction}({$sArgsInfo})</li>";
			}
			$arrError['trace']=$sTraceInfo;
		}
		$arrError['message']=$this->message;
		$arrError['type']=$this->_sType;
		$arrError['class']=$this->class;
		$arrError['code']=$this->getCode();
		$arrError['function']=$this->function;
		$arrError['line']=$this->line;
		$arrError['file']=$this->safeFile($this->file);
		return $arrError;
	}
	public function safeFile($sFile){
		$sFile=str_replace(C::tidyPath(Q_PATH),'{Q_PATH}',C::tidyPath($sFile));
		$sFile=str_replace(C::tidyPath(APP_PATH),'{APP_PATH}',C::tidyPath($sFile));
		if(strpos($sFile,':/') || strpos($sFile,':\\') || strpos($sFile,'/')===0){
			$sFile=basename($sFile);
		}
		return $sFile;
	}
}
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   全局函数集($$)*/
class Common{
	public static function seccode($arrOption=null,$bChinesecode=false,$nSeccodeTupe=1){
		@header("Expires: -1");// 定义头部
		@header("Cache-Control: no-store,private,post-check=0,pre-check=0,max-age=0",FALSE);
		@header("Pragma: no-cache");
		$nSeccode=C::randString(6,null,true);
		if($bChinesecode and $nSeccodeTupe==1){ // 中文
			$sChineseLang=(string)include(Q_PATH.'/Resource_/Images/Seccode/Chinese.inc.php');
			$arrCode=array(substr($nSeccode,0,3),substr($nSeccode,3,3));
			$nSeccode='';
			for($nI=0;$nI<2;$nI++){
				$nSeccode.=substr($sChineseLang,$arrCode[$nI]*3,3);
			}
		}else{
			$sS=sprintf('%04s',base_convert($nSeccode,10,24));
			$sSeccodeUnits='BCEFGHJKMPQRTVWXY2346789';
			$nSeccode='';
			for($nI=0;$nI<4;$nI++){
				$sUnit=ord($sS{$nI});
				$nSeccode.=($sUnit>=0x30 and $sUnit<=0x39)?$sSeccodeUnits[$sUnit-0x30]:$sSeccodeUnits[$sUnit-0x57];
			}
		}
		Q::cookie('_seccode_',C::authcode(strtoupper($nSeccode),FALSE));
		$oCode=new Seccode($arrOption);// 实例化对象
		$oCode->setCode($nSeccode)->display();
	}
	public static function checkSeccode($sSeccode){
		$nOldSeccode=C::authcode(Q::cookie('_seccode_'));
		if(empty($nOldSeccode)){
			return false;
		}
		return $nOldSeccode==strtoupper($sSeccode);// 开始比较数据
	}
	static public function stripslashes($String,$bRecursive=true){
		if($bRecursive===true and is_array($String)){// 递归
			foreach($String as $sKey=>$value){
				$String[self::stripslashes($sKey)]=self::stripslashes($value);// 如果你只注意到值，却没有注意到key
			}
		}else{
			if(is_string($String)){
				$String=stripslashes($String);
			}
		}
		return $String;
	}
	static public function stripslashesMagicquotegpc(){
		if(self::getMagicQuotesGpc()){
			$_GET=self::stripslashes($_GET);
			$_POST=self::stripslashes($_POST);
			$_COOKIE=self::stripslashes($_COOKIE);
			$_REQUEST=self::stripslashes($_REQUEST);
		}
	}
	static public function addslashes($String,$bRecursive=true){
		if($bRecursive===true and is_array($String)){
			foreach($String as $sKey=>$value){
				$String[self::addslashes($sKey)]=self::addslashes($value);// 如果你只注意到值，却没有注意到key
			}
		}else{
			if(is_string($String)){
				$String=addslashes($String);
			}
		}
		return $String;
	}
	static public function getMagicQuotesGpc(){
		return(defined('MAGIC_QUOTES_GPC') && MAGIC_QUOTES_GPC===TRUE);
	}
	static public function varType($Var,$sType){
		$sType=trim($sType);// 整理参数，以支持array:ini格式
		$arrTypes=explode(':',$sType);
		$sRealType=$arrTypes[0];
		$sAllow=isset($arrTypes[1])?$arrTypes[1]:null;
		$sRealType=strtolower($sRealType);
		switch($sRealType){
			case 'string':// 字符串
				return is_string($Var);
			case 'integer':// 整数
			case 'int' :
				return is_int($Var);
			case 'float':// 浮点
				return is_float($Var);
			case 'boolean':// 布尔
			case 'bool':
				return is_bool($Var);
			case 'num':// 数字
			case 'numeric':
				return is_numeric($Var);
			case 'base':// 标量（所有基础类型）
			case 'scalar':
				return is_scalar($Var);
			case 'handle':// 外部资源
			case 'resource':
				return is_resource($Var);
			case 'array':{// 数组
				if($sAllow){
					$arrAllow=explode(',',$sAllow);
					return self::checkArray($Var,$arrAllow);
				}else{
					return is_array($Var);
				}
			}
			case 'object':// 对象
				return is_object($Var);
			case 'null':// 空
			case 'NULL':
				return($Var===null);
			case 'callback':// 回调函数
				return is_callable($Var,false);
			default :// 类
				return self::isKindOf($Var,$sType);
		}
	}
	static public function smartDate($nDateTemp,$sDateFormat='Y-m-d H:i'){
		$sReturn='';
		$nSec=CURRENT_TIMESTAMP-$nDateTemp;
		$nHover=floor($nSec/3600);
		if($nHover==0){
			$nMin=floor($nSec/60);
			if($nMin==0){
				$sReturn=$nSec.' '.Q::L("秒前",'__QEEPHP__@Q');
			}else{
				$sReturn=$nMin.' '.Q::L("分钟前",'__QEEPHP__@Q');
			}
		}elseif($nHover<24){
			$sReturn=Q::L("大约 %d 小时前",'__QEEPHP__@Q',null,$nHover);
		}else{
			$sReturn=date($sDateFormat,$nDateTemp);
		}
		return $sReturn;
	}
	static public function urlGo($sUrl,$nTime=0,$sMsg='',$nWidth=650){
		$sUrl=str_replace(array("\n","\r"),'',$sUrl);// 多行URL地址支持
		if(empty($sMsg)){
			$sMsg=Q::L("系统将在%d秒之后自动跳转到%s。",'__QEEPHP__@Q',null,$nTime,$sUrl);
		}
		if(!headers_sent()){
			if(0==$nTime){
				header("Location:".$sUrl);
			}else{
				header("refresh:{$nTime};url={$sUrl}");
				include(Q_PATH.'/Resource_/Template/UrlGo.template.php');// 包含跳转页面模板
			}
			exit();
		}else{
			$sHeader="<meta http-equiv='Refresh' content='{$nTime};URL={$sUrl}'>";
			if($nTime==0){
				$sHeader='';
			}
			include(Q_PATH.'/Resource_/Template/UrlGo.template.php');// 包含跳转页面模板
			exit();
		}
	}
	static public function randString($nLength,$sCharBox=null,$bNumeric=false){
		if($bNumeric===true){
			return sprintf('%0'.$nLength.'d',mt_rand(1,pow(10,$nLength)-1));
		}
		if($sCharBox===null){
			$sBox=strtoupper(md5(self::now(true).rand(1000000000,9999999999)));
			$sBox.=md5(self::now(true).rand(1000000000,9999999999));
		}else{
			$sBox=&$sCharBox;
		}
		$nN=$nLength;
		$nBoxEnd=strlen($sBox)-1;
		$sRet='';
		while($nN--){
			$sRet.=substr($sBox,rand(0,$nBoxEnd),1);
		}
		return $sRet;
	}
	static public function now($bExact=true){
		if($bExact){
			list($nMS,$nS)=explode(' ',microtime());
			return $nS+$nMS;
		}else{
			return CURRENT_TIMESTAMP;
		}
	}
	static public function gbkToUtf8($FContents,$sFromChar,$sToChar='utf-8'){
		if(empty($FContents)){
			return $FContents;
		}
		$sFromChar=strtolower($sFromChar)=='utf8'?'utf-8':strtolower($sFromChar);
		$sToChar=strtolower($sToChar)=='utf8'?'utf-8':strtolower($sToChar);
		if($sFromChar==$sToChar || (is_scalar($FContents) && !is_string($FContents))){
			return $FContents;
		}
		if(is_string($FContents)){
			if(function_exists('iconv')){
				return iconv($sFromChar,$sToChar.'//IGNORE',$FContents);
			}elseif(function_exists('mb_convert_encoding')){
				return mb_convert_encoding($FContents,$sToChar,$sFromChar);
			}else{
				return $FContents;
			}
		}elseif(is_array($FContents)){
			foreach($FContents as $sKey=>$sVal){
				$sKeyTwo=self::gbkToUtf8($sKey,$sFromChar,$sToChar);
				$FContents[$sKeyTwo]=self::gbkToUtf8($sVal,$sFromChar,$sToChar);
				if($sKey!=$sKeyTwo){
					unset($FContents[$sKeyTwo]);
				}
			}
			return $FContents;
		}else{
			return $FContents;
		}
	}
	public static function isUtf8($sString){
		$nLength=strlen($sString);
		for($nI=0;$nI<$nLength;$nI++){
			if(ord($sString[$nI])<0x80){
				$nN=0;
			}elseif((ord($sString[$nI])&0xE0)==0xC0){
				$nN=1;
			}elseif((ord($sString[$nI])&0xF0)==0xE0){
				$nN=2;
			}elseif((ord($sString[$nI])&0xF0)==0xF0){
				$nN=3;
			}else{
				return FALSE;
			}
			for($nJ=0;$nJ<$nN;$nJ++){
				if((++$nI==$nLength) ||((ord($sString[$nI])&0xC0)!=0x80)){
					return FALSE;
				}
			}
		}
		return TRUE;
	}
	static public function subString($sStr,$nStart=0,$nLength=255,$sCharset="utf-8",$bSuffix=true){
		// 对系统的字符串函数进行判断
		if(function_exists("mb_substr")){
			return mb_substr($sStr,$nStart,$nLength,$sCharset);
		}elseif(function_exists('iconv_substr')){
			return iconv_substr($sStr,$nStart,$nLength,$sCharset);
		}
		// 常用几种字符串正则表达式
		$arrRe['utf-8']="/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
		$arrRe['gb2312']="/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
		$arrRe['gbk']="/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
		$arrRe['big5']="/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
		
		// 匹配
		preg_match_all($arrRe[$sCharset],$sStr,$arrMatch);
		$sSlice=join("",array_slice($arrMatch[0],$nStart,$nLength));
		if($bSuffix){
			return $sSlice."…";
		}
		return $sSlice;
	}
	static public function isSameCallback($CallbackA,$CallbackB){
		if(!is_callable($CallbackA) || is_callable($CallbackB)){
			return false;
		}
		if(is_array($CallbackA)){
			if(is_array($CallbackB)){
				return($CallbackA[0]===$CallbackB[0]) AND (strtolower($CallbackA[1])===strtolower($CallbackB[1]));
			}else{
				return false;
			}
		}else{
			return strtolower($CallbackA)===strtolower($CallbackB);
		}
	}
	static public function isThese($Var,$Types){
		if(!self::varType($Types,'string') && !self::checkArray($Types,array('string'))){
			Q::E(Q::L('正确格式:参数 $Types 必须为 string 或 各项元素为string的数组','__QEEPHP__@Q'));
		}
		if(is_string($Types)){
			$arrTypes=array($Types);
		}else{
			$arrTypes=$Types;
		}
		foreach($arrTypes as $sType){// 类型检查
			if(self::varType($Var,$sType)){
				return true;
			}
		}
		return false;
	}
	static public function isKindOf($SubClass,$sBaseClass){
		if(Q::classExists($sBaseClass,true)){// 接口
			return self::isImplementedTo($SubClass,$sBaseClass);
		}else{// 类
			if(is_object($SubClass)){// 统一类名,如果不是，返回false
				$sSubClassName=get_class($SubClass);
			}elseif(is_string($SubClass)){
				$sSubClassName=&$SubClass;
			}else{
				return false;
			}
			if($sSubClassName==$sBaseClass){// 子类名 即为父类名
				return true;
			}
			$sParClass=get_parent_class($sSubClassName);// 递归检查
			if(!$sParClass){
				return false;
			}
			return self::isKindOf($sParClass,$sBaseClass);
		}
	}
	static public function isImplementedTo($Class,$sInterface,$bStrictly=false){
		if(is_object($Class)){// 尝试获取类名，否则返回false
			$sClassName=get_class($Class);
		}elseif(is_string($Class)){
			$sClassName=&$Class;
		}else{
			return false;
		}
		if(!is_string($sClassName)){// 类型检查
			return false;
		}
		if(!class_exists($sClassName) || !interface_exists($sInterface)){// 检查类和接口是否都有效
			return false;
		}
		// 建立反射
		$oReflectionClass=new ReflectionClass($sClassName);
		$arrInterfaceRefs=$oReflectionClass->getInterfaces();
		foreach($arrInterfaceRefs as $oInterfaceRef){
			if($oInterfaceRef->getName()!=$sInterface){
				continue;
			}
			if(!$bStrictly){// 找到 匹配的 接口
				return true;
			}
			// 依次检查接口中的每个方法是否实现
			$arrInterfaceFuncs=get_class_methods($sInterface);
			foreach($arrInterfaceFuncs as $sFuncName){
				$sReflectionMethod=$oReflectionClass->getMethod($sFuncName);
				if($sReflectionMethod->isAbstract()){// 发现尚为抽象的方法
					return false;
				}
			}
			return true;
		}
		// 递归检查父类
		if(($sParName=get_parent_class($sClassName))!==false){
			return self::isImplementedTo($sParName,$sInterface,$bStrictly);
		}else{
			return false;
		}
	}
	static public function checkArray($arrArray,array $arrTypes){
		if(!is_array($arrArray)){// 不是数组直接返回
			return false;
		}
		// 判断数组内部每一个值是否为给定的类型
		foreach($arrArray as &$Element){
			$bRet=false;
			foreach($arrTypes as $Type){
				if(self::varType($Element,$Type)){
					$bRet=true;
					break;
				}
			}
			if(!$bRet){
				return false;
			}
		}
		return true;
	}
	static public function tidyPath($sPath,$bUnix=true){
		$sRetPath=str_replace('\\','/',$sPath);// 统一 斜线方向
		$sRetPath=preg_replace('|/+|','/',$sRetPath);// 归并连续斜线
		$arrDirs=explode('/',$sRetPath);// 削除 .. 和  .
		$arrDirs2=array();
		while(($sDirName=array_shift($arrDirs))!==null){
			if($sDirName=='.'){
				continue;
			}
			if($sDirName=='..'){
				if(count($arrDirs2)){
					array_pop($arrDirs2);
					continue;
				}
			}
			array_push($arrDirs2,$sDirName);
		}
		$sRetPath=implode('/',$arrDirs2);// 目录 以  '/' 结尾
		if(@is_dir($sRetPath)){// 存在的目录
			if(!preg_match('|/$|',$sRetPath)){
				$sRetPath.= '/';
			}
		}else if(preg_match("|\.$|",$sPath)){// 不存在，但是符合目录的格式
			if(!preg_match('|/$|',$sRetPath)){
				$sRetPath.= '/';
			}
		}
		$sRetPath=str_replace(':/',':\\',$sRetPath);// 还原 驱动器符号
		if(!$bUnix){// 转换到 Windows 斜线风格
			$sRetPath=str_replace('/','\\',$sRetPath);
		}
		$sRetPath=rtrim($sRetPath,'\\/');// 删除结尾的“/”或者“\”
		return $sRetPath;
	}
	static public function dump($Var,$bEcho=true,$sLabel=null,$bStrict=true){
		$SLabel=($sLabel===null)?'':rtrim($sLabel).' ';
		if(!$bStrict){
			if(ini_get('html_errors')){
				$sOutput=print_r($Var,true);
				$sOutput="<pre>".$sLabel.htmlspecialchars($sOutput,ENT_QUOTES)."</pre>";
			}else{
				$sOutput=$sLabel." : ".print_r($Var,true);
			}
		}else{
			ob_start();
			var_dump($Var);
			$sOutput=ob_get_clean();
			if(!extension_loaded('xdebug')){
				$sOutput=preg_replace("/\]\=\>\n(\s+)/m","] => ",$sOutput);
				$sOutput='<pre>'.$sLabel.htmlspecialchars($sOutput,ENT_QUOTES).'</pre>';
			}
		}
		if($bEcho){
			echo $sOutput;
			return null;
		}else{
			return $sOutput;
		}
	}
	static public function makeDir($Dir,$nMode=0777){
		if(is_dir($Dir)){
			return true;
		}
		if(is_string($Dir)){
			$arrDirs=explode('/',str_replace('\\','/',trim($Dir,'/')));
		}else{
			$arrDirs=$Dir;
		}
		$sMakeDir=IS_WIN?'':'/';
		foreach($arrDirs as $nKey=>$sDir){
			$sMakeDir.=$sDir.'/';
			if(!is_dir($sMakeDir)){
				if(isset($arrDirs[$nKey+1]) && is_dir($sMakeDir.$arrDirs[$nKey+1])){
					continue;
				}
				@mkdir($sMakeDir,$nMode);
			}
		}
		return TRUE;
	}
	static public function changeFileSize($nFileSize){
		if($nFileSize>=1073741824){
			$nFileSize=round($nFileSize/1073741824,2).'GB';
		}elseif($nFileSize>=1048576){
			$nFileSize=round($nFileSize/1048576,2).'MB';
		}elseif($nFileSize>=1024){
			$nFileSize=round($nFileSize/1024,2).'KB';
		}else{
			$nFileSize=$nFileSize.Q::L('字节','__QEEPHP__@qeephp');
		}
		return $nFileSize;
	}
	static public function getMicrotime(){
		list($nM1,$nM2)=explode(' ',microtime());
		return((float)$nM1+(float)$nM2);
	}
	static public function oneImensionArray($arrArray){
		return count($arrArray)==count($arrArray,1);
	}
	static public function getIp(){
		static $sRealip=NULL;
		if($sRealip !== NULL){
			return $sRealip;
		}
		if(isset($_SERVER)){
			if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
				$arrValue=explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
					foreach($arrValue AS $sIp){// 取X-Forwarded-For中第一个非unknown的有效IP字符串
						$sIp=trim($sIp);
						if($sIp!='unknown'){
							$sRealip=$sIp;
							break;
						}
					}
				}elseif(isset($_SERVER['HTTP_CLIENT_IP'])){
					$sRealip=$_SERVER['HTTP_CLIENT_IP'];
				}else{
					if(isset($_SERVER['REMOTE_ADDR'])){
						$sRealip=$_SERVER['REMOTE_ADDR'];
					}else{
						$sRealip='0.0.0.0';
					}
				}
			}else{
				if(getenv('HTTP_X_FORWARDED_FOR')){
					$sRealip=getenv('HTTP_X_FORWARDED_FOR');
				}elseif(getenv('HTTP_CLIENT_IP')){
					$sRealip=getenv('HTTP_CLIENT_IP');
				}else{
					$sRealip=getenv('REMOTE_ADDR');
				}
			}
			preg_match("/[\d\.]{7,15}/",$sRealip,$arrOnlineip);
			$sRealip=!empty($arrOnlineip[0])?$arrOnlineip[0]:'0.0.0.0';
			return $sRealip;
	}
	static public function authcode($string,$operation=TRUE,$key=null,$expiry=0){
		$ckey_length=4;
		$key=md5($key?$key:$GLOBALS['_commonConfig_']['QEEPHP_AUTH_KEY']);
		$keya=md5(substr($key,0,16));
		$keyb=md5(substr($key,16,16));
		$keyc=$ckey_length?($operation===TRUE?substr($string,0,$ckey_length):substr(md5(microtime()),-$ckey_length)):'';
		$cryptkey=$keya.md5($keya.$keyc);
		$key_length=strlen($cryptkey);
		$string=$operation===TRUE?base64_decode(substr($string, $ckey_length)):sprintf('%010d',$expiry?$expiry+time():0).substr(md5($string.$keyb),0,16).$string;
		$string_length=strlen($string);
		$result='';
		$box=range(0,255);
		$rndkey=array();
		for($i=0;$i<=255;$i++){
			$rndkey[$i]=ord($cryptkey[$i%$key_length]);
		}
		for($j=$i=0;$i<256;$i++){
			$j=($j+$box[$i]+$rndkey[$i])%256;
			$tmp=$box[$i];
			$box[$i]=$box[$j];
			$box[$j]=$tmp;
		}
		for($a=$j=$i=0;$i<$string_length;$i++){
			$a=($a+1)%256;
			$j=($j+$box[$a])%256;
			$tmp=$box[$a];
			$box[$a]=$box[$j];
			$box[$j]=$tmp;
			$result.=chr(ord($string[$i])^($box[($box[$a]+$box[$j])%256]));
		}
		if($operation===TRUE){
			if((substr($result,0,10)==0 || substr($result,0,10)-time()>0) && substr($result,10,16)==substr(md5(substr($result,26).$keyb),0,16)){
				return substr($result,26);
			}else{
				return '';
			}
		}else{
			return $keyc.str_replace('=','',base64_encode($result));
		}
	}
	static public function returnBytes($sVal){
		$sVal=trim($sVal);
		$sLast=strtolower($sVal{strlen($sVal)-1});
		switch($sLast){
			case 'g':
				$sVal*=1024*1024*1024;
			case 'm':
				$sVal*=1024*1024;
			case 'k':
				$sVal*=1024;
		}
		return $sVal;
	}
	static public function listDir($sDir,$bFullPath=FALSE,$bReturnFile=FALSE){
		if(is_dir($sDir)){
			$arrFiles=array();
			$hDir=opendir($sDir);
			while(($sFile=readdir($hDir))!==FALSE){
				if($bReturnFile===FALSE){
					if((is_dir($sDir."/".$sFile)) && $sFile!="." && $sFile!=".." && $sFile!='_svn'){
						if($bFullPath===TRUE){
							$arrFiles[]=$sDir."/".$sFile;
						}else{
							$arrFiles[]=$sFile;
						}
					}
				}else{
					if((is_file($sDir."/".$sFile)) && $sFile!="." && $sFile!=".."){
						if($bFullPath===TRUE){
							$arrFiles[]=$sDir."/".$sFile;
						}else{
							$arrFiles[]=$sFile;
						}
					}
				}
			}
			closedir($hDir);
			return $arrFiles;
		}else{
			return false;
		}
	}
	public static function hasStaticMethod($sClassName,$sMethodName){
		$oRef=new ReflectionClass($sClassName);
		if($oRef->hasMethod($sMethodName) and $oRef->getMethod($sMethodName)->isStatic()){
			return true;
		}
		return false;
	}
	static public function getAvatar($nUid,$sSize='middle'){
		$sSize=in_array($sSize,array('big','middle','small'))?$sSize:'middle';
		$arrDir=self::getDisDir($nUid);
		return $arrDir[0].$arrDir[1]."_avatar_{$sSize}.jpg";
	}
	static public function getDisDir($nUid){
		$nUid=abs(intval($nUid));
		$nUid=sprintf("%09d",$nUid);
		$nDir1=substr($nUid,0,3);
		$nDir2=substr($nUid,3,2);
		$nDir3=substr($nUid,5,2);
		return array($nDir1.'/'.$nDir2.'/'.$nDir3.'/',substr($nUid,-2));
	}
	static public function getExtName($sFileName,$nCase=0){
		if(!preg_match('/\./',$sFileName)){
			return '';
		}
		$arr=explode('.',$sFileName);
		$sExtName=end($arr);
		if($nCase==1){
			return strtoupper($sExtName);
		}elseif($nCase==2){
			return strtolower($sExtName);
		}else{
			return $sExtName;
		}
	}
	static public function cleanJs($sText){
		$sText=trim($sText);
		$sText=stripslashes($sText);
		$sText=preg_replace('/<!--?.*-->/','',$sText);// 完全过滤注释
		$sText=preg_replace('/<\?|\?>/','',$sText);// 完全过滤动态代码
		$sText=preg_replace('/<script?.*\/script>/','',$sText);// 完全过滤js
		$sText=preg_replace('/<\/?(html|head|meta|link|base|body|title|style|script|form|iframe|frame|frameset)[^><]*>/i','',$sText);// 过滤多余html
		while(preg_match('/(<[^><]+)(lang|onfinish|onmouse|onexit|onerror|onclick|onkey|onload|onchange|onfocus|onblur)[^><]+/i',$sText,$arrMat)){//过滤on事件lang js
			$sText=str_replace($arrMat[0],$arrMat[1],$sText);
		}
		while(preg_match('/(<[^><]+)(window\.|javascript:|js:|about:|file:|document\.|vbs:|cookie)([^><]*)/i',$sText,$arrMat)){
			$sText=str_replace($arrMat[0],$arrMat[1].$arrMat[3],$sText);
		}
		return $sText;
	}
	static function text($sText){
		$sText=self::cleanJs($sText);
		$sText=preg_replace('/\s(?=\s)/','',$sText);// 彻底过滤空格
		$sText=preg_replace('/[\n\r\t]/',' ',$sText);
		$sText=str_replace('  ',' ',$sText);
		$sText=str_replace(' ','',$sText);
		$sText=str_replace('&nbsp;','',$sText);
		$sText=str_replace('&','',$sText);
		$sText=str_replace('=','',$sText);
		$sText=str_replace('-','',$sText);
		$sText=str_replace('#','',$sText);
		$sText=str_replace('%','',$sText);
		$sText=str_replace('!','',$sText);
		$sText=str_replace('@','',$sText);
		$sText=str_replace('^','',$sText);
		$sText=str_replace('*','',$sText);
		$sText=str_replace('amp;','',$sText);
		$sText=strip_tags($sText);
		$sText=htmlspecialchars($sText);
		$sText=str_replace("'","",$sText);
		return $sText;
	}
	static public function strip($sText){
		$sText=trim($sText);
		$sText=self::cleanJs($sText);
		$sText=strip_tags($sText);
		return $sText;
	}
	static public function html($sText){
		$sText=trim($sText);
		$sText=htmlspecialchars($sText);
		return $sText;
	}
	static public function htmlView($sText){
		$sText=stripslashes($sText);
		$sText=nl2br($sText);
		return $sText;
	}
	static public function xmlEncode($arrData=array()){
		return Xml::xmlSerialize($arrData);
	}
	static public function isAjax(){
		if(isset($_SERVER['HTTP_X_REQUESTED_WITH'])){
			if('xmlhttprequest'==strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])){
				return true;
			}
		}
		if(!empty($_POST['ajax']) || !empty($_GET['ajax'])){
			return true;
		}
		return false;
	}
	static public function isSsl(){
		if(isset($_SERVER['HTTPS']) && ('1'==$_SERVER['HTTPS'] || 'on'==strtolower($_SERVER['HTTPS']))){
			return true;
		}elseif(isset($_SERVER['SERVER_PORT']) && ('443'==$_SERVER['SERVER_PORT'])){
			return true;
		}
		return false;
	}
	static public function getCurrentUrl(){
		return (C::isSsl()?'https://':'http://').$_SERVER['HTTP_HOST'].__SELF__;
	}
	static function closeTags($sHtml){
		$arrSingleTags=array('meta','img','br','link','area');
		
		preg_match_all('#<([a-z]+)(?:.*)?(?<![/|/ ])>#iU',$sHtml,$arrResult);
		$arrOpenedtags=$arrResult[1];
		preg_match_all('#</([a-z]+)>#iU',$sHtml,$arrResult); 
		$arrClosedtags=$arrResult[1];
		$nLenOpened=count($arrOpenedtags);
		if(count($arrClosedtags)==$nLenOpened){
			return $sHtml;
		}
		$arrOpenedtags=array_reverse($arrOpenedtags);
		for($nI=0;$nI<$nLenOpened;$nI++){
			if(!in_array($arrOpenedtags[$nI],$arrSingleTags)){
				if(!in_array($arrOpenedtags[$nI],$arrClosedtags)){
					$sHtml.='</'.$arrOpenedtags[$nI].'>';
				}else{
					unset($arrClosedtags[array_search($arrOpenedtags[$nI],$arrClosedtags)]);
				}
			}
		}
		return $sHtml;
	}
}
/** 兼容老版本 */
class C extends Common{}
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   初始化基本配置($$)*/
if(!is_file(APP_RUNTIME_PATH.'/Config.php')){
	require(Q_PATH.'/Common_/AppConfig.inc.php');
}
$GLOBALS['_commonConfig_']=Q::C((array)(include APP_RUNTIME_PATH.'/Config.php'));
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   语言包管理类（Learn JC!）($$)*/
class LangPackage{
	private $_sPackageName='';
	private $_sLangName='';
	private $_sPackagePath='';
	public $LANGS=array();
	private $_bNeedUpdated=false;
	private $_nUpdateTime=0;
	static private $LANG_PACKAGES=array();
	static private $_sHistoryPath=null;
	static public function getPackage($sLangName,$sPackageName){
		if(isset(self::$LANG_PACKAGES[$sLangName][$sPackageName])){// 直接返回已经被创建的享员对象
			return self::$LANG_PACKAGES[$sLangName][$sPackageName];
		}else{// 创建对象
			$sPackageNameOld=$sPackageName;
			
			// 分析语言包路径
			if(strpos($sPackageName,'@')!==false){
				$arrValues=explode('@',$sPackageName);
				if($arrValues[0]==='__QEEPHP__'){
					$sDir=Q_PATH.'/Resource_/Lang';
				}elseif($arrValues[0]==='__APP__'){
					$sDir=APP_LANG_PATH;
				}else{
					$bDefined=false;
					eval('$bDefined=defined(\''.$arrValues[0].'\');');
					if($bDefined){
						eval('$sDir='.$arrValues[0].';');
					}else{
						$sDir=$arrValues[0];
					}
				}
				$sPackageName=$arrValues[1];
			}else{
				$sDir=APP_LANG_PATH;
			}
			$sThePackagePath=self::findPackage($sDir,$sLangName,$sPackageName);
			if(!($oThePackage=self::hasPackage($sLangName,$sPackageNameOld))){
				$oThePackage=self::createPackage($sLangName,$sPackageNameOld,$sThePackagePath);
			}
			if($sThePackagePath){
				$oThePackage->loadPackageFile($sThePackagePath);
			}else{
				E('We can not find a lang file:<br/>'.self::$_sHistoryPath);
				exit();
			}
			return $oThePackage;
		}
	}
	static private function findPackage($sDir,$sLangName,$sPackageName=null){
		$sDir=ucfirst($sDir);
		$sLangName=ucfirst($sLangName);
		$sPackageName=ucfirst($sPackageName);
		$sPath="{$sDir}/{$sLangName}/{$sPackageName}.lang.php";
		if(is_file($sPath)){
			return $sPath;
		}elseif(is_file("{$sDir}/Zh-cn/{$sPackageName}.lang.php")){// 尝试从默认语言环境中加载
			return "{$sDir}/Zh-cn/{$sPackageName}.lang.php";
		}
		self::$_sHistoryPath=$sPath;
		return null;
	}
	public function loadPackageFile($sPackagePath){
		if(!is_file($sPackagePath)){
			E(sprintf('PackagePath [ %s ] is not exists',self::$_sHistoryPath));
		}
		$this->LANGS=array_merge($this->LANGS,(array)(include $sPackagePath));
		if($this->_nUpdateTime<filemtime($sPackagePath)){// 更新语言包的时间
			$this->_nUpdateTime=filemtime($sPackagePath);
		}
	}
	static private function createPackage($sLangName,$sPackageName,$sPackagePath){
		if(isset(self::$LANG_PACKAGES[$sLangName][$sPackageName])){
			return self::$LANG_PACKAGES[$sLangName][$sPackageName];
		}
		return self::$LANG_PACKAGES[$sLangName][$sPackageName]=new self($sLangName,$sPackageName,$sPackagePath);
	}
	static private function hasPackage($sLangName,$sPackageName){
		if(isset(self::$LANG_PACKAGES[$sLangName][$sPackageName])){
			return self::$LANG_PACKAGES[$sLangName][$sPackageName];
		}else{
			return false;
		}
	}
	private function __construct($sLangName,$sPackageName,$sPackagePath){
		$this->_sPackagePath=$sPackagePath;
		$this->_sPackageName=$sPackageName;
		$this->_sLangName=$sLangName;
		$this->load();
	}
	public function __destruct(){
		if($this->isUpdated()){
			$this->save();
		}
	}
	public function load(){
		$this->LANGS=array();
		$this->loadPackageFile($this->_sPackagePath);
	}
	public function save(){
		$sOut="<?php\r\n";
		$sOut.="/** QeePHP Lang File, Do not to modify it! */\r\n";
		$sOut.="return array(\r\n";
		foreach($this->LANGS as $sKey=>$sValue){
			$sKey=strtolower($sKey);
			$sValue=$this->filterOptionValue($sValue);
			$sOut.="'{$sKey}'=>$sValue,\r\n";
		}
		$sOut.=")\r\n";
		$sOut.="\r\n?>";
		if(!file_put_contents($this->_sPackagePath,$sOut)){
			E('Configuration write failed system language,if your sever is Linux hosts ,set permissions to 0777 ,the path is'.$this->_sPackagePath);
		}
	}
	private function filterOptionValue($sValue){
		if($sValue===false){
			return 'FALSE';
		}
		if($sValue===true){
			return 'TRUE';
		}
		if($sValue==''){
			return '""';
		}
		$sValue=str_replace('"','\\"',$sValue);
		$sValue=str_replace("\n","\\n",$sValue);
		$sValue=str_replace("\r","\\r",$sValue);
		$sValue=str_replace('$','\\$',$sValue);
		return '"'.$sValue.'"';
	}
	public function getName(){
		return $this->_sPackageName;
	}
	public function getLangName(){
		return $this->_sLanguageName;
	}
	public function set($sKey,$sValue){
		if($this->get($sKey)==$sValue){
			return;
		}
		$this->LANGS[$sKey]=$sValue;
		$this->_bNeedUpdated=true;
	}
	public function get($sKey){
		if($this->has($sKey)){
			return $this->LANGS[$sKey];
		}else{
			return null;
		}
	}
	public function has($sKey){
		return isset($this->LANGS[$sKey]);
	}
	public function isUpdated(){
		return $this->_bNeedUpdated;
	}
	public function getUpdateTime(){
		return $this->_nUpdateTime;
	}
}
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   语言管理类（Learn JC!）($$)*/
class Lang{
	const CURRENT_LANGUAGE=null;
	private $_sLangName;
	private $_oCurrentPackage=null;
	static private $LANG_INSES;
	static private $_oCurrentLang;
	private function __construct($sLangName){
		$this->_sLangName=$sLangName;
	}
	static public function getLang($sLangName){
		if(isset(self::$LANG_INSES[$sLangName])){
			return self::$LANG_INSES[$sLangName];
		}
		$oLang=new Lang($sLangName);
		self::$LANG_INSES[$sLangName]=$oLang;
		if(!self::$_oCurrentLang){// 若无当前语言实例,自动设置为当前语言实例
			self::$_oCurrentLang=$oLang;
		}
		return $oLang;
	}
	static public function setCurrentLang($Lang){
		$oOldValue=self::getCurrentLang();
		if(is_string($Lang)){
			self::$_oCurrentLang=self::getLang($Lang);
		}elseif($Lang instanceof Lang){
			self::$_oCurrentLang=$Lang;
		}else{
			E('Parameters $Lang must be a name(String type), the language object ever have be created,or null(the current language pack)');
		}
		return $oOldValue;
	}
	static public function getCurrentLang(){
		return self::$_oCurrentLang;
	}
	public function getLangName(){
		return $this->_sLangName;
	}
	public function getPackage($sPackageName){
		$oThePackage=LangPackage::getPackage($this->getLangName(),$sPackageName);
		if(!$oThePackage){
			E('Can not find the language pack according to the parameters \$sPackageName({$sPackageName}.');
		}
		if(!$this->getCurrentPackage()){
			$this->setCurrentPackage($oThePackage);
		}
		return $oThePackage;
	}
	public function getCurrentPackage(){
		return $this->_oCurrentPackage;
	}
	public function setCurrentPackage($Package){
		$oOldValue=$this->getCurrentPackage();
		if(is_string($Package)){
			$this->_oCurrentPackage=$this->getPackage($Package);
		}elseif($Package instanceof LangPackage){
			$this->_oCurrentPackage=$Package;
		}else{
			E('Parameters $Packaqe must be a language  name(String type), the language pack object ever have be created,or null(the current language pack)');
		}
		
		return $oOldValue;
	}
	static public function makeValueKey($sValue){
		return md5($sValue);
	}
	static public function setEx($sValue,$Package=null,$Lang=null/*Argvs*/){
		$sKey=self::makeValueKey($sValue);
		// 取得语言享员对象
		if(is_string($Lang)){
			$oTheLang=self::getLang($Lang);
		}elseif(is_object($Lang)){
			$oTheLang=$Lang;
		}elseif($Lang===null){
			$oTheLang=self::getCurrentLang();
			if(!$oTheLang){
				E('Not specify the current language ,triggering an exception!');
			}
		}
		
		// 取得语言包
		if(is_string($Package)){
			$oThePackage=$oTheLang->getPackage($Package);
		}elseif(is_object($Package)){
			$oThePackage=$Package;
		}elseif($Package===null){
			$oThePackage=$oTheLang->getCurrentPackage();
			if(!$oThePackage){
				E('Not specify the current language ,triggering anexception !');
			}
		}
		// 语句存在
		if($oThePackage->has($sKey)){
			$sReallyValue=$oThePackage->get($sKey);
		}else{
			$sReallyValue=$sValue;
			$oThePackage->set($sKey,$sReallyValue);
		}
		if(func_num_args()>3){// 代入参数
			$arrArgs=func_get_args();
			$arrArgs[0]=$sReallyValue;
			unset($arrArgs[1],$arrArgs[2]);
			$sReallyValue=call_user_func_array('sprintf',$arrArgs);
		}
		return $sReallyValue;
	}
}
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   全局控制器($$)*/
class App{
	static private function init_(){
		// 移除自动转义
		C::stripslashesMagicquotegpc();
		if(isset($_REQUEST['GLOBALS']) OR isset($_FILES['GLOBALS'])){
			E('GLOBALS not allowed!');
		}
		// 载入QeePHP框架
		Q::import(Q_PATH);
		// 载入应用
		Q::import(APP_PATH.'/App/Class');
		// 初始化时区和GZIP压缩
		if(function_exists('date_default_timezone_set')){
			date_default_timezone_set($GLOBALS['_commonConfig_']['TIME_ZONE']);
		}
		if($GLOBALS['_commonConfig_']['START_GZIP'] && function_exists('gz_handler')){
			ob_start('gz_handler');
		}else{
			ob_start();
		}
		// 解析系统URL
		$oUrl=new Url();
		$oUrl->parseUrl();
		// 检查语言包和模板以及定义系统常量
		self::checkTemplate();
		self::checkLanguage();
 		self::constantDefine();
		// 载入项目初始化文件
		if(!defined('APP_INIT_PATH')){
			define('APP_INIT_PATH',APP_PATH.'/App/QeePHP.php');
		}
		if(file_exists(APP_INIT_PATH)){
			require(APP_INIT_PATH);
		}
		return;
	}
	static public function RUN(){
		self::init_();
		self::execute();
		if($GLOBALS['_commonConfig_']['LOG_RECORD']){
			Log::S();
		}
		return;
	}
	static public function execute($sModule=MODULE_NAME,$sAction=ACTION_NAME){
		// 读取模块资源
		$sModule=ucfirst($sModule)."Controller";
		if(Q::classExists($sModule,false,true)){
			$oModule=new $sModule();
		}else{
			Q::E(Q::L('模块%s 不存在','__QEEPHP__@Q',null,$sModule));
		}
		// 执行控制器公用初始化函数
		if(method_exists($oModule,'init__')){
			call_user_func(array($oModule,'init__'));
		}
		// 执行控制器方法
		if(method_exists($oModule,'b'.ucfirst($sAction).'_')){
			call_user_func(array($oModule,'b'.ucfirst($sAction).'_'));
		}
		if(method_exists($oModule,$sAction)){
			call_user_func(array($oModule,$sAction));
		}else{
			Q::E(Q::L('模块%s 不存在的方法%s 不存在','__QEEPHP__@Q',null,$sModule,$sAction));
		}
		if(method_exists($oModule,'a'.ucfirst($sAction).'_')){
			call_user_func(array($oModule,'a'.ucwords($sAction).'_'));
		}
	}
	static private function checkTemplate(){
		if(!defined('APP_TEMPLATE_PATH')){
			define('APP_TEMPLATE_PATH',APP_PATH.'/Theme');
		}
		if($GLOBALS['_commonConfig_']['COOKIE_LANG_TEMPLATE_INCLUDE_APPNAME']===TRUE){
			$sCookieName=APP_NAME.'_template';
		}else{
			$sCookieName='template';
		}
		if(!$GLOBALS['_commonConfig_']['THEME_SWITCH']){
			$sTemplateSet=ucfirst(strtolower($GLOBALS['_commonConfig_']['TPL_DIR']));
		}elseif(isset($_GET['t'])){
			$sTemplateSet=ucfirst(strtolower($_GET['t']));
		}else{
			if(Q::cookie($sCookieName)){
				$sTemplateSet=Q::cookie($sCookieName);
			}else{
				$sTemplateSet=ucfirst(strtolower($GLOBALS['_commonConfig_']['TPL_DIR']));
			}
		}
		Q::cookie($sCookieName,$sTemplateSet);
		define('TEMPLATE_NAME',$sTemplateSet);
		define('TEMPLATE_PATH',APP_TEMPLATE_PATH.'/'.TEMPLATE_NAME);
		define('TEMPLATE_PATH_DEFAULT',APP_TEMPLATE_PATH.'/Default');
		if(!is_dir(TEMPLATE_PATH)){
			$sTemplatePath=APP_TEMPLATE_PATH.'/Default';
		}else{
			$sTemplatePath=TEMPLATE_PATH;
		}
		Template::setTemplateDir($sTemplatePath);
		return;
	}
	static private function checkLanguage(){
		if(!defined('APP_LANG_PATH')){
			define('APP_LANG_PATH',APP_PATH.'/App/Lang');
		}
		if($GLOBALS['_commonConfig_']['COOKIE_LANG_TEMPLATE_INCLUDE_APPNAME']===TRUE){
			$sCookieName=APP_NAME.'_language';
		}else{
			$sCookieName='language';
		}
		if(!$GLOBALS['_commonConfig_']['LANG_SWITCH']){
			$sLangSet=ucfirst(strtolower($GLOBALS['_commonConfig_']['LANG']));
		}elseif(isset($_GET['l'])){
			$sLangSet=ucfirst(strtolower($_GET['l']));
		}elseif($sCookieName){
			$sLangSet=Q::cookie($sCookieName);
			if(empty($sLangSet)){
				$sLangSet=ucfirst(strtolower($GLOBALS['_commonConfig_']['LANG']));
			}
		}elseif($GLOBALS['_commonConfig_']['AUTO_ACCEPT_LANGUAGE'] && isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
			preg_match('/^([a-z\-]+)/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'],$arrMatches);
			$sLangSet=ucfirst(strtolower($arrMatches[1]));
		}else{
			$sLangSet=ucfirst(strtolower($GLOBALS['_commonConfig_']['LANG']));
		}
		Q::cookie($sCookieName,$sLangSet);
		define('LANG_NAME',$sLangSet);
		Lang::setCurrentLang($sLangSet);
		define('LANG_PATH',APP_LANG_PATH.'/'.LANG_NAME);
		define('LANG_PATH_DEFAULT',APP_LANG_PATH.'/'.ucfirst(strtolower($GLOBALS['_commonConfig_']['LANG'])));
		return;
	}
	static private function constantDefine(){
		define('__ENTER__',basename(__APP__));
		// 项目入口公用静态资源目录(也叫做公共目录)
		if(defined('__STATICS__')){
			define('__APPPUB__',__ROOT__.'/'.__STATICS__);
		}else{
			define('__APPPUB__',__ROOT__.'/'.APP_NAME.'/Static');
		}
		// 模板目录
		if(defined('__THEMES__')){
			define('__THEME__',__ROOT__.'/'.__THEMES__);
		}else{
			define('__THEME__',__ROOT__.'/'.APP_NAME.'/Theme');
		}
		// 项目资源目录
		define('__TMPL__',__THEME__.'/'.TEMPLATE_NAME);
		define('__TMPL_DEFAULT__',__THEME__.'/Default');
		// 网站公共文件目录
		if(defined('__PUBLICS__')){
			define('__PUBLIC__',__ROOT__.'/'.__PUBLICS__);
		}else{
			define('__PUBLIC__',__ROOT__.'/Public');
		}
		// 项目公共文件目录
		define('__TMPLPUB__',__TMPL__.'/Public');
		define('__TMPLPUB_DEFAULT__',__TMPL_DEFAULT__.'/Public');
		// 框架一个特殊的模块定义
		define('MODULE_NAME2',$GLOBALS['_commonConfig_']['TMPL_MODULE_ACTION_DEPR']=='/' && MODULE_NAME==='public'?'Public':MODULE_NAME);
		// 当前文件路径
		define('__TMPL_FILE_NAME__',__TMPL__.'/'.MODULE_NAME2.$GLOBALS['_commonConfig_']['TMPL_MODULE_ACTION_DEPR'].
			ACTION_NAME.$GLOBALS['_commonConfig_']['TEMPLATE_SUFFIX']
		);
		define('__TMPL_FILE_PATH__',TEMPLATE_PATH.'/'.MODULE_NAME2.$GLOBALS['_commonConfig_']['TMPL_MODULE_ACTION_DEPR'].
			ACTION_NAME.$GLOBALS['_commonConfig_']['TEMPLATE_SUFFIX']
		);
	}
	static public function U(){
		return "var _ROOT_='".__ROOT__."',_SELF_='".__SELF__."',_MODULE_NAME_='".MODULE_NAME."',_ACTION_NAME_='".ACTION_NAME."',_APP_NAME_ ='".APP_NAME."',_ENTER_ ='".__ENTER__.
			"',_APP_VAR_NAME_='app',_CONTROL_VAR_NAME_='c',_ACTION_VAR_NAME_='a',_URL_HTML_SUFFIX_='".
			$GLOBALS['_commonConfig_']['URL_HTML_SUFFIX']."',_LANG_NAME_='".LANG_NAME."';";
	}
}
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   全局数据验证器（Learn QP!）($$)*/
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
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   控制器($$)*/
class Controller{
	protected $_oView=null;
	public $_bIsError=false;
	public $_oParent=null;
	public function __construct(){
		$this->_oView=new View($this);
	}
	public function init__(){}
	public function assign($Name,$Value=''){
		$this->_oView->assign($Name,$Value);
	}
	public function __set($Name,$Value){
		$this->assign($Name,$Value);
	}
	public function get($sName){
		$sValue=$this->_oView->get($sName);
		return $sValue;
	}
	public function &__get($sName){
		$value=$this->get($sName);
		return $value;
	}
	public function display($sTemplateFile='',$sCharset='utf-8',$sContentType='text/html',$bReturn=false){
		return $this->_oView->display($sTemplateFile,$sCharset,$sContentType,$bReturn);
	}
	public function child($sPath,$sAction){
		$sFile=$this->includeController($sPath,$sClassController);
		require_once($sFile);
		$sClassController=str_replace('Controller','_C_Controller',$sClassController);
		$oClass=new $sClassController($this);
		$oClass->{$sAction}();
	}
	
	protected function includeController($sPath,&$sClassController){
		$sFilepath=APP_PATH.'/App/Class/Controller/';
		$arrValue=explode('@',$sPath);
		if(!isset($arrValue[1])){
			Q::E('IncludeController parameter is error');
		}
		$sFilepath.=$arrValue[0].'_/';
		$arrValue=explode('/',$arrValue[1]);
		$sClassController=array_pop($arrValue).'Controller';
		$sClass=$sClassController.'_.php';
		$sFilepath.=($arrValue?implode('/',$arrValue).'/':'').$sClass;
		if(!is_file($sFilepath)){
			Q::E(sprintf('Include Controller %s failed',$sFilepath));
		}
		return $sFilepath;
	}
	protected function G($sName,$sViewName=null){
		$value=$this->_oView->getVar($sName);
		return $value;
	}
	protected function E($sMessage='',$nDisplay=3,$bAjax=FALSE){
		$this->J($sMessage,0,$nDisplay,$bAjax);
	}
	protected function S($sMessage,$nDisplay=1,$bAjax=FALSE){
		$this->J($sMessage,1,$nDisplay,$bAjax);
	}
	protected function A($Data,$sInfo='',$nStatus=1,$nDisplay=1,$sType=''){
		$arrResult=array();
		$arrResult['status']=$nStatus;
		$arrResult['display']=$nDisplay;
		$arrResult['info']=$sInfo?$sInfo:Q::L('Ajax未指定返回消息','__QEEPHP__@Q');
		$arrResult['data']=$Data;
		if(empty($sType)){
			$sType=$GLOBALS['_commonConfig_']['DEFAULT_AJAX_RETURN'];
		}
		$arrResult['type']=$sType;
		if(strtoupper($sType)=='JSON'){// 返回JSON数据格式到客户端 包含状态信息
			header("Content-Type:text/html; charset=utf-8");
			exit(json_encode($arrResult));
		}elseif(strtoupper($sType)=='XML'){// 返回xml格式数据
			header("Content-Type:text/xml; charset=utf-8");
			exit(C::xmlEncode($arrResult));
		}elseif(strtoupper($sType)=='EVAL'){// 返回可执行的js脚本
			header("Content-Type:text/html; charset=utf-8");
			exit($Data);
		}else{}
	}
	protected function U($sUrl,$arrParams=array(),$nDelay=0,$sMsg=''){
		$sUrl=Q::U($sUrl,$arrParams);
		C::urlGo($sUrl,$nDelay,$sMsg);
	}
	public function __call($sMethod,$arrArgs){
		switch($sMethod){
			case 'isPost':
			case 'isGet':
				return strtolower($_SERVER['REQUEST_METHOD'])==strtolower(substr($sMethod,2));
			case 'q':
				if(!empty($arrArgs[0])){
					return Q::G($arrArgs[0],isset($arrArgs[1])?$arrArgs[1]:'R');
				}else{
					Q::E('Can not find method.');
				}
			default:
				Q::E('Can not find method.');
		}
	}
	private function J($sMessage,$nStatus=1,$nDisplay=1,$bAjax=FALSE){
		if($nStatus==1){
			$this->_bIsError=false;
		}else{
			$this->_bIsError=true;
		}
		
		// 判断是否为AJAX返回
		if($bAjax || C::isAjax()){
			$this->A('',$sMessage,$nStatus,$nDisplay);
		}
		// 提示标题
		if(!$this->G('__MessageTitle__')){
			$this->assign('__MessageTitle__',$nStatus?Q::L('操作成功','__QEEPHP__@Q'):Q::L('操作失败','__QEEPHP__@Q'));
		}
		// 关闭窗口
		if($this->G('__CloseWindow__')){
			$this->assign('__JumpUrl__','javascript:window.close();');
		}
		// 消息图片
		if(defined('__MESSAGE_IMG_PATH__')){
			$arrMessageImg=array(
				'loader'=>__MESSAGE_IMG_PATH__.'/loader.gif',
				'infobig'=>__MESSAGE_IMG_PATH__.'/info_big.gif',
				'errorbig'=>__MESSAGE_IMG_PATH__.'/error_big.gif'
			);
		}else{
			$arrMessageImg=array(
				'loader'=>'Public/Images/loader.gif',
				'infobig'=>'Public/Images/info_big.gif',
				'errorbig'=>'Public/Images/error_big.gif'
			);
			foreach($arrMessageImg as $sKey=>$sMessageImg){
				$arrMessageImg[$sKey]=is_file(TEMPLATE_PATH.'/'.$arrMessageImg[$sKey])?__TMPL__.'/'.$arrMessageImg[$sKey]:__THEME__.'/Default/'.$arrMessageImg[$sKey];
			}
		}
		$this->assign('__LoadingImg__',$arrMessageImg['loader']);
		$this->assign('__InfobigImg__',$arrMessageImg['infobig']);
		$this->assign('__ErrorbigImg__',$arrMessageImg['errorbig']);
		// 状态
		$this->assign('__Status__',$nStatus);
		if($nStatus){
			$this->assign('__Message__',$sMessage);// 提示信息
		}else{
			$this->assign('__ErrorMessage__',$sMessage);
		}
		$arrInit=array();
		if($nStatus){
			if(!$this->G('__WaitSecond__')){// 成功操作后默认停留1秒
				$this->assign('__WaitSecond__',1);
				$arrInit['__WaitSecond__']=1;
			}else{
				$arrInit['__WaitSecond__']=$this->G('__WaitSecond__');
			}
			if(!$this->G('__JumpUrl__')){// 默认操作成功自动返回操作前页面
				$this->assign('__JumpUrl__',isset($_SERVER["HTTP_REFERER"])?$_SERVER["HTTP_REFERER"]:'');
				$arrInit['__JumpUrl__']=isset($_SERVER["HTTP_REFERER"])? $_SERVER["HTTP_REFERER"]:'';
			}else{
				$arrInit['__JumpUrl__']=$this->G('__JumpUrl__');
			}
			$sJavaScript=$this->javascriptR($arrInit);
			$this->assign('__JavaScript__',$sJavaScript);
			$sTemplate=strpos($GLOBALS['_commonConfig_']['TMPL_ACTION_SUCCESS'],'public+')===0 && $GLOBALS['_commonConfig_']['TMPL_MODULE_ACTION_DEPR']=='/'?
				str_replace('public+','Public+',$GLOBALS['_commonConfig_']['TMPL_ACTION_SUCCESS']):
				$GLOBALS['_commonConfig_']['TMPL_ACTION_SUCCESS'];
			$this->display($sTemplate);
		}else{
			if(!$this->G('__WaitSecond__')){// 发生错误时候默认停留3秒
				$this->assign('__WaitSecond__',3);
				$arrInit['__WaitSecond__']=3;
			}else{
				$arrInit['__WaitSecond__']=$this->G('__WaitSecond__');
			}
			if(!$this->G('__JumpUrl__')){// 默认发生错误的话自动返回上页
				if(preg_match('/(mozilla|m3gate|winwap|openwave)/i', $_SERVER['HTTP_USER_AGENT'])){
					$this->assign('__JumpUrl__','javascript:history.back(-1);');
				}else{// 手机
					$this->assign('__JumpUrl__',__APP__);
				}
				$arrInit['__JumpUrl__']='';
			}else{
				$arrInit['__JumpUrl__']=$this->G('__JumpUrl__');
			}
			$sJavaScript=$this->javascriptR($arrInit);
			$this->assign('__JavaScript__',$sJavaScript);
			$sTemplate=strpos($GLOBALS['_commonConfig_']['TMPL_ACTION_ERROR'],'public+')===0 && $GLOBALS['_commonConfig_']['TMPL_MODULE_ACTION_DEPR']=='/'?
				str_replace('public+','Public+',$GLOBALS['_commonConfig_']['TMPL_ACTION_ERROR']):
				$GLOBALS['_commonConfig_']['TMPL_ACTION_ERROR'];
			$this->display($sTemplate);
		}
		exit;
	}
	private function javascriptR($arrInit){
		extract($arrInit);
		return "<script type=\"text/javascript\">var nSeconds={$__WaitSecond__};var sDefaultUrl=\"{$__JumpUrl__}\";onload=function(){if((sDefaultUrl=='javascript:history.go(-1)' || sDefaultUrl=='') && window.history.length==0){document.getElementById('__JumpUrl__').innerHTML='';return;};window.setInterval(redirection,1000);};function redirection(){if(nSeconds<=0){window.clearInterval();return;};nSeconds --;document.getElementById('__Seconds__').innerHTML=nSeconds;if(nSeconds==0){document.getElementById('__Loader__').style.display='none';window.clearInterval();if(sDefaultUrl!=''){window.location.href=sDefaultUrl;}}}</script>";
	}
}
class PController extends Controller{
	public function __construct($oParentcontroller=null){
		parent::__construct();
		$this->_oParent=$oParentcontroller;
	}
}
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   视图管理类($$)*/
class View{
	private $_oPar=null;
	private $_oTemplate;
	static private $_oShareGlobalTemplate;
	private $_sTemplateFile;
	private $_nRuntime;
	public function __construct($oPar,$sTemplate=null,$oTemplate=null){
		if($oTemplate){
			$this->setTemplate($oTemplate);
		}else{
			$this->setTemplate(self::createShareTemplate());
		}
		$this->_sTemplateFile=$sTemplate;
		$this->_oPar=$oPar;
		$this->init__();
	}
	public function init__(){}
	public function parseTemplateFile($sTemplateFile){
		$arrTemplateInfo=array();
		if(empty($sTemplateFile)){
			$sSuffix=$GLOBALS['_commonConfig_']['TEMPLATE_SUFFIX'];
			$arrTemplateInfo=array(
				'file'=>MODULE_NAME2.$GLOBALS['_commonConfig_']['TMPL_MODULE_ACTION_DEPR'].ACTION_NAME.$sSuffix
			);
		}elseif(!strpos($sTemplateFile,':\\') && strpos($sTemplateFile,'/')!==0 && !is_file($sTemplateFile)){// D:\phpcondition\......排除绝对路径分析
			if(strpos($sTemplateFile,'@')){// 分析主题
				$arrArray=explode('@',$sTemplateFile);
				$arrTemplateInfo['theme']=ucfirst(strtolower(array_shift($arrArray)));
				$sTemplateFile=array_shift($arrArray);
			}
			$sTemplateFile =str_replace('+',$GLOBALS['_commonConfig_']['TMPL_MODULE_ACTION_DEPR'],$sTemplateFile);//模块和操作&分析文件
			$sSuffix=$GLOBALS['_commonConfig_']['TEMPLATE_SUFFIX'];
			$arrTemplateInfo['file']=$sTemplateFile.$sSuffix;
		}
		if(!empty($arrTemplateInfo)){
			return $arrTemplateInfo;
		}else{
			return $sTemplateFile;
		}
	}
	public function getTemplate(){
		if(is_null($this->_oTemplate)){
			$this->_oTemplate=self::createShareTemplate();
		}
		return $this->_oTemplate;
	}
	public function setTemplate(Template $oTemplate){
		$oOldValue=$this->_oTemplate;
		$this->_oTemplate=$oTemplate;
		return $oOldValue;
	}
	public function getPar(){
		if($this->_oPar===null){
			return null;
		}else{
			return $this->_oPar;
		}
	}
	static public function createShareTemplate(){
		if(!self::$_oShareGlobalTemplate){
			self::$_oShareGlobalTemplate=new Template();
		}
		return self::$_oShareGlobalTemplate;
	}
	public function display($sTemplateFile='',$sCharset='utf-8',$sContentType='text/html',$bReturn=false){
		header("Content-Type:".$sContentType."; charset=".$sCharset);
		header("Cache-control: private");//支持页面回跳
		if(empty($sTemplateFile)){
			$sTemplateFile=&$this->_sTemplateFile;
		}
		$this->_nRuntime=C::getMicrotime();// 记录模板开始运行时间
		$TemplateFile=$sTemplateFile;
		if(!is_file($sTemplateFile)){
			$TemplateFile=$this->parseTemplateFile($sTemplateFile);
		}
		$oTemplate=$this->getTemplate();
		$oController=$this->getPar();
		$oTemplate->setVar('TheView',$this);
		$oTemplate->setVar('TheController',$oController);
		$sContent=$oTemplate->display($TemplateFile,false);
		if(!C::isAjax()){
			if($GLOBALS['_commonConfig_']['SHOW_RUN_TIME']){
				$sContent.=$this->templateRuntime();
			}
			if($GLOBALS['_commonConfig_']['SHOW_PAGE_TRACE']){
				$sContent.=$this->templateTrace();
			}
		}
		if($bReturn===true){
			return $sContent;
		}else{
			echo $sContent;
			unset($sContent);
		}
	}
	public function templateRuntime($bReturn=false){
		if($bReturn===false){
			$sContent='<div id="qeephp_run_time" class="qeephp_run_time" style="display:none;">';
		}else{
			$sContent='';
		}
		// 总时间
		$nEndTime=microtime(TRUE);
		$nTotalRuntime=number_format(($nEndTime-$GLOBALS['_beginTime_']),3);
		$sContent.="Pro ".$nTotalRuntime." (s)";
		if($GLOBALS['_commonConfig_']['SHOW_DETAIL_TIME']){
			$sContent.="(Tpl:".$this->getMicrotime()." (s)";
		}
		if($GLOBALS['_commonConfig_']['SHOW_DB_TIMES']){
			$oDb=Db::RUN();
			$sContent.=" | ".$oDb->getConnect()->Q()." Q";
		}
		if($GLOBALS['_commonConfig_']['SHOW_GZIP_STATUS']){
			if($GLOBALS['_commonConfig_']['START_GZIP']){
				$sGzipString='on';
			}else{
				$sGzipString='off';
			}
			$sContent.=" | Gz {$sGzipString}";
		}
		if(MEMORY_LIMIT_ON && $GLOBALS['_commonConfig_']['SHOW_USE_MEM']){
			$nStartMem=array_sum(explode(' ',$GLOBALS['_startUseMems_']));
			$nEndMem=array_sum(explode(' ',memory_get_usage()));
			$sContent.=' | Mem:'. C::changeFileSize($nEndMem-$nStartMem);
		}
		if($bReturn===false){
			$sContent.="</div>";
		}
		return $sContent;
	}
	public function templateTrace(){
		$arrTrace=array();
		$arrTrace['ThePage']=$_SERVER['REQUEST_URI'];
		$arrTrace['Runtime']=$this->templateRuntime(true);
		$arrLog=Log::$_arrLog;
		$arrTrace[Q::L('日志记录','__QEEPHP__@Q')]=count($arrLog)?Q::L('%d条日志','__QEEPHP__@Q',null,count($arrLog)).'<br/>'.implode('<br/>',$arrLog):Q::L('无日志记录','__QEEPHP__@Q');
		$arrFiles= get_included_files();
		$arrTrace[Q::L('加载文件','__QEEPHP__@Q')]=count($arrFiles).str_replace("\n",'<br/>',substr(substr(print_r($arrFiles,true),7),0,-2));
		ob_start();
		include Q_PATH.'/Resource_/Template/PageTrace.template.php';
		$sContent=ob_get_contents();
		ob_end_clean();
		return $sContent;
	}
	public function getMicrotime(){
		return round(C::getMicrotime()-$this->_nRuntime,5);
	}
	public function assign($Name,$Value=null){
		$oTemplate=$this->getTemplate();
		return $oTemplate->setVar($Name,$Value);
	}
	public function get($Name){
		return $this->getVar($Name);
	}
	public function getVar($Name){
		$oTemplate=$this->getTemplate();
		return $oTemplate->getVar($Name);
	}
}
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   模板处理类（Learn JC!）($$)*/
class Template{
	protected $TEMPLATE_OBJS=array();
	static public $_arrParses=array();
	protected $_sCompiledFilePath;
	protected $_sThemeName='';
	protected $_bIsChildTemplate=FALSE;
	static protected $_bWithInTheSystem=FALSE;
	static private $_sTemplateDir;
	private $_arrVariable=array();
	public function loadParses(){
		$sClassName=get_class($this);// 具体的类
		call_user_func(array($sClassName,'loadDefaultParses'));// 载入默认的分析器
	}
	public function putInTemplateObj_(TemplateObj $oTemplateObj){
		$this->TEMPLATE_OBJS[]=$oTemplateObj;
	}
	public function clearTemplateObj(){
		$nCount=count($this->TEMPLATE_OBJS);
		$this->TEMPLATE_OBJS=array();
		return $nCount;
	}
	public function compile($sTemplatePath,$sCompiledPath='',$bReturnCompiled=false){
		if(!is_file($sTemplatePath)){
			Q::E('$sTemplatePath is not a file');
		}
		if($sCompiledPath==''){
			$sCompiledPath=$this->getCompiledPath($sTemplatePath);
		}
		$sCompiled=file_get_contents($sTemplatePath);
		foreach(self::$_arrParses as $sParserName){
			$oParser=Q::instance($sParserName);
			$this->bParseTemplate_($sCompiled);
			$oParser->parse($this,$sTemplatePath,$sCompiled);// 分析
			$sCompiled=$this->compileTemplateObj();// 编译
		}
		if(defined('TMPL_STRIP_SPACE')){
			// HTML
			$arrFind=array("~>\s+<~","~>(\s+\n|\r)~");
			$arrReplace=array("><",">");
			$sCompiled=preg_replace($arrFind,$arrReplace,$sCompiled);
			// Javascript
			$sCompiled=preg_replace(array('/(^|\r|\n)\/\*.+?(\r|\n)\*\/(\r|\n)/is','/\/\/note.+?(\r|\n)/i','/\/\/debug.+?(\r|\n)/i','/(^|\r|\n)(\s|\t)+/','/(\r|\n)/',"/\/\*(.*?)\*\//ies"),'',$sCompiled);
		}
		$sStr="<?php  /* QeePHP ".(Q::L('模板缓存文件生成时间：','__QEEPHP__@Q')).date('Y-m-d H:i:s',CURRENT_TIMESTAMP)."  */ ?>\r\n";
		$sCompiled=$sStr.$sCompiled;
		$sCompiled=str_replace(array("\r","\n"),'
',$sCompiled);
		$sCompiled=preg_replace("/(
)+/i",'
',$sCompiled);
		$sCompiled=str_replace('
',(IS_WIN?"\r\n":"\n"),$sCompiled);// 解决不同操作系统源代码换行混乱
		if($bReturnCompiled===false){
			$this->makeCompiledFile($sTemplatePath,$sCompiledPath,$sCompiled);// 生成编译文件
			return $sCompiledPath;
		}else{
			return $sCompiled;
		}
	}
	protected function compileTemplateObj(){
		$sCompiled='';// 逐个编译TemplateObj
		foreach($this->TEMPLATE_OBJS as $oTemplateObj){
			$oTemplateObj->compile();
			$sCompiled.=$oTemplateObj->getCompiled();
		}
		return $sCompiled;
	}
	public function getCompiledPath($sTemplatePath){
		$sTemplatePath=str_replace('\\','/',$sTemplatePath); 
		
		$arrValue=explode('/',str_replace(array(str_replace('\\','/',TEMPLATE_PATH.'/'),str_replace('\\','/',Q_PATH.'/'),str_replace('\\','/',getcwd().'/')),array(''),$sTemplatePath));
		if($GLOBALS['_commonConfig_']['TMPL_MODULE_ACTION_DEPR']=='/' && count($arrValue)>1){
			array_shift($arrValue);
		}
		
		if(self::$_bWithInTheSystem===true){// 如果保存在系统内部
			$this->_sCompiledFilePath=dirname($sTemplatePath).'/~Com/'.basename($sTemplatePath,$GLOBALS['_commonConfig_']['TEMPLATE_SUFFIX']).'.php';
			return $this->_sCompiledFilePath;
		}
		$sFileName=implode('/',$arrValue);
		$this->_sCompiledFilePath=APP_RUNTIME_PATH.'/Cache/'.($this->_sThemeName?ucfirst($this->_sThemeName).'/':'').
			($GLOBALS['_commonConfig_']['TMPL_MODULE_ACTION_DEPR']=='/'?ucfirst(MODULE_NAME).'/':'').
			basename($sFileName,$GLOBALS['_commonConfig_']['TEMPLATE_SUFFIX']).(dirname($sFileName)!='.'?'~@'.md5(dirname($sFileName)):'').'.php';
		return $this->_sCompiledFilePath;
	}
	static public function in($bWithInTheSystem=false){
		$bOldValue=self::$_bWithInTheSystem;
		self::$_bWithInTheSystem=$bWithInTheSystem;
		return $bOldValue;
	}
	public function returnCompiledPath(){
		return $this->_sCompiledFilePath;
	}
	protected function isCompiledFileExpired($sTemplatePath,$sCompiledPath){
		if(!is_file($sCompiledPath)){
			return true;
		}
		if($GLOBALS['_commonConfig_']['CACHE_LIFE_TIME']==-1){// 编译过期时间为-1表示永不过期
			return false;
		}
		if(filemtime($sCompiledPath)+$GLOBALS['_commonConfig_']['CACHE_LIFE_TIME']<CURRENT_TIMESTAMP){
			return true;
		}
		if(filemtime($sTemplatePath)>=filemtime($sCompiledPath)){
			return true;
		}
		return false;
	}
	protected function makeCompiledFile($sTemplatePath,$sCompiledPath,&$sCompiled){
		!is_file($sCompiledPath) && !is_dir(dirname($sCompiledPath)) && C::makeDir(dirname($sCompiledPath));
		file_put_contents($sCompiledPath,$sCompiled);
	}
	static public function loadDefaultParses(){
		include_once(Q_PATH.'/Template/TemplateParsers_.php');
		TemplateGlobalParser::regToParser();// 全局
		TemplatePhpParser::regToParser();// PHP
		TemplateCodeParser::regToParser();// 代码
		TemplateNodeParser::regToParser();// 节点
		TemplateRevertParser::regToParser(); // 反向
		TemplateGlobalRevertParser::regToParser(); // 全局反向
	}
	static public function setTemplateDir($sDir){
		if(!is_dir($sDir)){
			Q::E('$sDir is not a dir');
		}
		return self::$_sTemplateDir=$sDir;
	}
	static public function findTemplate($arrTemplateFile){
		$sTemplateFile=isset($arrTemplateFile['theme'])?$arrTemplateFile['theme'].'/':'';
		$sTemplateFile.=(isset($arrTemplateFile['file'])?$arrTemplateFile['file']:'');
		if(is_file(self::$_sTemplateDir.'/'.$sTemplateFile)){
			return self::$_sTemplateDir.'/'.$sTemplateFile;
		}
		if(defined('QEEPHP_TEMPLATE_BASE') && !isset($arrTemplateFile['theme']) && ucfirst(QEEPHP_TEMPLATE_BASE)!==TEMPLATE_NAME){// 依赖模板 兼容性分析
			$sTemplateDir=str_replace('/Theme/'.TEMPLATE_NAME.'/','/Theme/'.ucfirst(QEEPHP_TEMPLATE_BASE).'/',self::$_sTemplateDir.'/');
			if(is_file($sTemplateDir.'/'.$sTemplateFile)){
				return $sTemplateDir.'/'.$sTemplateFile;
			}
		}
		if(!isset($arrTemplateFile['theme']) && 'Default'!==TEMPLATE_NAME){// Default模板 兼容性分析
			$sTemplateDir=str_replace('/Theme/'.TEMPLATE_NAME.'/','/Theme/Default/',self::$_sTemplateDir.'/');
			if(is_file($sTemplateDir.'/'.$sTemplateFile)){
				return $sTemplateDir.$sTemplateFile;
			}
		}
		return null;
	}
	public function putInTemplateObj(TemplateObj $oTemplateObj){
		$oTopTemplateObj=$this->TEMPLATE_OBJS[0];
		$oTopTemplateObj->addTemplateObj($oTemplateObj);// 插入
	}
	protected function bParseTemplate_(&$sCompiled){
		$oTopTemplateObj=new TemplateObj($sCompiled);// 创建顶级TemplateObj
		$oTopTemplateObj->locate($sCompiled,0);
		$this->clearTemplateObj();
		Template::putInTemplateObj_($oTopTemplateObj);
	}
	public function includeChildTemplate($sTemplateFile,$sCurrentFile='',$sSourceFile=''){
		if(!is_file($sTemplateFile)){
			$bExistsFile=false;// 默认主题自动导向
			if(defined('QEEPHP_TEMPLATE_BASE') && ucfirst(QEEPHP_TEMPLATE_BASE)!==TEMPLATE_NAME){// 依赖主题自动导向
				$sReplacePath='/Theme/'.TEMPLATE_NAME.'/';
				$sTargetPath='/Theme/'.ucfirst(QEEPHP_TEMPLATE_BASE).'/';
				$sTemplateFile2=str_replace($sReplacePath,$sTargetPath,$sTemplateFile);
				if(is_file($sTemplateFile2)){
					$sTemplateFile=&$sTemplateFile2;
					$bExistsFile=true;
				}else{
					unset($sTemplateFile2);
				}
			}
			if($bExistsFile===false && 'Default'!==TEMPLATE_NAME){// 默认主题自动导向
				$sReplacePath='/Theme/'.TEMPLATE_NAME.'/';
				$sTargetPath='/Theme/Default/';
				$sTemplateFile=str_replace($sReplacePath,$sTargetPath,$sTemplateFile);
				if(is_file($sTemplateFile)){
					$bExistsFile=true;
				}
			}
			if($bExistsFile===false){
				E(Q::L('警告：对不起子模板 %s 不存在','__QEEPHP__@Q',null,$sTemplateFile));
				return;
			}
		}
		$this->display($sTemplateFile,true,true,$sCurrentFile,$sSourceFile);
	}
	public function display($TemplateFile,$bDisplayAtOnce=true,$bChild=false,$sCurrentFile='',$sSourceFile=''){
		$TemplateFileOld=$TemplateFile;
		if(is_string($TemplateFile) && is_file($TemplateFile)){
			$this->_sThemeName=TEMPLATE_NAME;
		}else{
			if(is_array($TemplateFile) && !empty($TemplateFile['theme'])){
				$this->_sThemeName=$TemplateFile['theme'];
			}else{
				$this->_sThemeName=TEMPLATE_NAME;
			}
			$TemplateFile=self::findTemplate($TemplateFile);
		}
		if(!is_file($TemplateFile)){
			$TemplateFile=$TemplateFile?$TemplateFile:$TemplateFileOld;
			Q::E(Q::L('无法找到模板文件<br>%s','__QEEPHP__@Q',null,is_array($TemplateFile)?implode(' ',$TemplateFile):$TemplateFile));
		}
		$arrVars=&$this->_arrVariable;
		if(is_array($arrVars) and count($arrVars)){
			extract($arrVars,EXTR_PREFIX_SAME,'tpl_');
		}
		$sCompiledPath=$this->getCompiledPath($TemplateFile);// 编译文件路径
		if($this->isCompiledFileExpired($TemplateFile,$sCompiledPath)){// 重新编译
			$this->loadParses();
			$this->compile($TemplateFile,$sCompiledPath);
		}
		// 逐步将子模板缓存写入父模板至到最后
		if($bChild===true){
			if($GLOBALS['_commonConfig_']['CACHE_REPLACE_CHILDREN'] && is_file($sCurrentFile) && is_file($sCompiledPath)){
				$sTheNote="/<!--<\#\#\#\#incl\*".md5($sSourceFile)."\*ude\#\#\#\#>-->(.*?)<!--<\/\#\#\#\#incl\*".md5($sSourceFile)."\*ude####\/>-->/s";
				$sCurrentCache=file_get_contents($sCurrentFile);
				preg_match_all($sTheNote,$sCurrentCache,$arrResult);
				// 替换标签
				if(!empty($arrResult[1][0])){
					$sCurrentCache=str_replace($arrResult[1][0],file_get_contents($sCompiledPath),$sCurrentCache);
					file_put_contents($sCurrentFile,$sCurrentCache);
				}
			}
		}
		$sReturn=null;
		if($bDisplayAtOnce===false){// 需要返回
			ob_start();
			include $sCompiledPath;
			$sReturn=ob_get_contents();
			ob_end_clean();
			return $sReturn;
		}else{// 不需要返回
			include $sCompiledPath;
		}
		return $sReturn;
	}
	public function setVar($Name,$Value=null){
		if(is_string($Name)){
			$sOldValue=isset($this->_arrVariable[$Name])?$this->_arrVariable[$Name]:null;
			$this->_arrVariable[$Name]=&$Value;
			return $sOldValue;
		}elseif(is_array($Name)){
			foreach($Name as $sName=>&$EachValue){
				$this->setVar($sName,$EachValue);
			}
		}
	}
	public function getVar($sName){
		return isset($this->_arrVariable[$sName])?$this->_arrVariable[$sName]:null;
	}
}
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   对 PHP 原生Cookie 函数库的封装($$)*/
class Cookie{
	public static function setCookie($sName,$sValue='',$nLife=0,$sCookieDomain=null,$bPrefix=true,$bHttponly=false){
		$sName=($bPrefix?$GLOBALS['_commonConfig_']['COOKIE_PREFIX']:'').$sName;
		if($sValue===null || $nLife<0){
			$nLife=-1;
			if(isset($_COOKIE[$sName])){
				unset($_COOKIE[$sName]);
			}
		}else{
			$_COOKIE[$sName]=$sValue;
			if($nLife!==NULL && $nLife==0){
				$nLife=$GLOBALS['_commonConfig_']['COOKIE_EXPIRE'];
			}
		}
		$nLife=$nLife>0?CURRENT_TIMESTAMP+$nLife:($nLife<0?CURRENT_TIMESTAMP-31536000:null);
		$sPath=$bHttponly && PHP_VERSION<'5.2.0'?$GLOBALS['_commonConfig_']['COOKIE_PATH'].';HttpOnly':$GLOBALS['_commonConfig_']['COOKIE_PATH'];
		$sCookieDomain=$sCookieDomain!==null?$sCookieDomain:$GLOBALS['_commonConfig_']['COOKIE_DOMAIN'];
		$nSecure=$_SERVER['SERVER_PORT']==443?1:0;
		if(PHP_VERSION<'5.2.0'){
			setcookie($sName,$sValue,$nLife,$sPath,$sCookieDomain,$nSecure);
		}else{
			setcookie($sName,$sValue,$nLife,$sPath,$sCookieDomain,$nSecure,$bHttponly);
		}
	}
	public static function getCookie($sName,$bPrefix=true){
		$sName=($bPrefix?$GLOBALS['_commonConfig_']['COOKIE_PREFIX']:'').$sName;
		return isset($_COOKIE[$sName])?$_COOKIE[$sName]:'';
	}
	public static function deleteCookie($sName,$sCookieDomain=null,$bPrefix=true){
		self::setCookie($sName,null,-1,$sCookieDomain,$bPrefix);
	}
	public static function clearCookie($bOnlyDeletePrefix=true,$sCookieDomain=null){
		$nCookie=count($_COOKIE);
		foreach($_COOKIE as $sKey=>$Val){
			if($bOnlyDeletePrefix===true && $GLOBALS['_commonConfig_']['COOKIE_PREFIX']){
				if(strpos($sKey,$GLOBALS['_commonConfig_']['COOKIE_PREFIX'])===0){
					self::deleteCookie($sKey,$sCookieDomain,false);
				}
			}else{
				self::deleteCookie($sKey,$sCookieDomain,false);
			}
		}
		return $nCookie;
	}
}
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   ModelMeta元模式（Learn QP!）($$)*/
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
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   模型（Learn QP!）($$)*/
class Model implements ArrayAccess{
	protected $_sErrorMessage;
	protected $_arrProp=array();
	protected $_sClassName;
	protected static $_arrMeta;
	protected $_bAutofill=true;
	protected $_arrChangeProp=array();
	public function __construct($Data=null,$sName='',$bSelect=false){
		// 设置模型名字
		if(empty($sName)){
			$sName=get_class($this);
		}
		$this->_sClassName=$sName;
		// 判断是否存在Meta对象，否则创建
		if(!isset(self::$_arrMeta[$this->_sClassName])){
			self::$_arrMeta[$this->_sClassName]=ModelMeta::instance($this->_sClassName);
		}
		$oMeta=self::$_arrMeta[$this->_sClassName];
		if($Data!==null){
			$this->changeProp($Data,null,$bSelect);
		}
	}
	public function save($sSaveMethod='save',$Data=null){
		if($Data!==null){
			$this->changeProp($Data);
		}
		// 表单自动填充
		$this->makePostData();
		$this->beforeSave_();
		// 程序通过内置方法统一实现
		switch(strtolower($sSaveMethod)){
			case 'create':
				$this->create_();
				break;
			case 'update':
				$this->update_();
				break;
			case 'replace':
				$this->replace_();
				break;
			case 'save':
				default:
				$arrPkValue=$this->id(true);
				if(!is_array($arrPkValue)){
					if(empty($arrPkValue)){// 单一主键的情况下，如果 $arrPkValue 为空，则 create，否则 update
						$this->create_();
					}else{
						$this->update_();
					}
				}else{
					$this->replace_();// 复合主键的情况下，则使用 replace 方式
				}
				break;
		}
		$this->afterSave_();
		return $this;
	}
	public function id($bChange=false){
		$arrId=array();
		foreach(self::$_arrMeta[$this->_sClassName]->_arrTableMeta['pk_'] as $sName){
			if($bChange===true){
				if(!in_array($sName,$this->_arrChangeProp)){
					$arrId[$sName]=$this->{$sName};
				}
			}else{
				$arrId[$sName]=$this->{$sName};
			}
		}
		if(count($arrId)==1){
			$arrId=reset($arrId);
		}
		if(!empty($arrId)){
			return $arrId;
		}else{
			return null;
		}
	}
	public function changeProp($Prop,$Value=null,$bSelect=false){
		if(!is_array($Prop)){
			$Prop=array($Prop=>$Value);
		}
		foreach($Prop as $sPropName=>$Value){// 将数组赋值给对象属性
			if(!in_array($sPropName,self::$_arrMeta[$this->_sClassName]->_arrTableMeta['field'])){
				continue;
			}
			$this->_arrProp[$sPropName]=$Value;
			if($bSelect===false){
				$this->_arrChangeProp[]=$sPropName;
			}
		}
		return $this;
	}
	public function get($sPropName){
		return $this->__get($sPropName);
	}
	public function &__get($sPropName){
		$this->checkProp_($sPropName);
		return $this->_arrProp[$sPropName];
	}
	public function set($sPropName,$Value){
		$this->__set($sPropName,$Value);
	}
	public function __set($sPropName,$Value){
		$this->checkProp_($sPropName);
		$this->_arrProp[$sPropName]=$Value;
		$this->_arrChangeProp[]=$sPropName;
	}
	public function setAutofill($bAutofill=true){
		$this->_bAutofill=$bAutofill;
	}
	public function __isset($sPropName){
		return array_key_exists($sPropName,$this->_arrProp);
	}
	public function offsetExists($sPropName){
		return array_key_exists($sPropName,$this->_arrProp);
	}
	public function offsetSet($sPropName,$Value){
		$this->checkProp_($sPropName);
		$this->_arrProp[$sPropName]=$Value;
	}
	public function offsetGet($sPropName){
		$this->checkProp_($sPropName);
		return $this->_arrProp[$sPropName];
	}
	public function offsetUnset($sPropName){
		$this->checkProp_($sPropName);
		$this->_arrProp[$sPropName]=null;
	}
	public function getClassName(){
		return $this->_sClassName;
	}
	public function getMeta(){
		return self::$_arrMeta[$this->_sClassName];
	}
	public function getTableEnter(){
		return self::$_arrMeta[$this->_sClassName]->_oTable;
	}
	public function getDb(){
		return self::$_arrMeta[$this->_sClassName]->_oTable->getDb();
	}
	public function hasProp($sPropName){
		return isset(self::$_arrMeta[$this->_sClassName]->_arrProp[$sPropName]);
	}
	public function destroy(){
		$oMeta=self::$_arrMeta[$this->_sClassName];
		$sPk=reset($oMeta->_arrTableMeta['pk_']);
		$value=$this->{$sPk};
		if(empty($value)){
			Q::E('Primary key does not exist');
		}
		// 确定删除当前对象的条件
		if(count($oMeta->_arrTableMeta['pk_'])>1){
			$where=$value;
		}else{
			$where=array($sPk=>$value);
		}
		// 从数据库中删除当前对象
		$bResult=$oMeta->_oTable->delete($where);
		if($bResult===false){
			$this->_sErrorMessage=$oMeta->_oTable->getErrorMessage();
			return false;
		}
	}
	public function toArray(){
		$arrData=array();
		foreach(self::$_arrMeta[$this->_sClassName]->_arrTableMeta['field'] as $sPropName){
			$arrData[$sPropName]=$this->{$sPropName};
		}
		return $arrData;
	}
	static function F_($sClass){
		$arrArgs=func_get_args();
		array_shift($arrArgs);
		return ModelMeta::instance($sClass,true)->findByArgs($arrArgs);
	}
	static function M_($sClass){
		return ModelMeta::instance($sClass,true);
	}
	public function isError(){
		return !empty($this->_sErrorMessage);
	}
	public function getErrorMessage(){
		return $this->_sErrorMessage;
	}
	protected function method_($sMethod){
		$arrArgs=func_get_args();
		array_shift($arrArgs);
		return $this->__call($sMethod,$arrArgs);
	}
	protected function makePostData(){
		foreach(self::$_arrMeta[$this->_sClassName]->_arrTableMeta['field'] as $sField){
			if(!in_array($sField,$this->_arrChangeProp) && isset($_POST[$sField])){
				$this->_arrProp[$sField]=trim($_POST[$sField]);
				$this->_arrChangeProp[]=$sField;
			}
		}
	}
	protected function create_(){
		$oMeta=self::$_arrMeta[$this->_sClassName];
		// 自动填充
		if($this->_bAutofill===true){
			$this->autofill_('create');
		}
		foreach($oMeta->_arrTableMeta['default'] as $sPropName=>$defaultVal){
			if(!isset($this->_arrProp[$sPropName]) || $this->_arrProp[$sPropName]===null){
				$this->_arrProp[$sPropName]=$defaultVal;
			}
		}
		$this->beforeCreate_();
		if($this->check_('create',true)===false){// 进行create验证
			return false;
		}
		// 准备要保存到数据库的数据
		$arrSaveData=array();
		foreach($this->_arrProp as $sPropName=>$sValue){
			// 过滤NULL值
			if($sValue!==null){
				$arrSaveData[$sPropName]=$sValue;
			}
		}
		// 将名值对保存到数据库
		$arrPkValue=$oMeta->_oTable->insert($arrSaveData);
		if($arrPkValue===false){
			$this->_sErrorMessage=$oMeta->_oTable->getErrorMessage();
			return false;
		}
		// 将获得的主键值指定给对象
		foreach($arrPkValue as $sFieldName=>$sFieldValue){
			$this->_arrProp[$sFieldName]=$sFieldValue;
		}
		$this->afterCreate_();
	}
	protected function update_(){
		$oMeta=self::$_arrMeta[$this->_sClassName];
		if($this->_bAutofill===true){// 这里允许update和all
			$this->autofill_('update');
		}
		$this->beforeUpdate_();
		if($this->check_('update',true)===false){// 进行update验证
			return false;
		}
		$arrSaveData=array();
		foreach($this->_arrProp as $sPropName=>$value){
			if(in_array($sPropName,$this->_arrChangeProp)){
				$arrSaveData[$sPropName]=$value;
			}
		}
		if(!empty($arrSaveData)){
			$arrConditions=array();
			foreach($oMeta->_arrTableMeta['pk_'] as $sFieldName){
				if(isset($arrSaveData[$sFieldName])){
					unset($arrSaveData[$sFieldName]);
				}
				if(!empty($this->_arrProp[$sFieldName])){
					$arrConditions[$sFieldName]=$this->_arrProp[$sFieldName];
				}
			}
			if(!empty($arrSaveData) && !empty($arrConditions)){
				$bResult=$oMeta->_oTable->update($arrSaveData,$arrConditions);
				if($bResult===false){
					$this->_sErrorMessage=$oMeta->_oTable->getErrorMessage();
					return false;
				}
			}
		}
		$this->afterUpdate_();
	}
	protected function replace_(){
		try{
			$bResult=$this->create_();// 数据库本身并不支持 replace 操作，所以只能是通过insert操作来模拟
		}catch(Exception $e){
			$this->update_();
		}
	}
	protected function autofill_($sMode='create'){
		$oMeta=self::$_arrMeta[$this->_sClassName];
		$arrFieldToProp=$arrField=self::$_arrMeta[$this->_sClassName]->_arrTableMeta['field'];// 我们要求数据库字段都以小写为准
		// 兼容大小写，字段必须全部为小写&任何时候使用当前时间戳进行填充
		if(in_array('dateline',$arrField)){
			$this->changeProp('dateline',CURRENT_TIMESTAMP);
		}
		if($sMode=='create' and in_array('create_dateline',$arrField)){// 创建对象的时候
			$this->changeProp('create_dateline',CURRENT_TIMESTAMP);
		}
		if($sMode=='update' and in_array('update_dateline',$arrField)){// 更新对象的时候
			$this->changeProp('update_dateline',CURRENT_TIMESTAMP);
		}
		$arrFillProps=$oMeta->_arrAutofill;
		$arrData=$this->_arrProp;
		foreach($arrFillProps as $arrValue){
			$sField=array_key_exists(0,$arrValue)?$arrValue[0]:''; // 字段
			$sContent=array_key_exists(1,$arrValue)?$arrValue[1]:''; // 内容
			$sCondition=array_key_exists(2,$arrValue)?$arrValue[2]:''; // 填充条件
			$sExtend=array_key_exists(3,$arrValue)?$arrValue[3]:''; // 附加规则
			if($sContent=='datetime'){
				$sContent=date('Y-m-d H:i:s',CURRENT_TIMESTAMP);
			}elseif($sContent=='timestamp'){
				$sContent=CURRENT_TIMESTAMP;
			}elseif($sContent=='date_'){
				$sContent=date('Y-m-d',CURRENT_TIMESTAMP);
			}elseif($sContent=='time_'){
				$sContent= date('H:i:s',CURRENT_TIMESTAMP);
			}
			// 自动填充类型处理,处理类型为空，那么为all
			if($sCondition=='' || $sMode==$sCondition){
				if($sExtend){// 调用附加规则
					switch($sExtend){
						case 'function':// 使用函数进行填充 字段的值作为参数
						case 'callback': // 使用回调方法
							$arrArgs=isset($arrValue[4])?$arrValue[4]:array();// 回调参数
							if(isset($arrData[$sField])){
								array_unshift($arrArgs,$arrData[$sField]);
							}
							if('function'==$sExtend){// funtion回调
								if(function_exists($sContent)){
									$arrData[$sField]=call_user_func_array($sContent,$arrArgs);
								}else{
									Q::E('Function is not exist');
								}
							}else{
								if(is_array($sContent)){
									if(!is_callable($sContent,false)){
										Q::E('Callback is not exist');
									}
									$arrData[$sField]=call_user_func_array($sContent,$arrArgs);
								}else{
									$arrData[$sField]=call_user_func_array(array(&$this,$sContent),$arrArgs);
								}
							}
							break;
						case "field":
							$arrData[$sField]=$arrData[$sContent];
							break;
						case "string":
							$arrData[$sField]=strval($sContent);
							break;
					}
				}else{
					$arrData[$sField]=$sContent;
				}
			}
		}
		$this->_arrProp=$arrData;
		return $this->_arrProp;
	}
	protected function checkProp_($sPropName){
		if(!in_array($sPropName,self::$_arrMeta[$this->_sClassName]->_arrTableMeta['field'])){
			Q::E(Q::L('属性：%s不存在。','__QEEPHP__@Q',null,$sPropName));
		}
	}
	protected function check_($sMode){
		$oMeta=self::$_arrMeta[$this->_sClassName];
		$arrCheckProps=$oMeta->_arrTableMeta['field'];
		foreach($arrCheckProps as $key=>$sValue){
			if(in_array($sValue,$oMeta->_arrTableMeta['pk_'])){
				unset($arrCheckProps[$key]);
			}
		}
		$arrCheckProps=array_flip($arrCheckProps);
		$arrError=$oMeta->check($this->_arrProp,$arrCheckProps,$sMode);
		if(!empty($arrError)){
			$sErrorMessage='<span class="QModelList">';
			foreach($arrError as $sField=>$arrValue){
				foreach($arrValue as $sK=>$sV){
					$sErrorMessage.=$sV.'<br/>';
				}
			}
			$sErrorMessage.='</span>';
			$this->_sErrorMessage=$sErrorMessage;
			return false;
		}
	}
	protected function beforeCreate_(){}
	protected function afterCreate_(){}
	protected function beforeUpdate_(){}
	protected function afterUpdate_(){}
	protected function beforeSave_(){}
	protected function afterSave_(){}
}
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   数据库访问统一入口，可以通过这个接口与数据库进行交互($$)*/
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
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   数据库表入口（Learn QP!）($$)*/
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
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   Mysql数据库工厂类，用于生成数据库相关对象($$)*/
class DbFactoryMysql extends DbFactory{
	public function createConnect(){
		return new DbConnectMysql();
	}
	public function createRecordSet(DbConnect $oConn,$nFetchMode=Db::FETCH_MODE_ASSOC){
		return new DbRecordSetMysql($oConn,$nFetchMode);
	}
}
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   数据库工厂类，用于生成数据库相关对象 < 抽象类 >($$)*/
abstract class DbFactory{
	abstract public function createConnect();
	abstract public function createRecordSet(DbConnect $oConnect);
}
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   所有数据库连接类的基类($$)*/
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
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   Mysql数据连接管理类($$)*/
define('CLIENT_MULTI_RESULTS',131072);
class DbConnectMysql extends DbConnect{
	public function commonConnect($Config='',$nLinkid=0){
		if(!isset($this->_arrHConnect[$nLinkid ])){
			$this->_arrCurrentDbConfig=$Config;// 赋值给当前数据库连接配置
			$nHost=$Config['db_host'].($Config['db_port']?":{$Config['db_port']}":'');// 端口处理
			if(empty($Config['connect'])){// 如果设置了数据连接，则不连接
				if($this->_bPConnect){// 是否永久连接
					$this->_arrHConnect[$nLinkid]=@mysql_pconnect($nHost,$Config['db_user'],$Config['db_password'],CLIENT_MULTI_RESULTS);
				}else{
					$this->_arrHConnect[$nLinkid]=@mysql_connect($nHost,$Config['db_user'],$Config['db_password'],true,CLIENT_MULTI_RESULTS);
				}
			}else{
				$this->_arrHConnect[$nLinkid]=$Config['connect'];
			}
			if(!$this->_arrHConnect[$nLinkid]){// 判断是否成功连接上数据
				Q::E(Q::L('数据库连接失败，请检查你的数据库信息是否正确，连接数据库的配置如下：%s','__QEEPHP__@Q',null,C::dump($Config,false)));
				return false;
			}
			$this->_hCurrentConnect=$this->_arrHConnect[$nLinkid];
			if(empty($Config['db_name'])|| !mysql_select_db($Config['db_name'],$this->_arrHConnect[$nLinkid])){// 尝试请求数据
				Q::E(Q::L('数据库不存在在或者错误，请检查你的数据库信息是否正确，连接数据库的配置如下：%s','__QEEPHP__@Q',null,C::dump($Config,false)));
				return false;
			}
			$nDbVersion=$this->databaseVersion();// 获取Mysql数据库版本,尝试兼容性纠正
			if($nDbVersion>="4.1"){// 使用UTF8存取数据库 需要mysql 4.1.0以上支持
				$sCharset=isset($Config['db_char'])?$Config['db_char']:$GLOBALS['_commonConfig_']['DB_CHAR'];// 获取数据库字符集
				if(!mysql_query("SET character_set_connection=".$sCharset.",character_set_results=".$sCharset.",character_set_client=binary")){
					Q::E(sprintf("Set db_host ‘%s’ charset=%s failed.",$nHost,$sCharset));
					return false;
				}
			}
			if($nDbVersion>'5.0.1'){// 忽略严格模式
				if(!mysql_query("SET sql_mode=''",$this->_arrHConnect[$nLinkid])){
					Q::E('Set sql_mode failed.',$this->_arrHConnect[$nLinkid]);
					return false;
				}
			}
			$this->_bConnected=true;// 标记连接成功
		}
		return $this->_arrHConnect[$nLinkid];
	}
	public function disConnect($hDbConnect=null,$bCloseAll=false){
		if($hDbConnect && is_resource($hDbConnect)){// 关闭指定数据库连接
			mysql_close($hDbConnect);
			$hDbConnect=null;
		}
		if($bCloseAll){// 关闭所有数据库连接
			if($this->_hWriteConnect && is_resource($this->_hWriteConnect)){
				mysql_close($this->_hWriteConnect);
				$this->_hWriteConnect=null;
			}
			if(is_array($this->_arrHReadConnect)&& !empty($this->_arrHReadConnect)){
				foreach($this->_arrHReadConnect as $hConnect){
					if($hConnect && is_resource($hConnect)){
						mysql_close($hConnect);
					}
				}
				$this->_arrHReadConnect=array();
			}
			$this->_arrHConnect=array();
		}
		return true;
	}
	public function query_($sSql,$bIsMaster=false){
		$sSql=trim($sSql);// 过滤SQL语句
		if($sSql==""){// sql语句为空，则返回错误
			$this->errorMessage('Sql query is empty.');
			return false;
		}
		if(!$GLOBALS['_commonConfig_']['DB_RW_SEPARATE'] || $this->_bSingleHost){// 是否只有一台数据库机器
			$bIsMaster=true;
		}
		$sType='';// 获取执行SQL的数据库连接
		if($bIsMaster){
			$sType=trim(strtolower(substr(ltrim($sSql),0,6)));
		}
		if($bIsMaster || $sType!="select"){// 主服务或者是非查询，那么连接写服务器
			$hDbConnect=$this->writeConnect();
		}else{// 否则连接读服务器
			$hDbConnect=$this->readConnect();
		}
		if(!$hDbConnect || !is_resource($hDbConnect)){
			$this->_hCurrentConnect=null;
			$this->errorMessage(sprintf("Not availability db connection. Query SQL:%s",$sSql));
			return;
		}
		$this->_hCurrentConnect=$hDbConnect;// 执行查询
		$this->setLastSql($sSql);// 记录最后查询的sql语句
		$this->_hQueryResult=null;
		if($this->_bIsRuntime){// 是否记录数据库查询时间
			$nStartTime=C::getMicrotime();
			$this->_hQueryResult=mysql_query($sSql,$hDbConnect);
			$nRunTime=C::getMicrotime()- $nStartTime; // 记录sql运行时间
			$this->setQueryTime($nRunTime);
		}else{// 直接查询
			$this->_hQueryResult=mysql_query($sSql,$hDbConnect);
		}
		$this->Q(1);
		if($this->_bLogEnabled){// 记录执行的SQL
			$this->debug();
		}
		if($this->_hQueryResult===false){// 判断数据库查询是否正确
			$this->errorMessage(sprintf("Query sql failed. SQL:%s",$sSql),$hDbConnect);
		}
		return $this->_hQueryResult;
	}
	public function selectDb($sDbName,$hDbHandle=null){
		if($hDbHandle && is_resource($hDbHandle)){// 重新选择一个连接的数据库
			if(!mysql_select_db($sDbName,$hDbHandle)){
				Q::E('Select database:$sDbName failed.');
				return false;
			}
			return true;
		}
		if($this->_hWriteConnect && is_resource($this->_hWriteConnect)){// 重新选择所有连接的数据库&读数据库连接
			if(!mysql_select_db($sDbName,$this->_hWriteConnect)){
				Q::E('Select database:$dbName failed.');
				return false;
			}
		}
		if(is_array($this->_arrHReadConnect && !empty($this->_arrHReadConnect))){// 写数据库连接
			foreach($this->_arrHReadConnect as $hConnect){
				if($hConnect && is_resource($hConnect)){
					if(!mysql_select_db($sDbName,$hConnect)){
						Q::E('Select database:$sDbName failed.');
						return false;
					}
				}
			}
		}
		$this->_arrHConnect=array();// 重设所有数据库连接
		if(is_array($this->_arrHReadConnect) && !empty($this->_arrHReadConnect)){
			$this->_arrHConnect=array_merge($this->_arrHReadConnect);
		}
		$this->_arrHConnect[]=$this->_hWriteConnect;
		$this->_hCurrentConnect=$this->_hWriteConnect;// 将当前连接切换到主服务器
		return true;
	}
	public function databaseVersion($nLinkid=0){
		if(!$nLinkid){
			$nLinkid=$this->_hCurrentConnect;
		}
		if($nLinkid){
			$this->_nVersion=mysql_get_server_info($nLinkid);
		}else{
			$this->_nVersion=mysql_get_server_info();
		}
		return $this->_nVersion;
	}
	public function errorMessage($sMsg='',$hConnect=null){
		if($sMsg=='' && !$hConnect){// 不存在消息返回
			return false;
		}
		$sMsg="MySQL Error:<br/>{$sMsg}";// 错误消息
		if($hConnect && is_resource($hConnect)){
			$sMsg.="<br/>MySQL Message:<br/>".mysql_error($hConnect);
			$sMsg.="<br/>MySQL Code:<br/>".mysql_errno($hConnect);
			$this->_nErrorCode=mysql_errno($hConnect);
		}
		$sMsg.="<br/>MySQL Time:<br/>[". date("Y-m-d H:i:s")."]";
		Q::E($sMsg);
	}
	public function selectLimit($sSql,$nOffset=0,$nLength=30,$arrInput=null,$bLimit=true){
		if($bLimit===true){
			if(!is_null($nOffset)){
				$sSql.=' LIMIT ' .(int)$nOffset;
				if(!is_null($nLength)){
					$sSql.=',' .(int)$nLength;
				}else{
					$sSql.=',18446744073709551615';
				}
			}elseif(!is_null($nLength)){
				$sSql.=' LIMIT ' .(int)$nLength;
			}
		}
		return $this->exec($sSql,$arrInput);
	}
	public function getDatabaseNameList(){
		$sSql="SHOW DATABASES ;";// 执行
		$hResult=$this->query_($sSql);
		if($hResult===false || !is_resource($hResult)){// 失败
			Q::E(Q::L('无法取得数据库名称清单','__QEEPHP__@Q'));
		}
		$arrReturn=array();// 获取结果
		while(($arrRes=mysql_fetch_row($hResult))!==false){
			$arrReturn[]=$arrRes[0];
		}
		return $arrReturn ;
	}
	public function getTableNameList($sDbName=null){
		// 确定数据库
		if($sDbName===null){
			$sQueryDb=$this->getCurrentDb();
		}else{
			$sQueryDb=&$sDbName;
		}
		$sSql="SHOW TABLES;";// 执行
		$hResult=$this->query($sSql,$sQueryDb);
		if($hResult===false || !is_resource($hResult)){// 失败
			Q::E(Q::L('无法取得数据表名称清单','__QEEPHP__@Q'));
			return false;
		}
		$arrReturn=array();
		while(($arrRes=mysql_fetch_row($hResult))!==false){
			$arrReturn[]=$arrRes[0];
		}
		return $arrReturn;
	}
	public function getColumnNameList($sTableName,$sDbName=null){
		if($sDbName===null){// 确定数据库
			$sQueryDb=$this->getCurrentDb();
		}else{
			$sQueryDb=&$sDbName;
		}
		$sSql="SHOW COLUMNS FROM {$sTableName}";// 执行
		$hResult=$this->query($sSql,$sQueryDb);
		if($hResult===false|| !is_resource($hResult)){// 失败
			Q::E(Q::L('无法取得数据表 < %s > 字段名称清单','__QEEPHP__@Q',null,$sTableName));
		}
		$arrReturn=array();
		while(($arrRes=mysql_fetch_object($hResult))!==false){
			if(is_object($arrRes)){// 进一步处理获取主键和自动增加
				$arrRes=get_object_vars($arrRes);
			}
			$arrReturn[]=$arrRes['Field'];// 获取结果
			$sPrimary=$arrRes['Key']=='PRI'?$arrRes['Field']:$sPrimary;
			$sAuto=!empty($arrRes['Extra'])?$arrRes['Field']:$sAuto;
		}
		$this->_sPrimary=$sPrimary;// 获取主键和自动增长
		$this->_sAuto=$sAuto;
		$this->_arrColumnNameList=$arrReturn;
		return $arrReturn;
	}
	public function isDatabaseExists($sDbName){}
	public function isTableExists($sTableName,$sDbName=null){}
	public function getInsertId(){
		$hDbConnect=$this->writeConnect();
		if(($nLastId=mysql_insert_id($hDbConnect))>0){
			return $nLastId;
		}
		return $this->getOne("SELECT LAST_INSERT_ID()",'',true);
	}
	public function getNumRows($hRes=null){
		if(!$hRes || !is_resource($hRes)){
			$hRes=$this->_hQueryResult;
		}
		return mysql_num_rows($hRes);
	}
	public function getAffectedRows(){
		$hDbConnect=$this->writeConnect();
		if(($nAffetedRows=mysql_affected_rows($hDbConnect))>=0){
			return $nAffetedRows;
		}
		return $this->getOne("SELECT ROW_COUNT()","",true);
	}
	public function lockTable($sTableName){
		return $this->query_("LOCK TABLES $sTableName",true);
	}
	public function unlockTable($sTableName){
		return $this->query_("UNLOCK TABLES $sTableName",true);
	}
	public function setAutoCommit($bAutoCommit=false){
		$bAutoCommit=($bAutoCommit?1:0);
		return $this->query_("SET AUTOCOMMIT=$bAutoCommit",true);
	}
	public function startTransaction(){
		// 没有当前数据库连接，直接返回
		if(!$this->_hCurrentConnect){
			return false;
		}
		if($this->_nTransTimes==0 && !$this->query_("BEGIN")){// 数据rollback 支持
			mysql_query('START TRANSACTION',$this->_hCurrentConnect);
		}
		$this->_nTransTimes++;
		return;
	}
	public function endTransaction(){}
	public function commit(){
		if($this->_nTransTimes>0){
			$this->_nTransTimes=0;
			if(!$this->query_("COMMIT",true)){
				return false;
			}
		}
		return $this->setAutoCommit(true);
	}
	public function rollback(){
		if($this->_nTransTimes>0){
			$this->_nTransTimes=0;
			if(!$this->query_("ROLLBACK",true)){
				return false;
			}
		}
		return $this->setAutoCommit(true);
	}
	public function identifier($sName){
		return ($sName!='*')?"`{$sName}`":'*';
	}
	public function qualifyStr($Value){
		if(is_array($Value)){// 数组，递归
			foreach($Value as $nOffset=>$sV){
				$Value[$nOffset]=$this->qualifyStr($sV);
			}
			return $Value;
		}
		if(is_int($Value)){
			return $Value;
		}
		if(is_bool($Value)){
			return $Value?$this->getTrueValue():$this->getFalseValue();
		}
		if(is_null($Value)){// Null值
			return $this->getNullValue();
		}
		if($Value instanceof DbExpression){
			$Value=$Value->makeSql($this);
		}
		return "'".mysql_real_escape_string($Value,$this->getCurrentConnect())."'";
	}
	public function metaColumns($sTableName){
		// 返回查询结果对象
		$oRs=$this->exec(sprintf('SHOW FULL COLUMNS FROM %s',$this->qualifyId($sTableName)));
		$arrRet=array();
		$oRs->_nFetchMode=Db::FETCH_MODE_ASSOC;
		$oRs->_bResultFieldNameLower=true;
		while(($arrRow=$oRs->fetch())!==false){
			$arrField=array();
			$arrField['name']=$arrRow['field'];
			$arrField['pk']=(strtolower($arrRow['key'])=='pri');
			$arrField['auto_incr']=(strpos($arrRow['extra'],'auto_incr')!==false);
			if(!is_null($arrRow['default'])&& strtolower($arrRow['default'])!='null'){
				$arrField['default']=$arrRow['default'];
			}else{
				$arrField['default']=null;
			}
			$arrRet[$arrField['name']]=$arrField;
		}
		return $arrRet;
	}
}
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   使用硬盘文件进行缓存($$)*/
class FileCache{
	protected $_arrOptions=array(
		'serialize'=>true,
		'cache_time'=>86400,
		'cache_path'=>'',
		'cache_prefix'=>'~@',
	);
	static protected $_sStaticHead='<?php die(); ?>';
	static protected $_nStaticHeadLen=15;
	public function __construct(array $arrOptions=null){
		if(!is_null($arrOptions)){
			$this->_arrOptions=array_merge($this->_arrOptions,$arrOptions);
		}
		if(empty($this->_arrOptions['cache_path'])){
			if(!empty($GLOBALS['_commonConfig_']['RUNTIME_FILECACHE_PATH'])){
				$this->_arrOptions['cache_path']=$GLOBALS['_commonConfig_']['RUNTIME_FILECACHE_PATH'];
			}else{
				$this->_arrOptions['cache_path']=APP_RUNTIME_PATH.'/Data';
			}
		}
	}
	public function checkCache($sCacheName,$arrOptions,$nTime=-1){
		$sFilePath=$this->getCacheFilePath($sCacheName,$arrOptions);
		if(!is_file($sFilePath)){
			return true;
		}
 		return ($nTime!==-1 && filemtime($sFilePath)+$nTime<CURRENT_TIMESTAMP);
	}
	public function getCache($sCacheName,array $arrOptions=null){
		$arrOptions=$this->option($arrOptions);
		$sCacheFilePath=$this->getCacheFilePath($sCacheName,$arrOptions);
		clearstatcache();
		if(!is_file($sCacheFilePath)){ 
			return false; 
		}
		$hFp=fopen($sCacheFilePath,'rb');
		if(!$hFp){
			return false;
		}
		flock($hFp,LOCK_SH);
		$nLen=filesize($sCacheFilePath);
		$bMqr=get_magic_quotes_runtime();
		if(version_compare(PHP_VERSION,'5.3.0','<')){
			@set_magic_quotes_runtime(0);
		}
		
		// 头部的16个字节存储了安全代码
		$sHead=fread($hFp,self::$_nStaticHeadLen);
		$nLen-=self::$_nStaticHeadLen;
		
		do{
			// 检查缓存是否已经过期
			if($this->checkCache($sCacheName,$arrOptions,$arrOptions['cache_time'])){
				$EncryptTest=null;
				$Data=false;
				break;
			}
			if($nLen>0){
				$Data=fread($hFp,$nLen);
			}else{
				$Data=false;
			}
			if(version_compare(PHP_VERSION,'5.3.0','<')){
				@set_magic_quotes_runtime($bMqr);
			}
		}while(false);
		flock($hFp,LOCK_UN);
		fclose($hFp);
		if($Data===false){
			return false;
		}
		// 解码
		if($arrOptions['serialize']){
			$Data=unserialize($Data);
		}
		return $Data;
	}
	public function setCache($sCacheName,$Data,array $arrOptions=null){
		$arrOptions=$this->option($arrOptions);
		if($arrOptions['serialize']){
			$Data=serialize($Data);
		}
		$Data=self::$_sStaticHead.$Data;
		$sCacheFilePath=$this->getCacheFilePath($sCacheName,$arrOptions);
		$this->writeData($sCacheFilePath,$Data);
	}
	public function deleleCache($sCacheName,array $arrOptions=null){
		$arrOptions=$this->option($arrOptions);
		$sCacheFilePath=$this->getCacheFilePath($sCacheName,$arrOptions);
		if($this->existCache($sCacheName,$arrOptions)){
			unlink($sCacheFilePath);
		}
	}
	public function existCache($sCacheName,$arrOptions){
		$sCacheFilePath=$this->getCacheFilePath($sCacheName,$arrOptions);
		return is_file($sCacheFilePath);
	}
	protected function getCacheFilePath($sCacheName,$arrOptions){
		if(!is_dir($arrOptions['cache_path'])){
			C::makeDir($arrOptions['cache_path']);
		}
		return $arrOptions['cache_path'].'/'.$arrOptions['cache_prefix'].$sCacheName.'.php';
	}
	public function writeData($sFileName,$sData){
		!is_dir(dirname($sFileName)) && C::makeDir(dirname($sFileName));
		return file_put_contents($sFileName,$sData,LOCK_EX);
	}
	protected function option(array $arrOptions=null){
		return !is_null($arrOptions)?array_merge($this->_arrOptions,$arrOptions):$this->_arrOptions;
	}
}
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   SQL表达式封装（Learn QP!）($$)*/
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
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   SQL Select 子句（Learn QP!）($$)*/
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
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   DbCond类封装复杂的查询条件（Learn QP!）($$)*/
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
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   MySQL 数据库记录集($$)*/
class DbRecordSetMysql extends DbRecordSet{
	public function free(){
		// 获取查询结果指针
		$hResult=$this->getQueryResultHandle();
		if($hResult){
			mysql_free_result($hResult);
		}
		$this->setQueryResultHandle(null);
	}
	public function fetch(){
		$hResult=$this->getQueryResultHandle();
		if($this->_nFetchMode==Db::FETCH_MODE_ASSOC){// 以关联数组的方式返回数据库结果记录
			$arrRow=mysql_fetch_assoc($hResult);
			if($this->_bResultFieldNameLower && $arrRow){
				$arrRow=array_change_key_case($arrRow,CASE_LOWER);
			}
		}else{// 以索引数组的方式返回结果记录
			$arrRow=mysql_fetch_array($hResult);
		}
		return $arrRow;
	}
}
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   数据库 记录集($$)*/
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
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   Coll实现了一个类型安全的对象集合（Learn QP!）($$)*/
class Coll implements Iterator,ArrayAccess,Countable{
	protected $_sType;
	protected $_arrColl=array();
	protected $_bIsValid=false;
	public function __construct($sType){
		$this->_sType=$sType;
	}
	public static function createFromArray(array $arrObjects,$sType,$bKeepKeys=false){
		$oColl=new self($sType);
		if($bKeepKeys){
			foreach($arrObjects as $offset=>$oObject){$arrColl[$offset]=$oObject;}
		}else{
			foreach($arrObjects as $oObject){$arrColl[]=$oObject;}
		}
		return $arrColl;
	}
	public function values($sPropName){
		$arrReturn=array();
		foreach(array_keys($this->_arrColl)as $offset){
			if(isset($this->_arrColl[$offset]->{$sPropName})){
				$arrReturn[]=$this->_arrColl[$offset]->{$sPropName};
			}
		}
		return $arrReturn;
	}
	public function offsetExists($Offset){
		return isset($this->_arrColl[$Offset]);
	}
	public function offsetGet($Offset){
		if(isset($this->_arrColl[$Offset])){
			return $this->_arrColl[$Offset];
		}
		Q::E(sprintf('Invalid key name %s.',$Offset));
	}
	public function offsetSet($Offset,$Value){
		if(is_null($Offset)){
			$Offset=count($this->_arrColl);
		}
		$this->checkType_($Value);
		while(isset($this->_arrColl[$Offset])){
			$Offset++;
		}
		$this->_arrColl[$Offset]=$Value;
	}
	public function offsetUnset($Offset){
		unset($this->_arrColl[$Offset]);
	}
	public function current(){
		return current($this->_arrColl);
	}
	public function key(){
		return key($this->_arrColl);
	}
	public function next(){
		$this->_bIsValid=(false!==next($this->_arrColl));
	}
	public function rewind(){
		$this->_bIsValid=(false!==reset($this->_arrColl));
	}
	public function valid(){
		return $this->_bIsValid;
	}
	public function count(){
		return count($this->_arrColl);
	}
	public function isEmpty(){
		return empty($this->_arrColl);
	}
	public function first(){
		if(count($this->_arrColl)){
			return reset($this->_arrColl);
		}
		Q::E(Q::L('%s 集合中没有任何对象。','__QEEPHP__@Q',null,$this->_sType));
	}
	public function last(){
		if(count($this->_arrColl)){
			$arrKeys=array_keys($this->_arrColl);
			$key=array_pop($arrKeys);
			return $this->_arrColl[$key];
		}
		Q::E(Q::L('%s 集合中没有任何对象。','__QEEPHP__@Q',null,$this->_sType));
	}
	public function append($Data){
		foreach($Data as $oItem){
			$this->offsetSet(null,$oItem);
		}
		return $this;
	}
	public function search($sPropName,$Needle,$bStrict=false){
		foreach($this->_arrColl as $oItem){
			if($bStrict){
				if($oItem->{$sPropName}===$Needle){
					return $oItem;
				}
			}else{
				if($oItem->{$sPropName}==$Needle){
					return $oItem;
				}
			}
		}
		return null;
	}
	public function toHashMap($sKeyName,$sValueName=null){
		$arrRet=array();
		if($sValueName){
			foreach($this->_arrColl as $oObj){
				$arrRet[$oObj[$sKeyName]]=$oObj[$sValueName];
			}
		}else{
			foreach($this->_arrColl as $oObj){
				$arrRet[$oObj[$sKeyName]]=$oObj;
			}
		}
		return $arrRet;
	}
	public function __call($sMethod,$arrArgs){
		$bNotImplement=false;
		$sMethod=strtolower($sMethod);
		if(method_exists($this->_sType,'collCallback_')){
			$arrMap=call_user_func(array($this->_sType,'collCallback_'));
			$arrMap=array_change_key_case($arrMap,CASE_LOWER);
			if(isset($arrMap[$sMethod])){
				array_unshift($arrArgs,$this->_arrColl);
				return call_user_func_array(array($this->_sType,$arrMap[$sMethod]),$arrArgs);
			}
		}
		$arrResult=array();
		foreach($this->_arrColl as $oObj){
			$arrResult[]=call_user_func_array(array($oObj,$sMethod),$arrArgs);
		}
		return $arrResult;
	}
	protected function checkType_($oObject){
		if(is_object($oObject)){
			if($oObject instanceof $this->_sType){
				return;
			}
			$sType=get_class($oObject);
		}else{
			$sType=gettype($oObject);
		}
		Q::E(Q::L('集合只能容纳 %s 类型的对象，而不是 %s 类型的值.','__QEEPHP__@Q',null ,$this->_sType,$sType));
	}
}
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   Page分页处理类($$)*/
class Page{
	protected $_nCount;
	protected $_nSize;
	protected $_nPage;
	protected $_nPageStart;
	protected $_nPageCount;
	protected $_nPageI;
	protected $_nPageUb;
	protected $_nPageLimit;
	protected static $_oDefaultDbIns=null;
	protected $_sPagename='page';
	protected $_sUrl='';
	protected $_sPrefixUrl='';
	protected $_sParameter;
	protected $_arrDefault=array(
		// URL
		'urlsuffix'=>true,
		// 标签和样式
		'id'=>'pagenav',
		'style'=>'span',
		'current'=>'current',
		'disabled'=>'disabled',
		// 语言
		'total'=>'Total:',
		'none'=>'None',
		'home'=>'Home',
		'first'=>'&laquo; First',
		'previous'=>'Previous',
		'prev'=>'&#8249; Prev',
		'page'=>'Page %d',
		'next'=>'Next',
		'nexts'=>'Next &#8250;',
		'last'=>'Last',
		'lasts'=>'Last &raquo;',
		// 界面配置
		'tpl'=>'{total} {first} {prev} {main} {next} {last}',
	);
	protected function __construct($nCount=0,$nSize=1,$sUrl='',$sParameter='',$nPage=null){
		if($nPage===null && isset($_GET['page'])){
			$nPage=$_GET['page'];
		}
		
		// 页码分析
		$this->_nCount=intval($nCount);
		$this->_nSize=intval($nSize);
		$this->_nPage=intval($nPage);
		
		if($this->_nPage<1){
			$this->_nPage=1;
		}
		if($this->_nCount<1){
			$this->_nPage=0;
		}
		$this->_nPageLimit=($this->_nPage*$this->_nSize)-$this->_nSize;
		$this->_nPageCount=ceil($this->_nCount/$this->_nSize); 
		if($this->_nPageCount<1){
			$this->_nPageCount=1;
		}
		if($this->_nPage>$this->_nPageCount){
			$this->_nPage=$this->_nPageCount; 
		}
		$this->_nPageI=$this->_nPage-2;
		$this->_nPageUb=$this->_nPage+2;
		if($this->_nPageI<1){
			$this->_nPageUb=$this->_nPageUb+(1-$this->_nPageI);
			$this->_nPageI=1;
		}
		if($this->_nPageUb>$this->_nPageCount){
			$this->_nPageI=$this->_nPageI-($this->_nPageUb-$this->_nPageCount);
			$this->_nPageUb=$this->_nPageCount;
			if($this->_nPageI<1){
				$this->_nPageI=1;
			}
		}
		$this->_nPageStart=($nPage-1)*$this->_nSize;
		if($this->_nPageStart<0){
			$this->_nPageStart=0;
		}
	
		// 参数
		$this->_sUrl=$sUrl;
		$this->_sParameter=$sParameter;
	}
	public static function RUN($nCount=0,$nSize=1,$sUrl='',$sParameter='',$nPage=null,$bDefaultIns=true){
		if($bDefaultIns and self::$_oDefaultDbIns){
			return self::$_oDefaultDbIns;
		}
		$oPage=new self($nCount,$nSize,$sUrl,$sParameter,$nPage);// 创建一个分页对象
		if($bDefaultIns){// 设置全局对象
			self::$_oDefaultDbIns=$oPage;
		}
		return $oPage;
	}
	public function P($arrOption=array(),$sPagename='page'){
		// 读取配置
		$arrDefault=$this->_arrDefault;
		if(!empty($arrOption)){
			$arrDefault=array_merge($arrDefault,$arrOption);
		}
		if(!empty($sPagename)){
			$this->_sPagename=$sPagename;
		}
		// 分离前缀
		if(strpos($this->_sUrl,'@~')!==false){
			$arrTemp=explode('@~',$this->_sUrl);
			$this->_sUrl=$arrTemp[1];
			$this->_sPrefixUrl=$arrTemp[0];
		}
		// 当前URL分析
		if(!empty($this->_sUrl)){
			if(strpos($this->_sUrl,'@')===0){
				$sUrl=Q::U(ltrim($this->_sUrl,'@'),false===strpos($this->_sUrl,'{page}')?array($this->_sPagename=>'{page}'):array());
			}else{
				$sDepr=$GLOBALS['_commonConfig_']['URL_PATHINFO_DEPR'];
				$sUrl=str_replace('//','/',rtrim(Q::U('/'.$this->_sUrl,array(),false,false,false===strpos($this->_sUrl,'{page}')?false:true),$sDepr));
				false===strpos($sUrl,'{page}') && $sUrl.=$sDepr.'{page}'.($arrDefault['urlsuffix'] && $GLOBALS['_commonConfig_']['URL_HTML_SUFFIX']?$GLOBALS['_commonConfig_']['URL_HTML_SUFFIX']:'');
			}
		}else{
			if($this->_sParameter && is_string($this->_sParameter)){
				parse_str($this->_sParameter,$arrParameter);
			}elseif(is_array($this->_sParameter)){
				$arrParameter=$this->_sParameter;
			}elseif(empty($this->_sParameter)){
				if(isset($_GET['parameter'])){
					unset($_GET['parameter']);
				}
				$arrVar=!empty($_POST)?$_POST:$_GET;
				if(empty($arrVar)){
					$arrParameter=array();
				}else{
					$arrParameter=$arrVar;
				}
			}
			$arrParameter[$this->_sPagename]='{page}';
			$sUrl=Q::U($this->_sPrefixUrl?$this->_sPrefixUrl.'~@':'',$arrParameter);
		}
		$this->_sUrl=$sUrl;
		
		// 分页数据
		$arrPagedata=array();
		// 初始化
		$bLiStyle=$arrDefault['style']=='li'?true:false;
		$sLiheader=$sLifooter=$sLiAheader=$sLiAfooter='';
		if($bLiStyle){
			$sLiheader='<li>';
			$sLifooter='</li>';
			$sLiAheader='<a>';
			$sLiAfooter='</a>';
		}
		// 头部(header)
		$arrIddata=explode('@',$arrDefault['id']);
		$arrPagedata['header']='<div id="'.$arrIddata[0].'" class="'.(isset($arrIddata[1])?$arrIddata[1]:$arrIddata[0]).'">';
		if($bLiStyle){
			$arrPagedata['header'].='<ul>';
		}
		// 总记录(total)
		$arrPagedata['total']="<{$arrDefault['style']} class=\"{$arrDefault['disabled']}\">".$sLiAheader;
		if($this->_nCount>0){
			$arrPagedata['total'].=$arrDefault['total'].$this->_nCount;
		}else{
			$arrPagedata['total'].=$arrDefault['none'];
		}
		$arrPagedata['total'].=$sLiAfooter."</{$arrDefault['style']}>";
		// 页面
		$arrPagedata['first']=$arrPagedata['prev']=$arrPagedata['main']=$arrPagedata['next']=$arrPagedata['last']='';
		if($this->_nPageCount>1){// 页码
			// 第一页和上一页(first && prev)
			if($this->_nPage!=1){
				$arrPagedata['first']=$sLiheader."<a href=\"{$this->pageReplace(1)}\" title=\"{$arrDefault['home']}\" >{$arrDefault['first']}</a>".$sLifooter;
				$arrPagedata['prev']=$sLiheader."<a href=\"{$this->pageReplace($this->_nPage-1)}\" title=\"{$arrDefault['previous']}\" >{$arrDefault['prev']}</a>".$sLifooter;
			}
			// 主页码(main)
			for($nI=$this->_nPageI;$nI<=$this->_nPageUb;$nI++){
				if($this->_nPage==$nI){
					$arrPagedata['main'].="<{$arrDefault['style']} class=\"{$arrDefault['current']}\">{$sLiAheader}{$nI}{$sLiAfooter}</{$arrDefault['style']}>";
				}else{
					$arrPagedata['main'].=$sLiheader."<a href=\"{$this->pageReplace($nI)}\" title=\"".sprintf($arrDefault['page'],$nI)."\">{$nI}</a>".$sLifooter;
				}
			}
			// 下一页和最后一页(next && last)
			if($this->_nPage!=$this->_nPageCount){
				$arrPagedata['next']=$sLiheader."<a href=\"{$this->pageReplace($this->_nPage+1)}\" title=\"{$arrDefault['next']}\" >{$arrDefault['nexts']}</a>".$sLifooter;
				$arrPagedata['last']=$sLiheader."<a href=\"{$this->pageReplace($this->_nPageCount)}\" title=\"{$arrDefault['last']}\" >{$arrDefault['lasts']}</a>".$sLifooter;
			}
		}
		// 结束(footer)
		$arrPagedata['footer']='';
		if($bLiStyle){
			$arrPagedata['footer'].='</ul>';
		}
		$arrPagedata['footer'].='</div>';
		// 返回
		$sPagenav=$arrPagedata['header'];
		$sPagenav.=str_replace(
			array('{total}','{first}','{prev}','{main}','{next}','{last}'),
			array($arrPagedata['total'],$arrPagedata['first'],$arrPagedata['prev'],$arrPagedata['main'],$arrPagedata['next'],$arrPagedata['last']),$arrDefault['tpl']);
		$sPagenav.=$arrPagedata['footer'];
		return $sPagenav;
	}
	public function setParameter($sParameter){
		$this->_sParameter=$sParameter;
		return $this;
	}
	public function S(){
		return $this->_nPageStart;
	}
	public function N(){
		return $this->_nSize;
	}
	public function O($sName,$sValue=null){
		if(isset($this->_arrDefault[$sName])){
			if($sValue===null){
				return $this->_arrDefault[$sName];
			}else{
				$this->_arrDefault[$sName]=$sValue;
			}
		}
	}
	public function getPage(){
		return $this->_nPageCount;
	}
	protected function pageReplace($nPage){
		return str_replace(array(urlencode('{page}'),'{page}'),$nPage,$this->_sUrl);
	}
}
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   Web URL分析器($$)*/
/** 支持的URL模式 */
define('URL_COMMON',0);// 普通模式
define('URL_PATHINFO',1);// PATHINFO模式
define('URL_REWRITE',2);// REWRITE模式
define('URL_COMPAT',3);// 兼容模式
class Url{
	protected $_sLastRouterName=null;
	protected $_arrLastRouteInfo=array();
	static private $_sBaseUrl;
	static private $_sBaseDir;
	static private $_sRequestUrl;
	private $_oRouter=null;
	public $_sControllerName;
	public $_sActionName;
	public $_sAppName;
	public function parseUrl(){
		$_SERVER['REQUEST_URI']=isset($_SERVER['REQUEST_URI'])?$_SERVER['REQUEST_URI']:$_SERVER["HTTP_X_REWRITE_URL"];//For IIS
		
		$sDepr=$GLOBALS['_commonConfig_']['URL_PATHINFO_DEPR'];
		if($GLOBALS['_commonConfig_']['URL_MODEL']){
			$this->filterPathInfo();
			if($GLOBALS['_commonConfig_']['START_ROUTER']){
				$arrRouterInfo=$this->getRouterInfo();
				if(empty($arrRouterInfo)){
					$_GET=array_merge($this->parsePathInfo(),$_GET);
				}else{
					$_GET=array_merge($this->getRouterInfo(),$_GET);
				}
			}else{
				$_GET=array_merge($this->parsePathInfo(),$_GET);
			}
		}else{
			if($GLOBALS['_commonConfig_']['START_ROUTER']){
				$arrRouterInfo=$this->getRouterInfo();
				if(!empty($arrRouterInfo)){
					$_GET=array_merge($arrRouterInfo,$_GET);
				}else{
					$_GET=array_merge($this->getRouterInfo(),$_GET);
				}
			}else{
				$_GET=array_merge($this->parsePathInfo(),$_GET);
			}
		}
		// 行为标签
		Q::tag('url');
		if(!defined('APP_NAME')){
			define('APP_NAME',$_GET['app']=$this->getApp('app'));
		}
		define('MODULE_NAME',$_GET['c']=$this->getControl('c'));
		define('ACTION_NAME',$_GET['a']=$this->getAction('a'));
		
		// 当前页面地址
		define('__SELF__',$_SERVER['REQUEST_URI']);
		// 解析__APP__路径
		$this->parseAppPath();
		define('__APP__',PHP_FILE);
		define('__URL__',__APP__.'/'.MODULE_NAME);
		define('__ACTION__',__URL__.$sDepr.ACTION_NAME);
		$_REQUEST=array_merge($_POST,$_GET);
	}
	public function parseAppPath(){
		define('IS_CGI',substr(PHP_SAPI,0,3)=='cgi'?1:0);
		define('IS_CLI',PHP_SAPI=='cli'?1:0);
		if(!IS_CLI){
			if(!defined('_PHP_FILE_')){/** PHP 文件 */
				if(IS_CGI){
					$arrTemp=explode('.php',$_SERVER["PHP_SELF"]);// CGI/FASTCGI模式下
					define('_PHP_FILE_',rtrim(str_replace($_SERVER["HTTP_HOST"],'',$arrTemp[0].'.php'),'/'));
				}else{
					define('_PHP_FILE_',rtrim($_SERVER["SCRIPT_NAME"],'/'));
				}
			}
			/** 网站URL根目录 */
			if(strtoupper(APP_NAME)==strtoupper(basename(dirname(_PHP_FILE_)))){ 
				if(defined('APPNAME_IS_PARENTDIR') && APPNAME_IS_PARENTDIR===FALSE){
					$sRoot=dirname(_PHP_FILE_);
				}else{
					$sRoot=dirname(dirname(_PHP_FILE_));
				}
			}else{
				$sRoot=dirname(_PHP_FILE_);
			}
			$sRoot=($sRoot=='/' || $sRoot=='\\')?'':$sRoot;
			if(defined('__ROOTS__')){
				$sRoot=$sRoot.'/'.__ROOTS__;
			}
			define('__ROOT__',$sRoot);
		}
		$nUrlModel=$GLOBALS['_commonConfig_']['URL_MODEL'];
		if($GLOBALS['_commonConfig_']['URL_MODEL']===URL_REWRITE){// 如果为重写模式
			$sUrl=dirname(_PHP_FILE_);
			if($sUrl=='\\'){
				$sUrl='/';
			}
			define('PHP_FILE',$sUrl);
		}elseif($GLOBALS['_commonConfig_']['URL_MODEL']===URL_COMPAT){
			define('PHP_FILE',_PHP_FILE_.'?s=');
		}else{
			define('PHP_FILE',_PHP_FILE_);
		}
	}
	private function getRouterInfo(){
		if(is_null($this->_oRouter)){
			$this->_oRouter=new Router($this);
		}
		$this->_oRouter->import();// 导入路由规则
		$this->_arrLastRouteInfo=$this->_oRouter->G();// 获取路由信息
		$this->_sLastRouterName=$this->_oRouter->getLastRouterName();
		return $this->_arrLastRouteInfo;
	}
	public function getLastRouterName(){
		return $this->_sLastRouterName;
	}
	public function getLastRouterInfo(){
		return $this->_arrLastRouteInfo;
	}
	public function requestUrl(){
		if(self::$_sRequestUrl){
			return self::$_sRequestUrl;
		}
		if(isset($_SERVER['HTTP_X_REWRITE_URL'])){
			$sUrl=$_SERVER['HTTP_X_REWRITE_URL'];
		}elseif(isset($_SERVER['REQUEST_URI'])){
			$sUrl=$_SERVER['REQUEST_URI'];
		}elseif(isset($_SERVER['ORIG_PATH_INFO'])){
			$sUrl=$_SERVER['ORIG_PATH_INFO'];
			if(!empty($_SERVER['QUERY_STRING'])){
				$sUrl.='?'.$_SERVER['QUERY_STRING'];
			}
		}else{
			$sUrl='';
		}
		self::$_sRequestUrl=$sUrl;
		return $sUrl;
	}
	public function baseDir(){
		if(self::$_sBaseDir){
			return self::$_sBaseDir;
		}
		$sBaseUrl=$this->baseUrl();
		if(substr($sBaseUrl,-1,1)=='/'){
			$sBaseDir=$sBaseUrl;
		}else{
			$sBaseDir=dirname($sBaseUrl);
		}
		self::$_sBaseDir=rtrim($sBaseDir,'/\\').'/';
		return self::$_sBaseDir;
	}
	public function baseUrl(){
		if(self::$_sBaseUrl){
			return self::$_sBaseUrl;
		}
		$sFileName=basename($_SERVER['SCRIPT_FILENAME']);
		if(basename($_SERVER['SCRIPT_NAME'])===$sFileName){
			$sUrl=$_SERVER['SCRIPT_NAME'];
		}elseif(basename($_SERVER['PHP_SELF'])===$sFileName){
			$sUrl=$_SERVER['PHP_SELF'];
		}elseif(isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME'])===$sFileName){
			$sUrl=$_SERVER['ORIG_SCRIPT_NAME'];
		}else{
			$sPath=$_SERVER['PHP_SELF'];
			$arrSegs=explode('/',trim($_SERVER['SCRIPT_FILENAME'],'/'));
			$arrSegs=array_reverse($arrSegs);
			$nIndex=0;
			$nLast=count($arrSegs);
			$sUrl='';
			do{
				$sSeg=$arrSegs[$nIndex];
				$sUrl='/'.$sSeg.$sUrl;
				++ $nIndex;
			}while(($nLast>$nIndex) && (false!==($nPos=strpos($sPath,$sUrl))) && (0!=$nPos));
		}
		$sRequestUrl=$this->requestUrl();
		if(0===strpos($sRequestUrl,$sUrl)){
			self::$_sBaseUrl=$sUrl;
			return self::$_sBaseUrl;
		}
		if(0===strpos($sRequestUrl,dirname($sUrl))){
			self::$_sBaseUrl=rtrim(dirname($sUrl),'/').'/';
			return self::$_sBaseUrl;
		}
		if(!strpos($sRequestUrl,basename($sUrl))){
			return '';
		}
		if((strlen($sRequestUrl)>=strlen($sUrl)) && ((false!==($nPos=strpos($sRequestUrl,$sUrl))) && ($nPos!==0))){
			$sUrl=substr($sRequestUrl,0,$nPos+strlen($sUrl));
		}
		self::$_sBaseUrl=rtrim($sUrl,'/').'/';
		return self::$_sBaseUrl;
	}
	public function pathinfo(){
		if(!empty($_SERVER['PATH_INFO'])){
			return $_SERVER['PATH_INFO'];
		}
		$sBaseUrl=$this->baseUrl();
		if(null===($sRequestUrl=$this->requestUrl())){
			return '';
		}
		if(($nPos=strpos($sRequestUrl,'?'))>0){
			$sRequestUrl=substr($sRequestUrl,0,$nPos);
		}
		if((null!==$sBaseUrl) && (false===($sPathinfo=substr($sRequestUrl,strlen($sBaseUrl))))){
			$sPathinfo='';
		}elseif(null===$sBaseUrl){
			$sPathinfo=$sRequestUrl;
		}
		return $sPathinfo;
	}
	public function parsePathInfo(){
		$arrPathInfo=array();
		$sPathInfo=&$_SERVER['PATH_INFO'];
		if($GLOBALS['_commonConfig_']['URL_PATHINFO_MODEL']==2){
			$arrPaths=explode($GLOBALS['_commonConfig_']['URL_PATHINFO_DEPR'],trim($sPathInfo,'/'));
			if($arrPaths[0]=='app'){
				array_shift($arrPaths);
				$arrPathInfo['app']=array_shift($arrPaths);
			}
			if(!isset($_GET['c'])){// 还没有定义模块名称
				$arrPathInfo['c']=array_shift($arrPaths);
			}
			$arrPathInfo['a']=array_shift($arrPaths);
			for($nI=0,$nCnt=count($arrPaths);$nI<$nCnt;$nI++){
				if(isset($arrPaths[$nI+1])){
					$arrPathInfo[$arrPaths[$nI]]=(string)$arrPaths[++$nI];
				}elseif($nI==0){
					$arrPathInfo[$arrPathInfo['a']]=(string)$arrPaths[$nI];
				}
			}
		}else{
			$bRes=preg_replace('@(\w+)'.$GLOBALS['_commonConfig_']['URL_PATHINFO_DEPR'].'([^,\/]+)@e','$arrPathInfo[\'\\1\']="\\2";',$sPathInfo);
		}
		return $arrPathInfo;
	}
	protected function getControl($sVar){
		$sControl=(!empty($_GET[$sVar])?$_GET[$sVar]:$GLOBALS['_commonConfig_']['DEFAULT_CONTROL']);
		$this->_sControllerName=strtolower($sControl);
		return $this->_sControllerName;
	}
	protected function getAction($sVar){
		$sAction=!empty($_POST[$sVar])?$_POST[$sVar]:(!empty($_GET[$sVar])?$_GET[$sVar]:$GLOBALS['_commonConfig_']['DEFAULT_ACTION']);
		$this->_sActionName=strtolower($sAction);
		return $this->_sActionName;
	}
	protected function getApp($sVar){
		$sApp=!empty($_POST[$sVar])?$_POST[$sVar]:(!empty($_GET[$sVar])?$_GET[$sVar]:basename(APP_PATH));
		$this->_sAppName=strtolower($sApp);
		return $this->_sAppName;
	}
	public function control(){
		return $this->_sControllerName;
	}
	public function action(){
		return $this->_sActionName;
	}
	public function filterPathInfo(){
		if(!empty($_GET['s'])){
			$sPathInfo=$_GET['s'];
			unset($_GET['s']);
		}else{
			$sPathInfo=$this->pathinfo();
		}
		$sPathInfo=$this->clearHtmlSuffix($sPathInfo);
		$sPathInfo= empty($sPathInfo)?'/':$sPathInfo;
		$_SERVER['PATH_INFO']=$sPathInfo;
	}
	protected function clearHtmlSuffix($sVal){
		if($GLOBALS['_commonConfig_']['URL_HTML_SUFFIX'] && !empty($sVal)){
			$sSuffix=substr($GLOBALS['_commonConfig_']['URL_HTML_SUFFIX'],1);
			$sVal=preg_replace('/\.'.$sSuffix.'$/','',$sVal);
		}
		return $sVal;
	}
}
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   系统路由解析类($$)*/
class Router{
	protected $_sLastRouterName=null;
	protected $_arrLastRouteInfo=array();
	protected $_arrRouters=array();
	protected $_oUrlParseObj=null;
	public function __construct($oUrlParseObj=null){
		if(!$GLOBALS['_commonConfig_']['START_ROUTER']){
			return false;
		}
		
		if(is_null($oUrlParseObj)){
			$this->_oUrlParseObj=new Url();
		}else{
			$this->_oUrlParseObj=$oUrlParseObj;
		}
	}
	public function G($sRouterName=null){
		$sRouterName=$sRouterName?$sRouterName:$this->getRouterName();
		$arrRouteInfo=array();
		if(!empty($this->_arrRouters)){
			if(isset($this->_arrRouters[$sRouterName])){
				if(!strpos($sRouterName,'@')){
					$arrRouteInfo=$this->getNormalRoute($sRouterName,$this->_arrRouters[$sRouterName]);
				}else{
					$arrRouteInfo=$this->getFlowRoute($sRouterName,$this->_arrRouters[$sRouterName]);
				}
			}else{
				$sRegx=trim($_SERVER['PATH_INFO'],'/');
				foreach($this->_arrRouters as $sKey=>$sRouter){
					if(0===strpos($sKey,'/') && preg_match($sKey,$sRegx,$arrMatches)){
						$arrRouteInfo=$this->getRegexRoute($arrMatches,$sRouter,$sRegx);
						break;
					}
				}
			}
		}
		$this->_arrRouteInfo=$arrRouteInfo;
		return $this->_arrRouteInfo;
	}
	public function import(array $arrRouters=null){
		if(!$GLOBALS['_commonConfig_']['START_ROUTER']){
			return false;
		}
		if(is_null($arrRouters)){
			$arrRouters=$GLOBALS['_commonConfig_']['_ROUTER_'];
		}
		$this->_arrRouters=array_merge($this->_arrRouters,$arrRouters);
		return $this;
	}
	public function add($sRouteName,array $arrRule){
		$this->_arrRouters[$sRouteName]=$arrRule;
		return $this;
	}
	public function remove($sRouteName){
		unset($this->_arrRouters[$sRouteName]);
		return $this;
	}
	public function get($sRouteName){
		return $this->_arrRouters[$sRouteName];
	}
	public function getLastRouterName(){
		return $this->_sLastRouterName;
	}
	public function getLastRouterInfo(){
		 return $this->_arrLastRouteInfo;
	}
	private function parseUrl($Route){
		if(is_string($Route)){
			$arrArray=array_filter(explode('/',$Route));
		}else{
			$arrArray=$Route;
		}
		if(count($arrArray)!==2){
			Q::E('$Route parameter format error,claiming the $arrArray the number of elements equal 2.');
		}
		$arrVar=array();
		$arrVar['a']=array_pop($arrArray);
		$arrVar['c']=array_pop($arrArray);
		return $arrVar;
	}
	private function getRouterName(){
		if(isset($_GET['r'])){
			$sRouteName=$_GET['r'];
			unset($_GET['r']);
		}else{
			$sPathInfo=&$_SERVER['PATH_INFO'];
			$arrPaths=explode($GLOBALS['_commonConfig_']['URL_PATHINFO_DEPR'],trim($sPathInfo,'/'));
			if(isset($arrPaths[0]) && $arrPaths[0]=='app'){
				array_shift($arrPaths);
				$_GET['app']=array_shift($arrPaths);
			}
			$sRouteName=array_shift($arrPaths);
		}
		$sRouteName=strtolower($sRouteName);
		if(isset($this->_arrRouters[$sRouteName.'@'])){
			$sRouteName=$sRouteName.'@';
		}
		$this->_sLastRouterName=$sRouteName;
		return $this->_sLastRouterName;
	}
	private function getNormalRoute($sRouteName,array $arrRule){
		if(isset($arrRule['regex'])){
			return $this->getRegexRoute_($sRouteName,$arrRule);
		}else{
			return $this->getSimpleRoute_($sRouteName,$arrRule);
		}
	}
	private function getFlowRoute($sRouteName,array $arrRule){
		foreach($arrRule as $arrRule){
			$arrVar=$this->getNormalRoute($sRouteName,$arrRule);
			if(!empty($arrVar)){
				return $arrVar;
			}
		}
		return array();
	}
	private function getSimpleRoute_($sRouteName,$arrRule){
		if(count($arrRule)<2 || count($arrRule)>5){
			Q::E('$arrRule parameter must be greater than or equal 2,less than or equal 5.');
		}
		$arrVar=$this->parseUrl($arrRule[0]);
		if($GLOBALS['_commonConfig_']['URL_MODEL']===URL_COMMON){
			return $arrVar;
		}
		$sPathInfo=&$_SERVER['PATH_INFO'];
		$sDepr=$GLOBALS['_commonConfig_']['URL_PATHINFO_DEPR'];
		$sRegx=str_replace('/',$sDepr,rtrim($sPathInfo,'/'));
		$arrPaths=array_filter(explode($sDepr,trim(str_ireplace($sDepr.strtolower($sRouteName).$sDepr,$sDepr,$sRegx),$sDepr)));
		if(isset($arrPaths[0]) && $arrPaths[0]=='app'){
			array_shift($arrPaths);
			$arrVar['app']=array_shift($arrPaths);
		}
		
		if(!empty($arrRule[1]) && in_array($arrRule[1],$arrPaths)){
			foreach($arrPaths as $nKey=>$sValue){
				if($sValue==$arrRule[1]){
					unset($arrPaths[$nKey]);
				}
			}
		}
		$arrVars=explode(',',$arrRule[1]);
		for($nI=0;$nI<count($arrVars);$nI++){
			$arrVar[$arrVars[$nI]]=array_shift($arrPaths);
		}
		$bResult=preg_replace('@(\w+)\/([^,\/]+)@e','$arrVar[\'\\1\']="\\2";',implode('/',$arrPaths));
		
		$arrParams=array();
		if(isset($arrRule[2])){
			parse_str($arrRule[2],$arrParams);
			$arrVar=array_merge($arrVar,$arrParams);
		}
		return $arrVar;
	}
	private function getRegexRoute_($sRouteName,$arrRule){
		if(count($arrRule)<3 || count($arrRule)>6){
			Q::E('$arrRule parameter must be greater than or equal 3, less than or equal 6.');
		}
		$sPathInfo=&$_SERVER['PATH_INFO'];
		$sDepr=$GLOBALS['_commonConfig_']['URL_PATHINFO_DEPR'];
		$sRegx=trim($sPathInfo,'/');
		$sRegx=explode($sDepr,$sRegx);
		if($sRegx[0]=='app'){
			array_shift($sRegx);
			$_GET['app']=array_shift($sRegx);
		}
		$sRegx=implode($sDepr,$sRegx);
		$sRegx=ltrim($sRegx,strtolower(rtrim($sRouteName,'@')));
		$sTheRegex=array_shift($arrRule);
		$arrMatches=array();
		if(preg_match($sTheRegex,$sRegx,$arrMatches)){
			$arrVar=$this->parseUrl($arrRule[0]);
			if($GLOBALS['_commonConfig_']['URL_MODEL']===URL_COMMON){
				return $arrVar;
			}
			$arrVars=explode(',',$arrRule[1]);
			for($nI=0;$nI<count($arrVars);$nI++){
				$arrVar[$arrVars[$nI]]=$arrMatches[$nI+1];
			}
			$bResult=preg_replace('@(\w+)\/([^,\/]+)@e','$arrVar[\'\\1\']="\\2";',trim(str_replace($arrMatches[0],'',$sRegx),'\/'));
			
			$arrParams=array();
			if(isset($arrRule[2])){
				parse_str($arrRule[2],$arrParams);
				$arrVar=array_merge($arrVar,$arrParams);
			}
			return $arrVar;
		}
		return array();
	}
	/**
	 * 全局解析规则 TP
	 */
	private function getRegexRoute($arrMatches,$sRouter,$sRegx){
		$sUrl=is_array($sRouter)?$sRouter[0]:$sRouter;
		$sUrl=preg_replace('/:(\d+)/e','$arrMatches[\\1]',$sUrl);
		if(0===strpos($sUrl,'/') || 0===strpos($sUrl,'http')){
			header("Location:{$sUrl}",true,is_array($sRouter) && !empty($sRouter[1])?$sRouter[1]:301);
			exit;
		}else{
			$arrVar=array();
			if(false!==strpos($sUrl,'?')){
				$arrInfo=parse_url($sUrl);
				$arrPath=explode('/',$arrInfo['path']);
				parse_str($arrInfo['query'],$arrVar);
			}elseif(strpos($sUrl,'/')){
				$arrPath=explode('/',$sUrl);
			}else{
				parse_str($sUrl,$arrVar);
			}
			if(isset($arrPath)) {
				$arrVar['a']=array_pop($arrPath);
				if(!empty($arrPath)){
					$arrVar['c']=array_pop($arrPath);
				}
				if(!empty($arrPath)){
					$var['app']=array_pop($arrPath);
				}
			}
			$sRegx=substr_replace($sRegx,'',0,strlen($arrMatches[0]));
			if($sRegx){
				preg_replace('@(\w+)\/([^,\/]+)@e','$arrVar[strtolower(\'\\1\')]=strip_tags(\'\\2\');',$sRegx);
			}
			if(is_array($sRouter) && !empty($sRouter[1])){
				parse_str($sRouter[1],$arrParams);
				$arrVar=array_merge($arrVar,$arrParams);
			}
		}
		return $arrVar;
	}
}