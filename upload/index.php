<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   WindsForce 主页入口文件($$)*/

error_reporting(E_ERROR|E_PARSE|E_STRICT);
//error_reporting(E_ALL);
//define('QEEPHP_DEBUG',TRUE);

/** Defined the version of WindsForce */
define('WINDSFORCE_SERVER_VERSION','2.0');
define('WINDSFORCE_SERVER_RELEASE','20141007');
define('WINDSFORCE_SERVER_BUG','1.0');

/** 安装&升级 */
if(!is_file('user/lock/Install.lock.php')){
	header('location:~@~/install/index.php');
}

/** 系统应用路径定义 */
define('WINDSFORCE_PATH',getcwd());

/** 定义Api环境 */
define('IN_APISELF',true);

/** 应用路径解析，还可以加强 */
if(isset($_GET['app'])){
	$sAppName=strtolower(str_replace(array('/','\\'),'',strip_tags(urldecode($_GET['app']))));
}else{
	// 默认设置（可以手动指定）
	$sUrlpathinfodepr='/';
	$sDefaultAppname='group';
	$sUrlHtmlSuffix='.html';
	
	if(!empty($_SERVER['PATH_INFO'])){
		$sPathinfo=$_SERVER['PATH_INFO'];
	}else{
		$sPhpfile='';
		if(substr(PHP_SAPI,0,3)=='cgi'){
			$arrTemp=explode('.php',$_SERVER["PHP_SELF"]);// CGI/FASTCGI模式下
			$sPhpfile=rtrim(str_replace($_SERVER["HTTP_HOST"],'',$arrTemp[0].'.php'),'/');
		}else{
			$sPhpfile=rtrim($_SERVER["SCRIPT_NAME"],'/');
		}

		if(!isset($_SERVER['REQUEST_URI'])){
			$_SERVER['REQUEST_URI']=$_SERVER['PHP_SELF'];
			if(isset($_SERVER['QUERY_STRING'])){
				$_SERVER['REQUEST_URI'].='?'.$_SERVER['QUERY_STRING'];
			}
		}

		$sRequesturi=str_replace('index.php/','',$_SERVER['REQUEST_URI']);
		$sPhpfile=str_replace('index.php','',$sPhpfile);
		if($sPhpfile=='/'){
			$sPathinfo=ltrim($sRequesturi,'/');
		}else{
			$sPathinfo=str_replace($sPhpfile,'',$sRequesturi);
		}
	}

	if(!empty($sPathinfo)){
		$arrPathinfos=explode($sUrlpathinfodepr,str_replace($sUrlHtmlSuffix,'',trim($sPathinfo,'/')));
		if(isset($arrPathinfos[1]) && $arrPathinfos[0]=='app'){
			$sAppName=$arrPathinfos[1];
		}else{
			$sAppName=$sDefaultAppname;
		}
	}else{
		$sAppName=$sDefaultAppname;
	}
}

/** 项目及项目路径定义 */
define('APP_NAME',$sAppName);
define('APP_PATH',WINDSFORCE_PATH.'/System/app/'.APP_NAME);

/** 项目运行时路径及数据库表缓存路径 */
define('DIS_RUNTIME_PATH',WINDSFORCE_PATH.'/~@~/dis');
define('APP_RUNTIME_PATH',WINDSFORCE_PATH.'/~@~/app/'.APP_NAME);
define('DB_META_CACHED_PATH',WINDSFORCE_PATH.'/~@~/field');
define('APP_INIT_PATH',WINDSFORCE_PATH.'/System/common/Common.inc.php');

/** 项目语言包路径定义 */
define('__COMMON_LANG__',WINDSFORCE_PATH.'/user/language');

/** 项目模板路径定义 */
define('__STATICS__','System/app/'.APP_NAME.'/Static');
define('__THEMES__','System/app/'.APP_NAME.'/Theme');

/** 项目编译锁定文件定义 */
define('APP_RUNTIME_LOCK',WINDSFORCE_PATH.'/user/lock/~Runtime.inc.lock');

/** 加载框架编译版本和设置父级目录为应用名 */
//define('STRIP_RUNTIME_SPACE',false);
define('APPNAME_IS_PARENTDIR',false);

/** 去掉模板空格 */
define('TMPL_STRIP_SPACE',true);

/** 载入框架 */
require(WINDSFORCE_PATH.'/System/include/QeePHP/~@~.php');
App::RUN();
