<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   System default config($$)*/

!defined('Q_PATH') && exit;

return array(
	// 数据库相关
	'DB_PASSWORD'=>'123456',
	'DB_PREFIX'=>'windsforce_',
	'DB_NAME'=>'windsforce',
	'DB_CACHE_FIELDS'=>TRUE,
	'DB_CACHE'=>TRUE,
	'DB_CACHE_TIME'=>86400000000,

	// 系统调试
	'APP_DEBUG'=>FALSE,
	'SHOW_RUN_TIME'=>TRUE,
	'SHOW_DB_TIMES'=>TRUE,
	'SHOW_GZIP_STATUS'=>FALSE,

	// 重要前缀
	'RBAC_DATA_PREFIX'=>'rbac_',
	'COOKIE_PREFIX'=>'wf_',
	'COOKIE_DOMAIN'=>'',

	// 加密安全Key
	'QEEPHP_AUTH_KEY'=>'authkey',

	// RBAC
	'RBAC_ROLE_TABLE'=>'role',
	'RBAC_USERROLE_TABLE'=>'userrole',
	'RBAC_ACCESS_TABLE'=>'access',
	'RBAC_NODE_TABLE'=>'node',
	'USER_AUTH_ON'=>TRUE,
	'USER_AUTH_KEY'=>'auth_id',
	'ADMIN_USERID'=>'1',
	'ADMIN_AUTH_KEY'=>'administrator',
	'USER_AUTH_MODEL'=>'user',
	'AUTH_PWD_ENCODER'=>'md5',
	'USER_AUTH_GATEWAY'=>'home://public/login',
	'NOT_AUTH_MODULE'=>'public,api,space,misc,wap,apps,search,index,homehelp,homesite',
	'REQUIRE_AUTH_MODULE'=>'',
	'NOT_AUTH_ACTION'=>'',
	'REQUIRE_AUTH_ACTION'=>'',
	'GUEST_AUTH_ON'=>true,
	'GUEST_AUTH_ID'=>'-1',
	'RBAC_ERROR_PAGE'=>'home://public/rbacerror',
	'RBAC_GUEST_ACCESS'=>array(
		/* home应用 */
		'home@stat@*'=>true,
		'home@online@*'=>true,
		'home@getpassword@*'=>true,
		'home@userappeal@*'=>true,
		'home@attachmentdownload@*'=>true,
		'home@attachmentread@*'=>true,
		'home@attachment@*'=>true,
		'home@attachment@show'=>false,
		'home@attachment@add'=>false,
		'home@attachment@normal_upload'=>false,
		'home@attachment@add_attachmentcomment'=>false,
		'home@homesite@*'=>true,
		'home@homehelp@*'=>true,
		'home@announcement@*'=>true,

		/* group应用 */
		'group@ucenter@*'=>true,
		'group@tag@*'=>true,
		'group@group@joingroup'=>true,
		'group@group@leavegroup'=>true,
		'group@group@getcategory'=>true,
		'group@grouptopic@set_grouptopicstyle'=>true,
		'group@grouptopic@set_grouptopicside'=>true,
		'group@create@*'=>true,

		/* 其它应用请到各自的配置文件设定 */
	),
	'RBAC_USER_ACCESS'=>array(
		/* home应用 */
		'home@spaceadmin@*'=>true,
		'home@spaceadmin@transfer'=>false,
		'home@spaceadmin@dotransfer'=>false,
		'home@pm@*'=>true,
		'home@notice@*'=>true,
		'home@friend@*'=>true,
		'home@ucenter@index'=>true,
		'home@ucenter@homefreshtopic'=>true,
		'home@ucenter@audit_homefreshcomment'=>true,
		'home@ucenter@feed'=>true,
		'home@ucenter@tag'=>true,
		'home@ucenter@tags'=>true,

		/* group应用 */
		'group@groupadmin@*'=>true,
		'group@grouptopic@printtable'=>true,
		'group@grouptopic@next'=>true,
		'group@grouptopic@prev'=>true,
		'group@grouptopic@readtopic'=>true,
		'group@grouptopic@love'=>true,
		'group@grouptopic@love_add'=>true,

		/* 其它应用请到各自的配置文件设定 */
	),

	// 异常错误模板
	'ERROR_PAGE'=>'home://public/page404',

	// 时区
	'TIME_ZONE'=>'Asia/Shanghai',
	
	// 开启注释版模板标签风格
	'TEMPLATE_TAG_NOTE'=>TRUE,
	'CACHE_REPLACE_CHILDREN'=>TRUE,

	// 开发者中心
	'APP_DEVELOP'=>0,// 是否开启后台应用设计，仅应用开发者设置为1

	// 模板设置
	'FRONT_TPL_DIR'=>'Default',
	'ADMIN_TPL_DIR'=>'Default',
	'CACHE_LIFE_TIME'=>8640000,

	// 语言包和模板COOKIE是否包含应用名字
	'COOKIE_LANG_TEMPLATE_INCLUDE_APPNAME'=>FALSE,
	
	// 是否开启FIREBUG检测
	'FIREBUGFORIE'=>FALSE,

	// 语言包设置
	'FRONT_LANGUAGE_DIR'=>'Zh-cn',
	'ADMIN_LANGUAGE_DIR'=>'Zh-cn',
	'LANG_SWITCH'=>TRUE,//前台专用，后台自动重写为TRUE
	'LANG_ON'=>false,//是否启用语言包

	// 默认应用
	'DEFAULT_APP'=>'group',

	// URL模式
	'URL_MODEL'=>0,
	'URL_HTML_SUFFIX'=>'.html',

	// 网址加上域名
	'URL_DOMAIN_ON'=>false,
	'URL_DOMAIN'=>'',

	// 多域名支持
	'DOMAIN_ON'=>false,
	'DOMAIN_TOP'=>'',
	'DOMAIN_SUFFIX'=>'',

	// 开启路由
	'START_ROUTER'=>true,
	'U_PRO_VAR'=>'',

	// 计划任务 && 计划任务跟系统功能紧密相关，如没有必要请不要关闭
	'CRON_ON'=>true,

	// 登陆才能够访问忽略页面,app@module@action
	'LOGINVIEW_IGNORE'=>array(
		'home@ucenter@view',
		'group@grouptopic@view',
	),

	// 应用个人中心菜单,'group://ucenter/index'=>'小组个人中心',
	'APP_MENU'=>array(),

	// 缓存设置
	'RUNTIME_CACHE_BACKEND'=>'FileCache',// 程序运行指定缓存
	'RUNTIME_CACHE_TIME'=>86400,// 程序缓存时间
	'RUNTIME_CACHE_TIMES'=>array(// 缓存时间预置,键值=缓存值，键值不带前缀 array('option'=>60)
		'newuser'=>3600,
		'activeuser'=>3600,
		'hottag'=>3600,
		'hottagtop'=>3600,
		'site'=>3600,
	),
	'RUNTIME_MEMCACHE_SERVERS'=>array(),// Memcache多台服务器
	'RUNTIME_MEMCACHE_HOST'=>'127.0.0.1',// Memcache默认缓存服务器
	'RUNTIME_MEMCACHE_PORT'=>11211,// Memcache默认缓存服务器端口
	'RUNTIME_MEMCACHE_COMPRESSED'=>false,// Memcache是否压缩缓存数据
	'RUNTIME_MEMCACHE_PERSISTENT'=>true,// Memcache是否使用持久连接

	// 其他设置
	'EDITOR_HEIGHT'=>1500,// 通用大内容编辑器高度（PX）
	'GLOBALS_TAGS'=>array(// 全局标签
	),
);
