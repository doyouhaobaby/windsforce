<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   WindsForce API入口($$)*/

error_reporting(E_ERROR|E_PARSE|E_STRICT);
//error_reporting(E_ALL);
//define('QEEPHP_DEBUG',TRUE);

/** Defined the version of WindsForce */
define('WINDSFORCE_SERVER_VERSION','2.0');
define('WINDSFORCE_SERVER_RELEASE','20140615');
define('WINDSFORCE_SERVER_BUG','1.0');

/** 安装&升级 */
if(!is_file('../../user/lock/Install.lock.php')){
	header('location:../../~@~/install/index.php');
}

/** 系统应用路径定义 */
define('WINDSFORCE_PATH',dirname(dirname(getcwd())));

/** 定义Api环境 */
define('IN_API',true);

/** API分为两种：(指向应用的API+Api目录) */
$sApp=!empty($_GET['app'])?strtolower(trim($_GET['app'])):'';// 第一种:应用API
$sModule=!empty($_GET['c'])?strtolower(trim($_GET['c'])):'api';
$sAction=!empty($_GET['a'])?strtolower(trim($_GET['a'])):'index';
$sApi=!empty($_GET['api'])?strtolower(trim($_GET['api'])):'';// 第二种:Api目录
$sModuleTwo=!empty($_GET['module'])?strtolower(trim($_GET['module'])):'';

if(!$sApp && !$sApi){
	exit('Error!');
}

/** 
 * (一):应用API将直接跳转
 * 只能访问不需要IN_API的接口
 * http://www.youdomain.com/user/url/api.php?app=group&c=api&a=recommend_group&num=5&cnum=20&type=xml
 */
if($sApp){
	// 处理URL
	if(isset($_GET['app'])){
		unset($_GET['app']);
	}
	
	if(isset($_GET['c'])){
		unset($_GET['c']);
	}

	if(isset($_GET['a'])){
		unset($_GET['a']);
	}

	$sUrl=http_build_query($_GET);
	$sUrl="../../index.php?app={$sApp}&c={$sModule}&a={$sAction}".($sUrl?'&'.$sUrl:'');

	// 跳转
	header("Location:{$sUrl}");
}else{
/** 
 * (二):Api目录
 * http://www.youdomain.com/user/url/api.php?api=user&module=new&num=10&type=xml
 */
	if(empty($sModule)){
		$sApifile=WINDSFORCE_PATH.'/System/api/'.$sApi.'.php';
	}else{
		$sApifile=WINDSFORCE_PATH.'/System/api/'.$sApi.'/'.ucfirst($sModuleTwo).'.php';
	}

	if(!is_file($sApifile)){
		exit('Error File!');
	}

	require_once($sApifile);
}
