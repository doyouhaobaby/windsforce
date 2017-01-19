<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   Helloworld基本配置文件($$)*/

!defined('Q_PATH') && exit;

// 自定义应用配置
$arrMyappConfigs=array();

// 读取前台应用基本配置
$arrFrontappconfigs=(array)require(WINDSFORCE_PATH.'/System/common/Config.php');

// 应用菜单
$arrFrontappconfigs['APP_MENU']=array(
	'event://ucenter/index'=>'我发起的',
	'event://ucenter/join'=>'我参加的',
	'event://ucenter/attention'=>'我感兴趣',
);

// 访问权限设置
$arrFrontappconfigs['RBAC_GUEST_ACCESS']=array(
	'event@event@show'=>true,
	'event@ucenter@*'=>true,
);

$arrFrontappconfigs['RBAC_USER_ACCESS']=array(
	'event@event@*'=>true,
);

return array_merge($arrMyappConfigs,$arrFrontappconfigs);
