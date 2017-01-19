<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   Helloworld初始化安装程序($$)*/

!defined('Q_PATH') && exit;

/** 初始化应用表数据 */

/*
// 第一种方法
$sSql=<<<EOF

DROP TABLE IF EXISTS {WINDSFORCE}helloworldoption;
CREATE TABLE {WINDSFORCE}helloworldoption (
  `helloworldoption_name` varchar(32) NOT NULL DEFAULT '' COMMENT '名字',
  `helloworldoption_value` text NOT NULL COMMENT '值',
  PRIMARY KEY (`helloworldoption_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

EOF;

$this->runQuery($sSql);
*/

// 第二种方法（注意本方法需要和helloworld应用一样保证SQL文件位置）
Admin_Extend::installApp('helloworld');

$bFinish=TRUE;
