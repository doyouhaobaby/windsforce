<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   WindsForce 安装程序入口文件($$)*/

error_reporting(E_ERROR|E_PARSE|E_STRICT);
//error_reporting(E_ALL);
//define('QEEPHP_DEBUG',TRUE);

/** Defined the version of WindsForce */
define('WINDSFORCE_SERVER_VERSION','2.0');
define('WINDSFORCE_SERVER_RELEASE','20141007');
define('WINDSFORCE_SERVER_BUG','1.0');

/** 已经安装过系统 */
if(is_file('../../user/lock/Install.lock.php')){
	header('Location:../../index.php');
}

/** 系统应用路径定义 */
define('WINDSFORCE_PATH',dirname(dirname(getcwd())));

/** 项目及项目路径定义 */
define('APP_NAME','install');
define('APP_PATH',getcwd());

/** 项目模板路径定义 */
define('__PUBLICS__','../Public');
define('__MESSAGE_IMG_PATH__','../../user/theme/Default/Public/Images');

/** 项目编译锁定文件定义 */
define('APP_RUNTIME_LOCK',WINDSFORCE_PATH.'/user/lock/~Runtime.inc.lock');

/** 加载框架编译版本 */
//define('STRIP_RUNTIME_SPACE',false);

/** 去掉模板空格 */
define('TMPL_STRIP_SPACE',true);

/** 载入框架 */
require(WINDSFORCE_PATH.'/System/include/QeePHP/~@.php');
App::RUN();
