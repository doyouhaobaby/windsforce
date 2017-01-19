<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   群组基本配置文件($$)*/

!defined('Q_PATH') && exit;

// 自定义应用配置
$arrMyappConfigs=array();

// 读取前台应用基本配置
$arrFrontappconfigs=(array)require(WINDSFORCE_PATH.'/System/common/Config.php');

// 应用菜单
$arrFrontappconfigs['APP_MENU']=array(
	'group://ucenter/index'=>'帖子中心',
	'group://ucenter/lovetopic'=>'收藏帖子',
);

$arrFrontappconfigs['RUNTIME_CACHE_TIMES']=array_merge($arrFrontappconfigs['RUNTIME_CACHE_TIMES'],
	array(
		'group_hottag'=>3600,
		'group_site_*'=>3600,
		'group_topic_*'=>3600,
	)
);

return array_merge($arrMyappConfigs,$arrFrontappconfigs);
