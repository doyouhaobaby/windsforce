<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   WindsForce公用路由配置文件($$)*/

!defined('Q_PATH') && exit;

/** 公用路由是为了解决跨应用生成URL */
return array(
	'list'=>array('public/index','page'),// 全局顶级路由
	'space'=>array('space/index','id'),
	'fresh'=>array('ucenter/view','id'),
	'help'=>array('homehelp/show','id'),
	'file'=>array('attachment/show','id'),
	'site'=>array('homesite/site','id'),
	'msg'=>array('announcement/show','id'),
);
