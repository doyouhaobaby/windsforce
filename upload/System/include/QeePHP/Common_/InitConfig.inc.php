<?php
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   初始化基本配置($$)*/

!defined('Q_PATH') && exit;

if(!is_file(APP_RUNTIME_PATH.'/Config.php')){
	require(Q_PATH.'/Common_/AppConfig.inc.php');
}
$GLOBALS['_commonConfig_']=Q::C((array)(include APP_RUNTIME_PATH.'/Config.php'));
