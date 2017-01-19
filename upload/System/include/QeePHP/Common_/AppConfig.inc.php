<?php
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   初始化基本配置执行程序($$)*/

!defined('Q_PATH') && exit;

/** 读取系统默认配置，并写入默认配置项 */
$arrConfig=(array)(include Q_PATH.'/Common_/DefaultConfig.inc.php');

/** 合并数据，项目配置优先于系统惯性配置 **/
if(is_file(APP_PATH.'/App/Config/Config.php')){
	$arrConfig=array_merge($arrConfig,(array)(include APP_PATH.'/App/Config/Config.php'));
	unset($arrAppConfig);
}

/** 读取扩展配置文件，扩展配置优先于项目配置 */
if(is_file(APP_PATH.'/App/Config/ExtendConfig.php' )){
	foreach((array)(include APP_PATH.'/App/Config/ExtendConfig.php') as $sVal){
		if(is_file(APP_PATH.'/App/Config/ExtendConfig/'.ucfirst($sVal).'.php')){
			$arrConfig=array_merge($arrConfig,(array)(include APP_PATH.'/App/Config/ExtendConfig/'.ucfirst($sVal).'.php'));
			unset($arrExtendConfig);
		}else{
			E('Extend config file :'.APP_PATH.'/App/Config/ExtendConfig/'.ucwords($sVal).'.php'.' not exists');
		}
	}
}

/** 设置路由 */
if(is_file(APP_PATH.'/App/Config/Router.php')){// 从配置文件中载入路由
	$arrRouters=(array)(include(APP_PATH.'/App/Config/Router.php'));
	$arrConfig['_ROUTER_']=$arrRouters;
}

if(!is_dir(APP_RUNTIME_PATH)){
	C::makeDir(APP_RUNTIME_PATH);
}

if(!file_put_contents(APP_RUNTIME_PATH.'/Config.php',
	"<?php\n /* QeePHP Config File,Do not to modify this file! */ \n return ".
	var_export($arrConfig,true).
	"\n?>")
){
	E(sprintf('Dir %s Do not have permission.',APP_RUNTIME_PATH));
}

unset($arrConfig);
