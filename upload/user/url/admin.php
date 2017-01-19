<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   WindsForce 后台入口文件($$)*/

error_reporting(E_ERROR|E_PARSE|E_STRICT);
//error_reporting(E_ALL);
//define('QEEPHP_DEBUG',TRUE);

/** Defined the version of WindsForce */
define('WINDSFORCE_SERVER_VERSION','2.0');
define('WINDSFORCE_SERVER_RELEASE','20141007');
define('WINDSFORCE_SERVER_BUG','1.0');

/** 安装&升级 */
if(!is_file('../../user/lock/Install.lock.php')){
	header('location:../../~@~/install/index.php');
}

/** 系统应用路径定义 */
define('WINDSFORCE_PATH',dirname(dirname(getcwd())));

/** 定义Api环境 */
define('IN_APISELF',true);

/** 项目及项目路径定义 */
define('APP_NAME','admin');
define('APP_PATH',WINDSFORCE_PATH.'/System/'.APP_NAME);

/** 项目运行时路径及数据库表缓存路径 */
define('DIS_RUNTIME_PATH',WINDSFORCE_PATH.'/~@~/dis');
define('APP_RUNTIME_PATH',WINDSFORCE_PATH.'/~@~/app/'.APP_NAME);
define('DB_META_CACHED_PATH',WINDSFORCE_PATH.'/~@~/field');

/** 项目语言包路径定义 */
define('__COMMON_LANG__',WINDSFORCE_PATH.'/user/language');

/** 项目模板路径定义 */
define('__ROOTS__','../..');
define('__STATICS__','System/'.APP_NAME.'/Static');
define('__THEMES__','System/'.APP_NAME.'/Theme');

/** 项目编译锁定文件定义 */
define('APP_RUNTIME_LOCK',WINDSFORCE_PATH.'/user/lock/~Runtime.inc.lock');

/** 加载框架编译版本和设置父级目录为应用名 */
//define('STRIP_RUNTIME_SPACE',false);
define('QEEPHP_THIN',true);
define('APPNAME_IS_PARENTDIR',false);

/** 去掉模板空格 */
define('TMPL_STRIP_SPACE',true);

/** 载入框架 */
require(WINDSFORCE_PATH.'/System/include/QeePHP/~@.php');
App::RUN();
