<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   前台基本配置文件($$)*/

!defined('Q_PATH') && exit;

// 自定义应用配置
$arrMyappConfigs=array();

// 读取前台应用基本配置
$arrFrontappconfigs=(array)require(WINDSFORCE_PATH.'/System/common/Config.php');

return array_merge($arrMyappConfigs,$arrFrontappconfigs);
