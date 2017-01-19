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

//[RUNTIME]
/** 已经生成缓存 */
if(is_file(Q_PATH.'/~@.php')){
	exit('Please load the ~@.php instead of Q.php');
}else{
	require_once(Q_PATH.'/Common_/InitRuntime.inc.php');
	exit();
}
//[/RUNTIME]

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
