<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   前台路由配置文件($$)*/

!defined('Q_PATH') && exit;

// 自定义路由配置
$arrMyappRouters=array();

// 读取前台应用基本路由配置
$arrFrontapprouters=(array)require(WINDSFORCE_PATH.'/System/common/Router.php');

return array_merge($arrMyappRouters,$arrFrontapprouters);
