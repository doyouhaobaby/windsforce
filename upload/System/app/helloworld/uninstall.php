<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   Helloworld卸载清理程序($$)*/

!defined('Q_PATH') && exit;

/** 卸载应用表数据 && 如果应用不需要清理数据，你可以删除本文件 */

/*
// 第一种方法
$sSql=<<<EOF

DROP TABLE IF EXISTS {WINDSFORCE}helloworldoption;

EOF;

$this->runQuery($sSql);
*/

// 第二种方法（注意本方法需要和helloworld应用一样保证SQL文件位置）
Admin_Extend::uninstallApp('helloworld');

$bFinish=TRUE;
