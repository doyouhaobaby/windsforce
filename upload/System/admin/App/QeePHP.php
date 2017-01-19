<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   后台公用初始化文件($$)*/

!defined('Q_PATH') && exit;

/** 导入WindsForce核心函数 */
require(WINDSFORCE_PATH.'/System/function/Core_Extend.class.php');

/** 导入WindsForce后台函数 */
require(WINDSFORCE_PATH.'/System/function/Admin_Extend.class.php');

/** 导入公用模型 */
Q::import(WINDSFORCE_PATH.'/System/model');

/** 导入WindsForce后台模板函数 */
require(WINDSFORCE_PATH.'/System/function/Admintheme_Extend.class.php');
