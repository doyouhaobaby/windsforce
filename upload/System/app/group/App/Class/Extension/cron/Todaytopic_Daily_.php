<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   主题今日发帖清理操作，也包括小组日常清理（默认每日0时0分）($$)*/

!defined('Q_PATH') && exit;

/** 导入小组模型 */
if(APP_NAME!=='group'){
	Q::import(WINDSFORCE_PATH.'/System/app/group/App/Class/Model');
	define('__APPGROUP_COMMON_LANG__',WINDSFORCE_PATH.'/System/app/group/App/Lang/Admin');// 定义语言包
}

/** 清空小组 */
Q::instance('GroupModel')->clearToday();

/** 清理配置 */
Core_Extend::updateOption(
	array(
		'group_topictodaynum'=>0,
		'group_topiccommenttodaynum'=>0,
		'group_totaltodaynum'=>0
	),'group'
);

/** 清空帖子搜索记录 */
Q::instance('GroupsearchindexModel')->deleteAll();
