<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   前台公用配置文件($$)*/

!defined('Q_PATH') && exit;

// 基础应用配置
$arrAppConfigs=array(
	'PHP_OFF'=>TRUE,
	'DEFAULT_CONTROL'=>'public',
	'TMPL_ACTION_ERROR'=>'message',
	'TMPL_ACTION_SUCCESS'=>'message',
	'START_ROUTER'=>true,
);

// 读取全局配置
$arrGlobalConfig=(array)require(WINDSFORCE_PATH.'/~@~/Config.inc.php');

// 模板设置
$arrAppConfigs['TPL_DIR']=$arrGlobalConfig['FRONT_TPL_DIR'];
unset($arrGlobalConfig['FRONT_TPL_DIR']);

// 语言包设置
$arrAppConfigs['LANG']=$arrGlobalConfig['FRONT_LANGUAGE_DIR'];
unset($arrGlobalConfig['FRONT_LANGUAGE_DIR']);

// CSS资源配置
if(is_file(APP_PATH.'/App/Config/Curscript.php')){
	$arrAppConfigs['_CURSCRIPT_']=(array)require(APP_PATH.'/App/Config/Curscript.php');
}

return array_merge($arrAppConfigs,$arrGlobalConfig);
