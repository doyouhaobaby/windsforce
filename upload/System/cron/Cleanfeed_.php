<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   定时清理过期动态（默认每日0时0分）($$)*/

!defined('Q_PATH') && exit;

/** 清理网站过期动态数据 */
Q::instance('FeedModel')->deleteAllCreatedateline($GLOBALS['_option_']['feed_keep_time']);
