<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   今日数据清理（默认每日0时0分）($$)*/

!defined('Q_PATH') && exit;

/** 导入缓存组件 */
if(!Q::classExists('Cache_Extend')){
	require_once(Core_Extend::includeFile('function/Cache_Extend'));
}

/** 每日更新用户排行数据 */
Cache_Extend::updateCache('usertop');

/** 清理网站今日发布数量 */
Core_Extend::updateOption(
	array(
		'todayusernum'=>0,
		'todayhomefreshnum'=>0,
		'todaytotalnum'=>0,
	)
);

/** 清空网站登陆记录数据 */
Q::instance('LoginlogModel')->deleteAll(86400*365);

/** 清理网站管理记录数据 */
Q::instance('AdminlogModel')->deleteAll(86400*365);

/** 清理网站过期提醒数据 */
Q::instance('NoticeModel')->deleteAllCreatedateline($GLOBALS['_option_']['notice_keep_time']);
