<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   今日公告清理（默认每日0时0分）($$)*/

!defined('Q_PATH') && exit;

/** 清理公告 */
Q::instance('AnnouncementModel')->deleteAllEndtime(CURRENT_TIMESTAMP);

/** 更新缓存 */
if(!Q::classExists('Cache_Extend')){
	require_once(Core_Extend::includeFile('function/Cache_Extend'));
}
Cache_Extend::updateCache('announcement');
