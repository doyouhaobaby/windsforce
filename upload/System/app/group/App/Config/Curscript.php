<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   当前CSS资源配置文件($$)*/

!defined('Q_PATH') && exit;

/**
 * 新帖和首页可以互换，所以样式都满足
 */
return array(
	'public::newtopic'=>'userhome,groups,link',
	'public::index'=>'groups,userhome,link',
	'public::group'=>'groups',
	'group::show'=>'comment,media',
	'group::user',
	'groupadmin'=>'media',
	'space::index'=>'groups',
	'tag::show',
	'grouptopic::view'=>'media,comment',
	'grouptopic::reply'=>'media',
	'grouptopic::add'=>'media',
	'grouptopic::edit'=>'media',
	'search::result',
	'search::groupresult'=>'groups',
	'create::index'=>'media',
	'ucenter::index'=>'userhome,media',
	'ucenter::lovetopic'=>'userhome',
);
