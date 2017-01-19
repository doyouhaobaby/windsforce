<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   前台公用初始化文件($$)*/

!defined('Q_PATH') && exit;

/** 导入WindsForce核心函数 */
require(WINDSFORCE_PATH.'/System/function/Core_Extend.class.php');

/** 导入公用模型 */
Q::import(WINDSFORCE_PATH.'/System/model');
Q::import(WINDSFORCE_PATH.'/System/controller');

/** 定义应用的语言包 */
if(APP_NAME!=='wap'){
	define('__APP'.strtoupper(APP_NAME).'_COMMON_LANG__',WINDSFORCE_PATH.'/System/app/'.APP_NAME.'/App/Lang/Admin');
}else{
	define('__APPHOME_COMMON_LANG__',WINDSFORCE_PATH.'/System/app/home/App/Lang/Admin');
}
define('__APP'.strtoupper(APP_NAME).'_APP_LANG__',WINDSFORCE_PATH.'/System/app/'.APP_NAME.'/App/Lang');

/** 定义应用的公用主题目录 */
define('__UTHEME__',__ROOT__.'/user/theme/'.TEMPLATE_NAME);
define('__UTHEMEPUB__',__ROOT__.'/user/theme/'.TEMPLATE_NAME.'/Public');

/** 定义应用的公用消息图片目录 */
if(TEMPLATE_NAME==='default' || !is_file(WINDSFORCE_PATH.'/user/theme/'.TEMPLATE_NAME.'/Public/Images/loader.gif')){
	define('__MESSAGE_IMG_PATH__',__ROOT__.'/user/theme/Default/Public/Images');
}else{
	define('__MESSAGE_IMG_PATH__',__ROOT__.'/user/theme/'.TEMPLATE_NAME.'/Public/Images');
}
