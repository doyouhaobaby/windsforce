<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   Api公用初始化文件($$)*/

/** 应用路径解析 */
if(isset($_GET['app'])){
	$sAppName=strtolower(str_replace(array('/','\\'),'',strip_tags(urldecode($_GET['app']))));
}else{
	$sAppName='home';
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
