-- WINDSFORCE 数据库表
-- version 2.0
-- http://windsforce.114.ms
--
-- 开发: Windsforce TEAM
-- 网站: http://windsforce.114.ms

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- 数据库: `windsforce`
--

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_access`
--

DROP TABLE IF EXISTS `#@__access`;
CREATE TABLE `#@__access` (
  `role_id` smallint(6) unsigned NOT NULL COMMENT '角色ID',
  `node_id` smallint(6) unsigned NOT NULL COMMENT '节点ID',
  `access_level` tinyint(1) NOT NULL COMMENT '级别，1（应用），2（模块），3（方法）',
  `access_parentid` smallint(6) NOT NULL COMMENT '父级ID',
  `access_status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态',
  KEY `group_id` (`role_id`),
  KEY `node_id` (`node_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_adminctrlmenu`
--

DROP TABLE IF EXISTS `#@__adminctrlmenu`;
CREATE TABLE `#@__adminctrlmenu` (
  `adminctrlmenu_id` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '后台菜单ID',
  `adminctrlmenu_internal` tinyint(1) NOT NULL DEFAULT '0' COMMENT '快捷菜单类型，0自定义，1内置',
  `adminctrlmenu_title` varchar(50) NOT NULL COMMENT '后台菜单标题',
  `adminctrlmenu_url` varchar(255) NOT NULL COMMENT '后台菜单网址',
  `adminctrlmenu_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '后台菜单状态',
  `adminctrlmenu_sort` tinyint(3) NOT NULL COMMENT '后台菜单排序',
  `adminctrlmenu_clicknum` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '后台菜单点击量',
  `user_id` int(10) unsigned NOT NULL COMMENT '后台菜单操作人',
  `adminctrlmenu_admin` varchar(50) NOT NULL COMMENT '操作人用户名',
  `create_dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '后台菜单创建时间',
  PRIMARY KEY (`adminctrlmenu_id`),
  KEY `adminctrlmenu_status` (`adminctrlmenu_status`),
  KEY `user_id` (`user_id`),
  KEY `create_dateline` (`create_dateline`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_adminlog`
--

DROP TABLE IF EXISTS `#@__adminlog`;
CREATE TABLE `#@__adminlog` (
  `adminlog_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '后台管理ID',
  `create_dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '日志所记录的操作者ID',
  `adminlog_username` varchar(50) NOT NULL COMMENT '后台管理记录用户名',
  `adminlog_info` varchar(255) NOT NULL DEFAULT '' COMMENT '管理操作内容',
  `adminlog_ip` varchar(16) NOT NULL DEFAULT '' COMMENT '登录者登录IP',
  PRIMARY KEY (`adminlog_id`),
  KEY `user_id` (`user_id`),
  KEY `create_dateline` (`create_dateline`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_announcement`
--

DROP TABLE IF EXISTS `#@__announcement`;
CREATE TABLE `#@__announcement` (
  `announcement_id` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '公告ID',
  `announcement_username` varchar(50) NOT NULL DEFAULT '' COMMENT '公告发布用户',
  `announcement_title` varchar(255) NOT NULL DEFAULT '' COMMENT '公告标题',
  `announcement_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '公告类型，0文字，1网址',
  `announcement_sort` tinyint(3) NOT NULL DEFAULT '0' COMMENT '公告排序',
  `create_dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间',
  `announcement_endtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '公告结束时间',
  `announcement_message` text NOT NULL COMMENT '公告内容',
  `announcement_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '公告状态',
  PRIMARY KEY (`announcement_id`),
  KEY `timespan` (`create_dateline`,`announcement_endtime`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_app`
--

DROP TABLE IF EXISTS `#@__app`;
CREATE TABLE `#@__app` (
  `app_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '应用ID',
  `app_identifier` varchar(32) NOT NULL COMMENT '应用唯一识别符',
  `app_name` varchar(32) NOT NULL COMMENT '应用名字',
  `app_version` varchar(20) NOT NULL COMMENT '应用版本',
  `app_description` varchar(255) NOT NULL COMMENT '应用描述',
  `app_url` varchar(255) NOT NULL COMMENT '应用官方网站',
  `app_email` varchar(255) NOT NULL COMMENT '应用邮件',
  `app_author` varchar(32) NOT NULL COMMENT '应用作者',
  `app_authorurl` varchar(255) NOT NULL COMMENT '应用作者主页',
  `app_isadmin` tinyint(1) NOT NULL DEFAULT '0' COMMENT '应用是否需要管理项',
  `app_isinstall` tinyint(1) NOT NULL DEFAULT '0' COMMENT '应用是否需要安装',
  `app_isuninstall` tinyint(1) NOT NULL DEFAULT '0' COMMENT '应用是否需要卸载',
  `app_issystem` tinyint(1) NOT NULL DEFAULT '0' COMMENT '应用是否为系统核心',
  `app_isappnav` tinyint(1) NOT NULL DEFAULT '0' COMMENT '应用是否需要写入前台菜单',
  `app_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否启用应用',
  PRIMARY KEY (`app_id`),
  UNIQUE KEY `app_identifier` (`app_identifier`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_appeal`
--

DROP TABLE IF EXISTS `#@__appeal`;
CREATE TABLE `#@__appeal` (
  `appeal_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '申诉ID',
  `user_id` int(10) NOT NULL COMMENT '申诉用户ID',
  `appeal_realname` varchar(50) NOT NULL COMMENT '申诉真实姓名',
  `appeal_address` varchar(300) NOT NULL COMMENT '申诉详细地址',
  `appeal_idnumber` varchar(32) NOT NULL COMMENT '申诉身份证号码',
  `appeal_email` varchar(150) NOT NULL COMMENT '申诉邮件地址',
  `appeal_receiptnumber` varchar(50) NOT NULL COMMENT '申诉回执号码',
  `create_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `appeal_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '申诉状态',
  `appeal_progress` tinyint(1) NOT NULL DEFAULT '0' COMMENT '申诉进度',
  `appeal_reason` varchar(325) NOT NULL COMMENT '驳回理由',
  PRIMARY KEY (`appeal_id`),
  KEY `user_id` (`user_id`),
  KEY `create_dateline` (`create_dateline`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_attachment`
--

DROP TABLE IF EXISTS `#@__attachment`;
CREATE TABLE `#@__attachment` (
  `attachment_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '附件ID',
  `attachment_name` varchar(100) NOT NULL COMMENT '名字',
  `attachment_type` varchar(40) NOT NULL COMMENT '类型',
  `attachment_size` int(10) NOT NULL COMMENT '大小，单位KB',
  `attachment_extension` varchar(20) NOT NULL COMMENT '后缀',
  `attachment_savepath` varchar(50) NOT NULL COMMENT '保存路径',
  `attachment_savename` char(50) NOT NULL COMMENT '保存名字',
  `attachment_isthumb` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否存在缩略图',
  `attachment_thumbprefix` varchar(25) NOT NULL COMMENT '缩略图前缀',
  `attachment_thumbpath` varchar(32) NOT NULL COMMENT '缩略图路径',
  `create_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `attachmentcategory_id` int(10) NOT NULL COMMENT '分类ID',
  `attachment_download` int(10) NOT NULL COMMENT '下载次数',
  `attachment_commentnum` int(10) NOT NULL DEFAULT '0' COMMENT '评论数量',
  `attachment_islock` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否锁定',
  `user_id` int(10) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `attachment_username` varchar(50) NOT NULL COMMENT '用户名',
  `attachment_isimg` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否为图片附件',
  PRIMARY KEY (`attachment_id`),
  KEY `user_id` (`user_id`),
  KEY `create_dateline` (`create_dateline`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_attachmentcategory`
--

DROP TABLE IF EXISTS `#@__attachmentcategory`;
CREATE TABLE `#@__attachmentcategory` (
  `attachmentcategory_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '附件分类ID',
  `attachmentcategory_name` varchar(50) NOT NULL COMMENT '分类名字',
  `attachmentcategory_cover` varchar(100) NOT NULL DEFAULT '' COMMENT '分类封面',
  `attachmentcategory_sort` smallint(6) NOT NULL DEFAULT '0' COMMENT '排序',
  `attachmentcategory_description` varchar(500) NOT NULL COMMENT '专辑描述',
  `attachmentcategory_attachmentnum` int(10) NOT NULL DEFAULT '0' COMMENT '专辑中附件数量',
  `create_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `user_id` int(10) NOT NULL DEFAULT '0' COMMENT '用户',
  `attachmentcategory_username` varchar(50) NOT NULL COMMENT '用户名',
  PRIMARY KEY (`attachmentcategory_id`),
  KEY `create_dateline` (`create_dateline`),
  KEY `user_id` (`user_id`),
  KEY `attachmentcategory_sort` (`attachmentcategory_sort`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_attachmentcomment`
--

DROP TABLE IF EXISTS `#@__attachmentcomment`;
CREATE TABLE `#@__attachmentcomment` (
  `attachmentcomment_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '评论ID',
  `create_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `user_id` int(10) NOT NULL DEFAULT '0' COMMENT '用户ID，在线用户评论',
  `attachmentcomment_name` varchar(50) NOT NULL COMMENT '名字',
  `attachmentcomment_content` varchar(250) NOT NULL COMMENT '内容',
  `attachmentcomment_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0=待审 1=通过 2=拒绝 3=管理员关闭 11=修改后提交 12=结束 13=用户关闭',
  `attachmentcomment_ip` varchar(16) NOT NULL COMMENT 'IP',
  `attachmentcomment_ismobile` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否为手机评论',
  `attachment_id` int(10) NOT NULL DEFAULT '0' COMMENT '附件ID',
  `attachmentcomment_qq` varchar(20) NOT NULL COMMENT 'QQ号码',
  `attachmentcomment_mobile` varchar(20) NOT NULL COMMENT '手机号',
  PRIMARY KEY (`attachmentcomment_id`),
  KEY `user_id` (`user_id`),
  KEY `attachment_id` (`attachment_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_badword`
--

DROP TABLE IF EXISTS `#@__badword`;
CREATE TABLE `#@__badword` (
  `badword_id` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '词语替换ID',
  `badword_admin` varchar(50) NOT NULL DEFAULT '' COMMENT '添加词语过滤用户',
  `badword_find` varchar(300) NOT NULL DEFAULT '' COMMENT '待查找的过滤词语',
  `badword_replacement` varchar(300) NOT NULL DEFAULT '' COMMENT '待替换的过滤词语',
  `badword_findpattern` varchar(300) NOT NULL DEFAULT '' COMMENT '查找的正则表达式',
  `create_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`badword_id`),
  UNIQUE KEY `find` (`badword_find`),
  KEY `create_dateline` (`create_dateline`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_cache`
--

DROP TABLE IF EXISTS `#@__cache`;
CREATE TABLE `#@__cache` (
  `cache_key` varchar(255) NOT NULL DEFAULT '' COMMENT '缓存key',
  `cache_value` mediumblob NOT NULL COMMENT '缓存值',
  `create_dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  PRIMARY KEY (`cache_key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_creditlog`
--

DROP TABLE IF EXISTS `#@__creditlog`;
CREATE TABLE `#@__creditlog` (
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `creditlog_operation` varchar(25) NOT NULL DEFAULT '' COMMENT '操作类型',
  `creditlog_relatedid` int(10) unsigned NOT NULL COMMENT '关联ID',
  `create_dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `creditlog_extcredits1` int(10) NOT NULL COMMENT '第一种扩展积分',
  `creditlog_extcredits2` int(10) NOT NULL COMMENT '第二种扩展积分',
  `creditlog_extcredits3` int(10) NOT NULL COMMENT '第三种扩展积分',
  `creditlog_extcredits4` int(10) NOT NULL COMMENT '第四种扩展积分',
  `creditlog_extcredits5` int(10) NOT NULL COMMENT '第五种扩展积分',
  `creditlog_extcredits6` int(10) NOT NULL COMMENT '第六种扩展积分',
  `creditlog_extcredits7` int(10) NOT NULL COMMENT '第七种扩展积分',
  `creditlog_extcredits8` int(10) NOT NULL COMMENT '第八种扩展积分',
  KEY `create_dateline` (`create_dateline`),
  KEY `creditlog_relatedid` (`creditlog_relatedid`),
  KEY `creditlog_operation` (`creditlog_operation`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_creditoperation`
--

DROP TABLE IF EXISTS `#@__creditoperation`;
CREATE TABLE `#@__creditoperation` (
  `creditoperation_name` varchar(25) NOT NULL COMMENT '积分操作名字',
  `creditoperation_title` varchar(25) NOT NULL COMMENT '积分操作标题',
  PRIMARY KEY (`creditoperation_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_creditrule`
--

DROP TABLE IF EXISTS `#@__creditrule`;
CREATE TABLE `#@__creditrule` (
  `creditrule_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '积分规则ID',
  `creditrule_name` varchar(20) NOT NULL DEFAULT '' COMMENT '积分规则名字',
  `creditrule_action` varchar(20) NOT NULL DEFAULT '' COMMENT '规则action唯一KEY',
  `creditrule_cycletype` tinyint(1) NOT NULL DEFAULT '0' COMMENT '奖励周期0:一次;1:每天;2:整点;3:间隔分钟;4:不限',
  `creditrule_cycletime` int(10) NOT NULL DEFAULT '0' COMMENT '间隔时间',
  `creditrule_rewardnum` tinyint(2) NOT NULL DEFAULT '1' COMMENT '周期内最多奖励次数',
  `creditrule_extendcredit1` int(10) NOT NULL DEFAULT '0' COMMENT '第一种积分类型',
  `creditrule_extendcredit2` int(10) NOT NULL DEFAULT '0' COMMENT '第二种积分类型',
  `creditrule_extendcredit3` int(10) NOT NULL DEFAULT '0' COMMENT '第三种积分类型',
  `creditrule_extendcredit4` int(10) NOT NULL DEFAULT '0' COMMENT '第四种积分类型',
  `creditrule_extendcredit5` int(10) NOT NULL DEFAULT '0' COMMENT '第五种积分类型',
  `creditrule_extendcredit6` int(10) NOT NULL DEFAULT '0' COMMENT '第六种积分类型',
  `creditrule_extendcredit7` int(10) NOT NULL DEFAULT '0' COMMENT '第七种积分类型',
  `creditrule_extendcredit8` int(10) NOT NULL DEFAULT '0' COMMENT '第八种积分类型',
  PRIMARY KEY (`creditrule_id`),
  KEY `creditrule_action` (`creditrule_action`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_creditrulelog`
--

DROP TABLE IF EXISTS `#@__creditrulelog`;
CREATE TABLE `#@__creditrulelog` (
  `creditrulelog_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '策略日志ID',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '策略日志所有者uid',
  `creditrule_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '策略ID',
  `creditrulelog_total` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '策略被执行总次数',
  `creditrulelog_cyclenum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '周期被执行次数',
  `creditrulelog_extendcredit1` int(10) NOT NULL DEFAULT '0' COMMENT '第一种积分类型',
  `creditrulelog_extendcredit2` int(10) NOT NULL DEFAULT '0' COMMENT '第二种积分类型',
  `creditrulelog_extendcredit3` int(10) NOT NULL DEFAULT '0' COMMENT '第三种积分类型',
  `creditrulelog_extendcredit4` int(10) NOT NULL DEFAULT '0' COMMENT '第四种积分类型',
  `creditrulelog_extendcredit5` int(10) NOT NULL DEFAULT '0' COMMENT '第五种积分类型',
  `creditrulelog_extendcredit6` int(10) NOT NULL DEFAULT '0' COMMENT '第六种积分类型',
  `creditrulelog_extendcredit7` int(10) NOT NULL DEFAULT '0' COMMENT '第七种积分类型',
  `creditrulelog_extendcredit8` int(10) NOT NULL DEFAULT '0' COMMENT '第八种积分类型',
  `creditrulelog_starttime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '周期开始时间',
  `update_dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '策略最后执行时间',
  PRIMARY KEY (`creditrulelog_id`),
  KEY `user_id` (`user_id`),
  KEY `creditrule_id` (`creditrule_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_cron`
--

DROP TABLE IF EXISTS `#@__cron`;
CREATE TABLE `#@__cron` (
  `cron_id` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '任务计划ID',
  `cron_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否启用任务计划',
  `cron_type` enum('user','system','plugin','app') NOT NULL DEFAULT 'user' COMMENT '任务计划类型',
  `cron_name` char(50) NOT NULL DEFAULT '' COMMENT '计划任务名字',
  `cron_filename` char(50) NOT NULL DEFAULT '' COMMENT '计划任务脚本',
  `cron_lastrun` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后一次执行时间',
  `cron_nextrun` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '下次执行时间',
  `cron_weekday` tinyint(1) NOT NULL DEFAULT '0' COMMENT '每周',
  `cron_day` tinyint(2) NOT NULL DEFAULT '0' COMMENT '每月',
  `cron_hour` tinyint(2) NOT NULL DEFAULT '0' COMMENT '小时',
  `cron_minute` char(36) NOT NULL DEFAULT '' COMMENT '分钟',
  PRIMARY KEY (`cron_id`),
  KEY `cron_nextrun` (`cron_status`,`cron_nextrun`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_feed`
--

DROP TABLE IF EXISTS `#@__feed`;
CREATE TABLE `#@__feed` (
  `feed_id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `feed_username` varchar(50) NOT NULL COMMENT '用户名',
  `feed_template` text NOT NULL COMMENT '动态模板',
  `feed_data` text NOT NULL COMMENT '动态数据',
  `create_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `feed_application` varchar(32) NOT NULL DEFAULT 'home' COMMENT '动态来源应用',
  `site_id` int(6) NOT NULL DEFAULT '0' COMMENT '站点ID',
  PRIMARY KEY (`feed_id`),
  KEY `user_id` (`user_id`),
  KEY `create_dateline` (`create_dateline`),
  KEY `site_id` (`site_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_friend`
--

DROP TABLE IF EXISTS `#@__friend`;
CREATE TABLE `#@__friend` (
  `user_id` int(10) NOT NULL COMMENT '用户ID',
  `friend_friendid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '好友ID',
  `friend_direction` tinyint(1) NOT NULL DEFAULT '1' COMMENT '关系，1（A加B）,3（A与B彼此相加）',
  `friend_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态',
  `friend_comment` char(255) NOT NULL DEFAULT '' COMMENT '备注',
  `friend_fancomment` varchar(255) NOT NULL COMMENT '粉丝备注',
  `create_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `friend_username` varchar(50) NOT NULL COMMENT '用户名',
  `friend_friendusername` varchar(50) NOT NULL COMMENT '好友用户名',
  KEY `user_id` (`user_id`),
  KEY `friend_friendid` (`friend_friendid`),
  KEY `create_dateline` (`create_dateline`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_homefresh`
--

DROP TABLE IF EXISTS `#@__homefresh`;
CREATE TABLE `#@__homefresh` (
  `homefresh_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '新鲜事ID',
  `homefresh_title` varchar(300) NOT NULL COMMENT '新鲜事标题',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户',
  `homefresh_username` varchar(50) NOT NULL DEFAULT '' COMMENT '用户名',
  `homefresh_from` varchar(20) NOT NULL DEFAULT '' COMMENT '来源',
  `create_dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `homefresh_message` text NOT NULL COMMENT '新鲜事内容',
  `homefresh_ip` varchar(20) NOT NULL DEFAULT '' COMMENT 'IP',
  `homefresh_commentnum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评论数量',
  `homefresh_goodnum` int(10) NOT NULL DEFAULT '0' COMMENT '赞数量',
  `homefresh_viewnum` int(10) NOT NULL DEFAULT '0' COMMENT '评论数量',
  `homefresh_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '新鲜事状态',
  `homefresh_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '新鲜事类型：1文字,2音乐,3图片,4视频,5电影,6购物',
  `homefresh_attribute` text NOT NULL COMMENT '新鲜事扩展属性值',
  `homefresh_thumb` varchar(100) NOT NULL DEFAULT '' COMMENT '新鲜事缩略图',
  `homefreshcategory_id` int(6) NOT NULL DEFAULT '0' COMMENT '新鲜事类型',
  PRIMARY KEY (`homefresh_id`),
  KEY `create_dateline` (`create_dateline`),
  KEY `user_id` (`user_id`),
  KEY `homefreshcategory_id` (`homefreshcategory_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_homefreshcategory`
--

DROP TABLE IF EXISTS `#@__homefreshcategory`;
CREATE TABLE `#@__homefreshcategory` (
  `homefreshcategory_id` int(6) NOT NULL AUTO_INCREMENT COMMENT '帮助分类ID',
  `homefreshcategory_name` char(32) NOT NULL DEFAULT '' COMMENT '帮助分类名字',
  `homefreshcategory_count` int(10) NOT NULL DEFAULT '0' COMMENT '帮助个数',
  `homefreshcategory_sort` smallint(6) NOT NULL DEFAULT '0' COMMENT '帮助分类排序名字',
  `update_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  `create_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '群组创建时间',
  PRIMARY KEY (`homefreshcategory_id`),
  KEY `create_dateline` (`create_dateline`),
  KEY `homefreshcategory_sort` (`homefreshcategory_sort`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_homefreshcomment`
--

DROP TABLE IF EXISTS `#@__homefreshcomment`;
CREATE TABLE `#@__homefreshcomment` (
  `homefreshcomment_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '评论ID',
  `create_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `user_id` int(10) NOT NULL DEFAULT '0' COMMENT '用户ID，在线用户评论',
  `homefreshcomment_name` varchar(50) NOT NULL COMMENT '名字',
  `homefreshcomment_content` varchar(250) NOT NULL COMMENT '内容',
  `homefreshcomment_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0=待审 1=通过 2=拒绝 3=管理员关闭 11=修改后提交 12=结束 13=用户关闭',
  `homefreshcomment_ip` varchar(16) NOT NULL COMMENT 'IP',
  `homefreshcomment_parentid` int(10) NOT NULL DEFAULT '0' COMMENT '父级ID',
  `homefreshcomment_ismobile` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否为手机评论',
  `homefresh_id` int(10) NOT NULL DEFAULT '0' COMMENT '新鲜事ID',
  `homefreshcomment_qq` varchar(20) NOT NULL COMMENT 'QQ号码',
  `homefreshcomment_mobile` varchar(20) NOT NULL COMMENT '手机号',
  PRIMARY KEY (`homefreshcomment_id`),
  KEY `user_id` (`user_id`),
  KEY `homefresh_id` (`homefresh_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_homefreshtag`
--

DROP TABLE IF EXISTS `#@__homefreshtag`;
CREATE TABLE `#@__homefreshtag` (
  `homefreshtag_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '新鲜事话题ID',
  `homefreshtag_name` varchar(50) NOT NULL DEFAULT '' COMMENT '新鲜事话题名字',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '话题创建者',
  `homefreshtag_username` varchar(50) NOT NULL DEFAULT '' COMMENT '话题创建者用户名',
  `create_dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发布时间',
  `update_dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  `homefreshtag_totalcount` int(10) NOT NULL DEFAULT '0' COMMENT '总计',
  `homefreshtag_usercount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户数量',
  `homefreshtag_homefreshcount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '新鲜事数量',
  `homefreshtag_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '话题状态',
  PRIMARY KEY (`homefreshtag_id`),
  KEY `user_id` (`user_id`),
  KEY `create_dateline` (`create_dateline`),
  KEY `homefreshtag_name` (`homefreshtag_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_homehelp`
--

DROP TABLE IF EXISTS `#@__homehelp`;
CREATE TABLE `#@__homehelp` (
  `homehelp_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '帮组ID',
  `homehelp_title` varchar(250) NOT NULL COMMENT '帮组标题',
  `homehelp_content` text NOT NULL COMMENT '帮助正文',
  `homehelpcategory_id` int(10) NOT NULL DEFAULT '0' COMMENT '帮组信息分类',
  `homehelp_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '帮助状态',
  `create_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_dateline` int(10) NOT NULL  DEFAULT '0' COMMENT '更新时间',
  `user_id` int(10) NOT NULL DEFAULT '0' COMMENT '发布用户',
  `homehelp_username` varchar(50) NOT NULL COMMENT '文章发布用户',
  `homehelp_updateuserid` int(10) NOT NULL DEFAULT '0' COMMENT '最新更新帮助的用户',
  `homehelp_updateusername` varchar(50) NOT NULL COMMENT '文章最后更新用户',
  `homehelp_viewnum` int(10) NOT NULL DEFAULT '0' COMMENT '帮助浏览次数',
  `homehelp_issystem` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否为系统帮助',
  PRIMARY KEY (`homehelp_id`),
  KEY `create_dateline` (`create_dateline`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_homehelpcategory`
--

DROP TABLE IF EXISTS `#@__homehelpcategory`;
CREATE TABLE `#@__homehelpcategory` (
  `homehelpcategory_id` int(6) NOT NULL AUTO_INCREMENT COMMENT '帮助分类ID',
  `homehelpcategory_name` char(32) NOT NULL DEFAULT '' COMMENT '帮助分类名字',
  `homehelpcategory_count` int(10) NOT NULL DEFAULT '0' COMMENT '帮助个数',
  `homehelpcategory_sort` smallint(6) NOT NULL DEFAULT '0' COMMENT '帮助分类排序名字',
  `update_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  `create_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '群组创建时间',
  `homehelpcategory_issystem` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否为系统帮助分类',
  PRIMARY KEY (`homehelpcategory_id`),
  KEY `create_dateline` (`create_dateline`),
  KEY `homehelpcategory_sort` (`homehelpcategory_sort`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_homeoption`
--

DROP TABLE IF EXISTS `#@__homeoption`;
CREATE TABLE `#@__homeoption` (
  `homeoption_name` varchar(32) NOT NULL DEFAULT '' COMMENT '名字',
  `homeoption_value` text NOT NULL COMMENT '值',
  PRIMARY KEY (`homeoption_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_homesite`
--

DROP TABLE IF EXISTS `#@__homesite`;
CREATE TABLE `#@__homesite` (
  `homesite_id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `homesite_name` char(32) NOT NULL DEFAULT '' COMMENT '键值',
  `homesite_nikename` char(32) NOT NULL COMMENT '站点信息别名',
  `homesite_content` text NOT NULL COMMENT '内容',
  `homesite_issystem` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否为系统站点信息',
  PRIMARY KEY (`homesite_id`),
  UNIQUE KEY `homesite_name` (`homesite_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_hometag`
--

DROP TABLE IF EXISTS `#@__hometag`;
CREATE TABLE `#@__hometag` (
  `hometag_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '用户标签',
  `hometag_name` char(32) NOT NULL DEFAULT '' COMMENT '标签名字',
  `hometag_count` int(10) NOT NULL DEFAULT '0' COMMENT '标签用户数量',
  `create_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`hometag_id`),
  KEY `hometag_name` (`hometag_name`),
  KEY `create_dateline` (`create_dateline`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_hometagindex`
--

DROP TABLE IF EXISTS `#@__hometagindex`;
CREATE TABLE `#@__hometagindex` (
  `user_id` int(10) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `hometag_id` int(10) NOT NULL DEFAULT '0' COMMENT '标签ID',
  PRIMARY KEY (`user_id`,`hometag_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_link`
--

DROP TABLE IF EXISTS `#@__link`;
CREATE TABLE `#@__link` (
  `link_id` mediumint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '链接ID',
  `create_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `link_name` varchar(32) NOT NULL COMMENT '名字',
  `link_url` varchar(250) NOT NULL COMMENT 'URL',
  `link_description` varchar(300) NOT NULL COMMENT '描述',
  `link_logo` varchar(360) NOT NULL DEFAULT '0' COMMENT 'LOGO',
  `link_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否显示',
  `link_sort` smallint(6) NOT NULL COMMENT '排序',
  `link_issystem` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否为系统链接',
  PRIMARY KEY (`link_id`),
  KEY `link_sort` (`link_sort`),
  KEY `create_dateline` (`create_dateline`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_loginlog`
--

DROP TABLE IF EXISTS `#@__loginlog`;
CREATE TABLE `#@__loginlog` (
  `loginlog_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '登录ID',
  `user_id` int(10) NOT NULL COMMENT '用户ID',
  `create_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `loginlog_username` varchar(50) NOT NULL COMMENT '登录用户',
  `loginlog_ip` varchar(16) NOT NULL COMMENT '登录IP',
  `loginlog_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '登录状态',
  `login_application` varchar(32) NOT NULL COMMENT '登录应用',
  PRIMARY KEY (`loginlog_id`),
  KEY `user_id` (`user_id`),
  KEY `create_dateline` (`create_dateline`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_mail`
--

DROP TABLE IF EXISTS `#@__mail`;
CREATE TABLE `#@__mail` (
  `mail_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '邮件ID',
  `mail_touserid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '接受用户ID',
  `mail_fromuserid` int(10) NOT NULL DEFAULT '0' COMMENT '发送用户ID',
  `mail_tomail` varchar(100) NOT NULL COMMENT '接收者邮件地址',
  `mail_frommail` varchar(100) NOT NULL COMMENT '发送者邮件地址',
  `mail_subject` varchar(300) NOT NULL COMMENT '主题',
  `mail_message` text NOT NULL COMMENT '内容',
  `mail_charset` varchar(15) NOT NULL COMMENT '编码',
  `mail_htmlon` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启html',
  `mail_level` tinyint(1) NOT NULL DEFAULT '1' COMMENT '紧急级别',
  `create_dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `mail_status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态，是否成功',
  `mail_application` varchar(32) NOT NULL COMMENT '来源应用',
  PRIMARY KEY (`mail_id`),
  KEY `create_dateline` (`create_dateline`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_nav`
--

DROP TABLE IF EXISTS `#@__nav`;
CREATE TABLE `#@__nav` (
  `nav_id` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '导航ID',
  `nav_parentid` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '父级ID',
  `nav_name` varchar(32) NOT NULL COMMENT '菜单名字，如群组',
  `nav_identifier` varchar(255) NOT NULL COMMENT 'URL唯一标识符',
  `nav_title` varchar(255) NOT NULL COMMENT '菜单标题，如Group',
  `nav_url` varchar(255) NOT NULL COMMENT '菜单URL地址',
  `nav_target` tinyint(1) NOT NULL DEFAULT '0' COMMENT '菜单是否新窗口打开',
  `nav_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '菜单类型，0内置，1自定义',
  `nav_style` varchar(55) NOT NULL COMMENT '菜单的下划线，斜体，粗体修饰',
  `nav_location` tinyint(1) NOT NULL DEFAULT '0' COMMENT '导航位置，0主导航，1头部，2底部',
  `nav_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否显示',
  `nav_sort` tinyint(3) NOT NULL COMMENT '菜单排序',
  `nav_color` tinyint(1) NOT NULL DEFAULT '0' COMMENT '菜单高亮，对应一些颜色值',
  `nav_icon` varchar(255) NOT NULL COMMENT '菜单图标',
  PRIMARY KEY (`nav_id`),
  KEY `nav_sort` (`nav_sort`),
  KEY `nav_identifier` (`nav_identifier`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_node`
--

DROP TABLE IF EXISTS `#@__node`;
CREATE TABLE `#@__node` (
  `node_id` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '节点ID',
  `node_name` varchar(150) NOT NULL COMMENT '名字',
  `node_title` varchar(50) DEFAULT NULL COMMENT '别名',
  `node_status` tinyint(1) DEFAULT '0' COMMENT '状态',
  `node_remark` varchar(300) DEFAULT NULL COMMENT '备注',
  `node_sort` smallint(6) unsigned DEFAULT NULL COMMENT '排序',
  `node_parentid` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '父级ID',
  `node_level` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '级别，1（应用），2（模块），3（方法）',
  `nodegroup_id` tinyint(3) unsigned DEFAULT '0' COMMENT '分组ID',
  `create_dateline` int(10) NOT NULL DEFAULT '0'  COMMENT '创建时间',
  `update_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `node_issystem` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否为系统节点',
  PRIMARY KEY (`node_id`),
  KEY `node_parentid` (`node_parentid`),
  KEY `create_dateline` (`create_dateline`),
  KEY `nodegroup_id` (`nodegroup_id`),
  KEY `node_sort` (`node_sort`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_nodegroup`
--

DROP TABLE IF EXISTS `#@__nodegroup`;
CREATE TABLE `#@__nodegroup` (
  `nodegroup_id` smallint(3) unsigned NOT NULL AUTO_INCREMENT COMMENT '节点分组ID',
  `nodegroup_name` varchar(50) NOT NULL COMMENT '名字，英文',
  `nodegroup_title` varchar(50) NOT NULL COMMENT '别名，中文等注解',
  `create_dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `nodegroup_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态',
  `nodegroup_sort` smallint(3) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `nodegroup_issystem` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否为系统节点分组',
  PRIMARY KEY (`nodegroup_id`),
  KEY `create_dateline` (`create_dateline`),
  KEY `nodegroup_sort` (`nodegroup_sort`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_notice`
--

DROP TABLE IF EXISTS `#@__notice`;
CREATE TABLE `#@__notice` (
  `notice_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '提醒ID',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `notice_type` varchar(20) NOT NULL DEFAULT '' COMMENT '提醒类型',
  `notice_isread` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否已经查看',
  `notice_authorid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '作者ID',
  `notice_authorusername` varchar(50) NOT NULL DEFAULT '' COMMENT '作者用户名字',
  `notice_template` text NOT NULL COMMENT '提示模板',
  `notice_data` text NOT NULL COMMENT '提示数据',
  `create_dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '通知最后更新时间',
  `notice_fromid` int(10) NOT NULL COMMENT '通知来源ID',
  `notice_fromnum` int(10) NOT NULL DEFAULT '0' COMMENT '通知数量',
  `notice_application` varchar(32) NOT NULL DEFAULT 'home' COMMENT '提醒来源应用',
  PRIMARY KEY (`notice_id`),
  KEY `user_id` (`user_id`),
  KEY `create_dateline` (`create_dateline`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_option`
--

DROP TABLE IF EXISTS `#@__option`;
CREATE TABLE `#@__option` (
  `option_name` varchar(32) NOT NULL DEFAULT '' COMMENT '名字',
  `option_value` text NOT NULL COMMENT '值',
  PRIMARY KEY (`option_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_online`
--

DROP TABLE IF EXISTS `#@__online`;
CREATE TABLE `#@__online` (
  `online_hash` char(6) NOT NULL COMMENT '用户标识',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `online_username` varchar(50) NOT NULL COMMENT '登录用户',
  `online_atpage` varchar(50) NOT NULL DEFAULT '' COMMENT '用户所在页面',
  `online_ip` varchar(16) NOT NULL DEFAULT '' COMMENT '活动IP',
  `online_activetime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后活动时间',
  `create_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `online_isstealth` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否隐身',
  KEY `user_id` (`user_id`),
  KEY `online_hash` (`online_hash`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_pm`
--

DROP TABLE IF EXISTS `#@__pm`;
CREATE TABLE `#@__pm` (
  `pm_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '短消息ID',
  `pm_msgfrom` varchar(50) NOT NULL DEFAULT '' COMMENT '来源',
  `pm_msgfromid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '来源用户ID',
  `pm_msgtoid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '接收ID',
  `pm_isread` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否已经阅读',
  `pm_subject` varchar(75) NOT NULL DEFAULT '' COMMENT '主题',
  `create_dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `pm_message` text NOT NULL COMMENT '内容',
  `pm_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '删除状态',
  `pm_mystatus` tinyint(1) NOT NULL DEFAULT '1' COMMENT '我的发件箱短消息状态',
  `pm_fromapp` varchar(32) NOT NULL COMMENT '来源应用',
  `pm_type` enum('system','user') NOT NULL DEFAULT 'user' COMMENT '类型',
  PRIMARY KEY (`pm_id`),
  KEY `pm_msgfromid` (`pm_msgfromid`),
  KEY `pm_msgtoid` (`pm_msgtoid`),
  KEY `create_dateline` (`create_dateline`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_pmsystemdelete`
--

DROP TABLE IF EXISTS `#@__pmsystemdelete`;
CREATE TABLE `#@__pmsystemdelete` (
  `user_id` int(10) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `pm_id` int(10) NOT NULL COMMENT '系统短消息删除状态',
  PRIMARY KEY (`user_id`,`pm_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_pmsystemread`
--

DROP TABLE IF EXISTS `#@__pmsystemread`;
CREATE TABLE `#@__pmsystemread` (
  `user_id` int(10) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `pm_id` int(10) NOT NULL COMMENT '系统短消息阅读状态',
  PRIMARY KEY (`user_id`,`pm_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_process`
--

DROP TABLE IF EXISTS `#@__process`;
CREATE TABLE `#@__process` (
  `process_id` char(32) NOT NULL COMMENT '进程ID',
  `process_expiry` int(10) DEFAULT NULL COMMENT '进程过期时间',
  `process_extra` int(10) DEFAULT NULL COMMENT '进程扩展',
  PRIMARY KEY (`process_id`),
  KEY `process_expiry` (`process_expiry`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_promotion`
--

DROP TABLE IF EXISTS `#@__promotion`;
CREATE TABLE `#@__promotion` (
  `promotion_ip` varchar(16) NOT NULL DEFAULT '' COMMENT '访问推广IP值',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `promotion_username` varchar(50) NOT NULL DEFAULT '' COMMENT '访问推广用户名',
  KEY `promotion_ip` (`promotion_ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_rating`
--

DROP TABLE IF EXISTS `#@__rating`;
CREATE TABLE `#@__rating` (
  `rating_id` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '等级ID',
  `rating_name` varchar(50) NOT NULL COMMENT '名字',
  `rating_remark` varchar(300) DEFAULT NULL COMMENT '备注',
  `rating_nikename` varchar(55) DEFAULT NULL COMMENT '等级别名',
  `create_dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `rating_creditstart` int(10) NOT NULL COMMENT '等级开始积分',
  `rating_creditend` int(10) NOT NULL COMMENT '等级结束积分',
  `ratinggroup_id` tinyint(3) NOT NULL COMMENT '等级分组',
  `rating_icon` varchar(35) NOT NULL COMMENT '等级图标',
  `rating_issystem` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否为系统等级',
  PRIMARY KEY (`rating_id`),
  KEY `ratinggroup_id` (`ratinggroup_id`),
  KEY `create_dateline` (`create_dateline`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_ratinggroup`
--

DROP TABLE IF EXISTS `#@__ratinggroup`;
CREATE TABLE `#@__ratinggroup` (
  `ratinggroup_id` smallint(3) unsigned NOT NULL AUTO_INCREMENT COMMENT '等级分组ID',
  `ratinggroup_name` varchar(25) NOT NULL COMMENT '名字，英文',
  `ratinggroup_title` varchar(50) NOT NULL COMMENT '别名，中文等注解',
  `create_dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `ratinggroup_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态',
  `ratinggroup_sort` smallint(3) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `ratinggroup_issystem` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否为系统等级分组',
  PRIMARY KEY (`ratinggroup_id`),
  KEY `create_dateline` (`create_dateline`),
  KEY `ratinggroup_sort` (`ratinggroup_sort`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_role`
--

DROP TABLE IF EXISTS `#@__role`;
CREATE TABLE `#@__role` (
  `role_id` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '角色ID',
  `role_name` varchar(50) NOT NULL COMMENT '名字',
  `role_parentid` smallint(6) DEFAULT NULL COMMENT '父级ID',
  `role_status` tinyint(1) unsigned DEFAULT NULL COMMENT '状态',
  `role_remark` varchar(300) DEFAULT NULL COMMENT '备注',
  `role_nikename` varchar(55) DEFAULT NULL COMMENT '角色别名',
  `create_dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `rolegroup_id` tinyint(3) NOT NULL DEFAULT '0' COMMENT '角色分组ID',
  `role_issystem` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否为系统角色',
  PRIMARY KEY (`role_id`),
  KEY `create_dateline` (`create_dateline`),
  KEY `rolegroup_id` (`rolegroup_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_rolegroup`
--

DROP TABLE IF EXISTS `#@__rolegroup`;
CREATE TABLE `#@__rolegroup` (
  `rolegroup_id` smallint(3) unsigned NOT NULL AUTO_INCREMENT COMMENT '角色分组ID',
  `rolegroup_name` varchar(50) NOT NULL COMMENT '名字，英文',
  `rolegroup_title` varchar(50) NOT NULL COMMENT '别名，中文等注解',
  `create_dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `rolegroup_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态',
  `rolegroup_sort` smallint(3) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `rolegroup_issystem` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否为系统节点分组',
  PRIMARY KEY (`rolegroup_id`),
  KEY `create_dateline` (`create_dateline`),
  KEY `rolegroup_sort` (`rolegroup_sort`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_slide`
--

DROP TABLE IF EXISTS `#@__slide`;
CREATE TABLE `#@__slide` (
  `slide_id` smallint(6) NOT NULL AUTO_INCREMENT COMMENT '滑动幻灯片状态ID',
  `slide_sort` smallint(6) NOT NULL DEFAULT '0' COMMENT '排序',
  `slide_title` varchar(50) NOT NULL COMMENT '标题',
  `slide_url` varchar(325) NOT NULL COMMENT 'URL地址',
  `slide_img` varchar(325) NOT NULL COMMENT '图片地址',
  `slide_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态',
  `create_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `slide_issystem` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否为系统幻灯片',
  PRIMARY KEY (`slide_id`),
  KEY `create_dateline` (`create_dateline`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_sociatype`
--

DROP TABLE IF EXISTS `#@__sociatype`;
CREATE TABLE `#@__sociatype` (
  `sociatype_id` tinyint(3) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `sociatype_title` varchar(35) NOT NULL COMMENT '标题',
  `sociatype_identifier` varchar(32) NOT NULL COMMENT '社会化帐号唯一标识',
  `sociatype_appid` varchar(80) NOT NULL COMMENT '应用ID',
  `sociatype_appkey` varchar(100) NOT NULL COMMENT 'KEY',
  `sociatype_callback` varchar(325) NOT NULL COMMENT '回调',
  `sociatype_scope` varchar(200) NOT NULL COMMENT '允许的权限',
  `sociatype_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态',
  `create_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `sociatype_issystem` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否为系统系统社会化类型',
  PRIMARY KEY (`sociatype_id`),
  KEY `create_dateline` (`create_dateline`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_sociauser`
--

DROP TABLE IF EXISTS `#@__sociauser`;
CREATE TABLE `#@__sociauser` (
  `sociauser_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sociauser_openid` char(32) NOT NULL DEFAULT '' COMMENT '用户绑定Openid值',
  `user_id` varchar(16) NOT NULL COMMENT '本站用户ID',
  `sociauser_vendor` varchar(20) NOT NULL DEFAULT '' COMMENT '第三方网站名称',
  `sociauser_name` varchar(32) NOT NULL DEFAULT '' COMMENT '名称',
  `sociauser_nikename` varchar(32) NOT NULL DEFAULT '' COMMENT '昵称',
  `sociauser_desc` varchar(100) NOT NULL DEFAULT '' COMMENT '简介',
  `sociauser_url` varchar(100) NOT NULL DEFAULT '' COMMENT '主页',
  `sociauser_img` varchar(100) NOT NULL DEFAULT '' COMMENT '头像',
  `sociauser_img1` varchar(100) NOT NULL COMMENT '头像2',
  `sociauser_img2` varchar(100) NOT NULL COMMENT '头像3',
  `sociauser_gender` varchar(10) NOT NULL DEFAULT '' COMMENT '性别',
  `sociauser_email` varchar(30) NOT NULL DEFAULT '' COMMENT '邮箱',
  `sociauser_location` varchar(20) NOT NULL DEFAULT '' COMMENT '所在地',
  `sociauser_vip` tinyint(3) NOT NULL COMMENT 'vip',
  `sociauser_level` tinyint(3) NOT NULL DEFAULT '0' COMMENT '级别',
  `create_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`sociauser_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_style`
--

DROP TABLE IF EXISTS `#@__style`;
CREATE TABLE `#@__style` (
  `style_id` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '主题样式ID',
  `style_name` varchar(32) NOT NULL DEFAULT '' COMMENT '主题样式名字',
  `style_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '主题样式状态',
  `theme_id` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '模板ID',
  `style_extend` varchar(320) NOT NULL DEFAULT '' COMMENT '主题样式扩展',
  PRIMARY KEY (`style_id`),
  KEY `theme_id` (`theme_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_stylevar`
--

DROP TABLE IF EXISTS `#@__stylevar`;
CREATE TABLE `#@__stylevar` (
  `stylevar_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '变量ID',
  `style_id` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '主题ID',
  `stylevar_variable` text NOT NULL COMMENT '变量名',
  `stylevar_substitute` text NOT NULL COMMENT '变量替换值',
  PRIMARY KEY (`stylevar_id`),
  KEY `style_id` (`style_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_syscache`
--

DROP TABLE IF EXISTS `#@__syscache`;
CREATE TABLE `#@__syscache` (
  `syscache_name` varchar(32) NOT NULL COMMENT '缓存名字',
  `syscache_type` tinyint(3) unsigned NOT NULL COMMENT '缓存类型',
  `create_dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `syscache_data` mediumblob NOT NULL COMMENT '缓存数据',
  PRIMARY KEY (`syscache_name`),
  KEY `create_dateline` (`create_dateline`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_theme`
--

DROP TABLE IF EXISTS `#@__theme`;
CREATE TABLE `#@__theme` (
  `theme_id` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '主题ID',
  `theme_name` varchar(32) NOT NULL DEFAULT '' COMMENT '主题名字',
  `theme_dirname` varchar(32) NOT NULL COMMENT '主题英文目录名字',
  `theme_copyright` varchar(250) NOT NULL DEFAULT '' COMMENT '主题版权',
  PRIMARY KEY (`theme_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_user`
--

DROP TABLE IF EXISTS `#@__user`;
CREATE TABLE `#@__user` (
  `user_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `user_name` varchar(50) CHARACTER SET ucs2 NOT NULL COMMENT '用户名',
  `user_nikename` varchar(50) DEFAULT NULL COMMENT '用户别名',
  `user_password` char(32) NOT NULL COMMENT '用户密码',
  `user_registerip` varchar(40) NOT NULL COMMENT '注册IP',
  `user_lastlogintime` int(10) NOT NULL DEFAULT '0' COMMENT '用户最后登录时间',
  `user_lastloginip` varchar(40) DEFAULT NULL COMMENT '用户登录IP',
  `user_logincount` int(10) DEFAULT '0' COMMENT '用户登录次数',
  `user_email` varchar(150) DEFAULT NULL COMMENT '用户Email',
  `user_remark` varchar(255) DEFAULT NULL COMMENT '用户备注',
  `user_sign` varchar(1000) NOT NULL COMMENT '用户签名',
  `create_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `user_status` tinyint(1) DEFAULT '0' COMMENT '用户状态',
  `user_random` char(6) NOT NULL COMMENT '用户随机码',
  `user_temppassword` varchar(255) NOT NULL COMMENT '密码重置临时密码',
  `user_extendstyle` varchar(35) NOT NULL COMMENT '用户扩展样式',
  `user_verifycode` varchar(255) NOT NULL COMMENT 'Email验证码',
  `user_isverify` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Email是否验证',
  `user_avatar` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否上传头像',
  `user_isstealth` tinyint(1) NOT NULL DEFAULT '0' COMMENT '用户是否隐身',
  `user_isvest` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否为马甲',
  PRIMARY KEY (`user_id`),
  KEY `create_dateline` (`create_dateline`),
  KEY `user_email` (`user_email`),
  KEY `user_name` (`user_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_userguestbook`
--

DROP TABLE IF EXISTS `#@__userguestbook`;
CREATE TABLE `#@__userguestbook` (
  `userguestbook_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '评论ID',
  `create_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `user_id` int(10) NOT NULL DEFAULT '0' COMMENT '用户ID，在线用户评论',
  `userguestbook_name` varchar(50) NOT NULL COMMENT '名字',
  `userguestbook_content` varchar(250) NOT NULL COMMENT '内容',
  `userguestbook_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否显示',
  `userguestbook_ip` varchar(16) NOT NULL COMMENT 'IP',
  `userguestbook_ismobile` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否为手机评论',
  `userguestbook_userid` int(10) NOT NULL DEFAULT '0' COMMENT '被评论用户ID',
  `userguestbook_qq` varchar(20) NOT NULL COMMENT 'QQ号码',
  `userguestbook_mobile` varchar(20) NOT NULL COMMENT '手机号码',
  PRIMARY KEY (`userguestbook_id`),
  KEY `user_id` (`user_id`),
  KEY `create_dateline` (`create_dateline`),
  KEY `userguestbook_useridid` (`userguestbook_userid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_usercount`
--

DROP TABLE IF EXISTS `#@__usercount`;
CREATE TABLE `#@__usercount` (
  `user_id` int(10) unsigned NOT NULL COMMENT '用户ID',
  `usercount_extendcredit1` int(10) NOT NULL DEFAULT '0' COMMENT '第一种积分类型',
  `usercount_extendcredit2` int(10) NOT NULL DEFAULT '0' COMMENT '第二种积分类型',
  `usercount_extendcredit3` int(10) NOT NULL DEFAULT '0' COMMENT '第三种积分类型',
  `usercount_extendcredit4` int(10) NOT NULL DEFAULT '0' COMMENT '第四种积分类型',
  `usercount_extendcredit5` int(10) NOT NULL DEFAULT '0' COMMENT '第五种积分类型',
  `usercount_extendcredit6` int(10) NOT NULL DEFAULT '0' COMMENT '第六种积分类型',
  `usercount_extendcredit7` int(10) NOT NULL DEFAULT '0' COMMENT '第七种积分类型',
  `usercount_extendcredit8` int(10) NOT NULL DEFAULT '0' COMMENT '第八种积分类型',
  `usercount_friends` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '用户好友数量',
  `usercount_oltime` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '用户在线时间',
  `usercount_fans` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户粉丝数量',
  `usercount_homfresh` int(10) NOT NULL DEFAULT '0' COMMENT '新鲜事数量',
  `usercount_grouptopic` int(10) NOT NULL DEFAULT '0' COMMENT '帖子数量',
  `usercount_grouptopiccomment` int(10) NOT NULL DEFAULT '0' COMMENT '帖子回复数量',
  `usercount_extend1` int(10) NOT NULL DEFAULT '0' COMMENT '扩展积分1',
  `usercount_extend2` int(10) NOT NULL DEFAULT '0' COMMENT '扩展积分2',
  `usercount_extend3` int(10) NOT NULL DEFAULT '0' COMMENT '扩展积分3',
  `usercount_extend4` int(10) NOT NULL DEFAULT '0' COMMENT '扩展积分4',
  `usercount_extend5` int(10) NOT NULL DEFAULT '0' COMMENT '扩展积分5',
  `usercount_extend6` int(10) NOT NULL DEFAULT '0' COMMENT '扩展积分6',
  `usercount_extend7` int(10) NOT NULL DEFAULT '0' COMMENT '扩展积分7',
  `usercount_extend8` int(10) NOT NULL DEFAULT '0' COMMENT '扩展积分8',
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_userprofile`
--

DROP TABLE IF EXISTS `#@__userprofile`;
CREATE TABLE `#@__userprofile` (
  `user_id` int(10) unsigned NOT NULL COMMENT '用户ID',
  `userprofile_realname` varchar(255) NOT NULL DEFAULT '' COMMENT '真实姓名',
  `userprofile_gender` tinyint(1) NOT NULL DEFAULT '0' COMMENT '性别',
  `userprofile_birthyear` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '出生年份',
  `userprofile_birthmonth` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '出生月份',
  `userprofile_birthday` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '生日',
  `userprofile_constellation` varchar(255) NOT NULL DEFAULT '' COMMENT '星座',
  `userprofile_zodiac` varchar(255) NOT NULL DEFAULT '' COMMENT '生肖',
  `userprofile_telephone` varchar(255) NOT NULL DEFAULT '' COMMENT '固定电话',
  `userprofile_mobile` varchar(255) NOT NULL DEFAULT '' COMMENT '手机',
  `userprofile_idcardtype` varchar(255) NOT NULL DEFAULT '' COMMENT '证件类型',
  `userprofile_idcard` varchar(255) NOT NULL DEFAULT '' COMMENT '证件号',
  `userprofile_address` varchar(255) NOT NULL DEFAULT '' COMMENT '邮寄地址',
  `userprofile_zipcode` varchar(255) NOT NULL DEFAULT '' COMMENT '邮编',
  `userprofile_nationality` varchar(255) NOT NULL DEFAULT '' COMMENT '国籍',
  `userprofile_birthprovince` varchar(255) NOT NULL DEFAULT '' COMMENT '出生省份',
  `userprofile_birthcity` varchar(255) NOT NULL DEFAULT '' COMMENT '出生地',
  `userprofile_birthdist` varchar(20) NOT NULL DEFAULT '' COMMENT '出生县',
  `userprofile_birthcommunity` varchar(255) NOT NULL DEFAULT '' COMMENT '出生小区',
  `userprofile_resideprovince` varchar(255) NOT NULL DEFAULT '' COMMENT '居住省份',
  `userprofile_residecity` varchar(255) NOT NULL DEFAULT '' COMMENT '居住地',
  `userprofile_residedist` varchar(20) NOT NULL DEFAULT '' COMMENT '居住县',
  `userprofile_residecommunity` varchar(255) NOT NULL DEFAULT '' COMMENT '居住小区',
  `userprofile_residesuite` varchar(255) NOT NULL DEFAULT '' COMMENT '房间',
  `userprofile_graduateschool` varchar(255) NOT NULL DEFAULT '' COMMENT '毕业学校',
  `userprofile_company` varchar(255) NOT NULL DEFAULT '' COMMENT '学历',
  `userprofile_education` varchar(255) NOT NULL DEFAULT '' COMMENT '公司',
  `userprofile_occupation` varchar(255) NOT NULL DEFAULT '' COMMENT '职业',
  `userprofile_position` varchar(255) NOT NULL DEFAULT '' COMMENT '职位',
  `userprofile_revenue` varchar(255) NOT NULL DEFAULT '' COMMENT '年收入',
  `userprofile_affectivestatus` varchar(255) NOT NULL DEFAULT '' COMMENT '情感状态',
  `userprofile_lookingfor` varchar(255) NOT NULL DEFAULT '' COMMENT '交友目的',
  `userprofile_bloodtype` varchar(255) NOT NULL DEFAULT '' COMMENT '血型',
  `userprofile_height` varchar(255) NOT NULL DEFAULT '' COMMENT '身高',
  `userprofile_weight` varchar(255) NOT NULL DEFAULT '' COMMENT '体重',
  `userprofile_alipay` varchar(255) NOT NULL DEFAULT '' COMMENT '支付宝',
  `userprofile_icq` varchar(255) NOT NULL DEFAULT '' COMMENT 'ICQ',
  `userprofile_qq` varchar(255) NOT NULL DEFAULT '' COMMENT 'QQ',
  `userprofile_yahoo` varchar(255) NOT NULL DEFAULT '' COMMENT 'YAHOO帐号',
  `userprofile_msn` varchar(255) NOT NULL DEFAULT '' COMMENT 'MSN',
  `userprofile_taobao` varchar(255) NOT NULL DEFAULT '' COMMENT '阿里旺旺',
  `userprofile_site` varchar(255) NOT NULL DEFAULT '' COMMENT '个人主页',
  `userprofile_bio` text NOT NULL COMMENT '自我介绍',
  `userprofile_interest` text NOT NULL COMMENT '兴趣爱好',
  `userprofile_google` varchar(255) NOT NULL COMMENT 'Google帐号',
  `userprofile_baidu` varchar(255) NOT NULL COMMENT '百度帐号',
  `userprofile_renren` varchar(255) NOT NULL COMMENT '人人帐号',
  `userprofile_douban` varchar(255) NOT NULL COMMENT '豆瓣帐号',
  `userprofile_facebook` varchar(255) NOT NULL COMMENT 'Facebook',
  `userprofile_twriter` varchar(255) NOT NULL COMMENT 'TWriter',
  `userprofile_windsforce` varchar(255) NOT NULL COMMENT 'WindsForce帐号',
  `userprofile_skype` varchar(255) NOT NULL COMMENT 'Skype',
  `userprofile_weibocom` varchar(255) NOT NULL COMMENT '新浪微博',
  `userprofile_tqqcom` varchar(255) NOT NULL COMMENT '腾讯微博',
  `userprofile_diandian` varchar(255) NOT NULL COMMENT '点点网',
  `userprofile_kindergarten` varchar(255) NOT NULL COMMENT '幼儿班',
  `userprofile_primary` varchar(255) NOT NULL COMMENT '小学',
  `userprofile_juniorhighschool` varchar(255) NOT NULL COMMENT '初中',
  `userprofile_highschool` varchar(255) NOT NULL COMMENT '高中',
  `userprofile_university` varchar(255) NOT NULL COMMENT '大学',
  `userprofile_master` varchar(255) NOT NULL COMMENT '硕士',
  `userprofile_dr` varchar(255) NOT NULL COMMENT '博士',
  `userprofile_nowschool` varchar(255) NOT NULL COMMENT '当前学校',
  `userprofile_field1` text NOT NULL COMMENT '自定义字段1',
  `userprofile_field2` text NOT NULL COMMENT '自定义字段2',
  `userprofile_field3` text NOT NULL COMMENT '自定义字段3',
  `userprofile_field4` text NOT NULL COMMENT '自定义字段4',
  `userprofile_field5` text NOT NULL COMMENT '自定义字段5',
  `userprofile_field6` text NOT NULL COMMENT '自定义字段6',
  `userprofile_field7` text NOT NULL COMMENT '自定义字段7',
  `userprofile_field8` text NOT NULL COMMENT '自定义字段8',
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_userprofilesetting`
--

DROP TABLE IF EXISTS `#@__userprofilesetting`;
CREATE TABLE `#@__userprofilesetting` (
  `userprofilesetting_id` varchar(255) NOT NULL DEFAULT '' COMMENT '个人信息字段名字',
  `userprofilesetting_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否启用属性字段',
  `userprofilesetting_title` varchar(255) NOT NULL DEFAULT '' COMMENT '个人信息标题',
  `userprofilesetting_description` varchar(255) NOT NULL DEFAULT '' COMMENT '个人信息描述',
  `userprofilesetting_sort` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '个人信息排序',
  `userprofilesetting_showinfo` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否显示在个人信息中',
  `userprofilesetting_allowsearch` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否允许搜索',
  `userprofilesetting_privacy` tinyint(1) NOT NULL DEFAULT '0' COMMENT '属性隐私 0公开，1好友可见，3保密',
  `userprofilesetting_issystem` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否为系统栏目',
  PRIMARY KEY (`userprofilesetting_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_userrole`
--

DROP TABLE IF EXISTS `#@__userrole`;
CREATE TABLE `#@__userrole` (
  `role_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '角色ID',
  `user_id` char(32) NOT NULL DEFAULT '' COMMENT '用户ID',
  PRIMARY KEY (`role_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_district`
--

DROP TABLE IF EXISTS `#@__district`;
CREATE TABLE `#@__district` (
  `district_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '地区ID',
  `district_name` varchar(255) NOT NULL DEFAULT '' COMMENT '地区名字',
  `district_level` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '地区级别，省份/城市/州县/乡镇',
  `district_upid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级地址ID值',
  `district_sort` smallint(6) NOT NULL DEFAULT '0' COMMENT '地区排序',
  PRIMARY KEY (`district_id`),
  KEY `district_upid` (`district_upid`),
  KEY `district_sort` (`district_sort`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
