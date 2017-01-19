<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   WindsForce 新用户数据($$)*/

!defined('IN_API') && exit;

/** API参数配置 */
define('USER_NEW_MAXNUM',20);// API最大最新用户数据
define('USER_NEW_DEFAULTRETURNTYPE','json');// API默认返回数据类型,支持xml和json,请使用小写

/** 设置API的访问参数 */
$_GET['app']='home';
$_GET['c']='api';
$_GET['a']='newuser';

/** 载入Api公用初始化文件 */
require(WINDSFORCE_PATH.'/System/common/Api.inc.php');

/** 载入框架 */
require(WINDSFORCE_PATH.'/System/include/QeePHP/~@.php');
App::RUN();
