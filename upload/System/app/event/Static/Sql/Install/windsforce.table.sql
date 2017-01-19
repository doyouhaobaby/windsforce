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
-- 表的结构 `windsforce_event`
--

DROP TABLE IF EXISTS `{WINDSFORCE}event`;
CREATE TABLE `{WINDSFORCE}event` (
  `event_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '活动ID',
  `user_id` int(10) NOT NULL DEFAULT '0' COMMENT '活动发起人user_id',
  `event_username` varchar(50) NOT NULL COMMENT '活动用户名',
  `event_title` varchar(255) NOT NULL COMMENT '活动标题',
  `event_content` text NOT NULL COMMENT '活动详细内容',
  `event_contact` varchar(32) NOT NULL COMMENT '活动联系人',
  `event_jointcontact` varchar(32) NOT NULL COMMENT '联合联系人(主办方)',
  `event_contactsite` varchar(255) NOT NULL COMMENT '活动联系人网站',
  `event_jointcontactsite` varchar(255) NOT NULL COMMENT '活动联合联系人网站',
  `eventcategory_id` int(10) NOT NULL DEFAULT '0' COMMENT '活动类型ID',
  `event_starttime` int(10) NOT NULL DEFAULT '0' COMMENT '开始时间',
  `event_endtime` int(10) NOT NULL DEFAULT '0' COMMENT '结束时间',
  `event_address` varchar(255) NOT NULL COMMENT '活动地点',
  `create_dateline` int(10) NOT NULL COMMENT '创建时间',
  `event_deadline` int(10) NOT NULL DEFAULT '0' COMMENT '报名截止时间',
  `event_joincount` int(10) NOT NULL DEFAULT '0' COMMENT '已加入人数',
  `event_attentioncount` int(10) NOT NULL DEFAULT '0' COMMENT '关注数',
  `event_limitcount` int(10) NOT NULL DEFAULT '0' COMMENT '限制加入数',
  `event_commentcount` int(10) NOT NULL DEFAULT '0' COMMENT '评论数',
  `event_cover` varchar(100) NOT NULL COMMENT '活动封面',
  `event_cost` varchar(255) NOT NULL COMMENT '活动费用',
  `event_costdescription` varchar(255) NOT NULL COMMENT '活动费用说明',
  `event_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '活动状态',
  `event_isaudit` tinyint(1) NOT NULL DEFAULT '0' COMMENT '活动是否需要审核参加用户，0表示不用审核',
  `city_id` int(6) NOT NULL DEFAULT '0' COMMENT '城市编号',
  KEY `eventcategory_id` (`eventcategory_id`),
  KEY `create_dateline` (`create_dateline`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_eventattentionuser`
--

DROP TABLE IF EXISTS `{WINDSFORCE}eventattentionuser`;
CREATE TABLE `{WINDSFORCE}eventattentionuser` (
  `event_id` int(10) NOT NULL COMMENT '活动ID',
  `user_id` int(10) NOT NULL COMMENT '用户ID',
  `create_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`event_id`,`user_id`),
  KEY `create_dateline` (`create_dateline`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_eventcategory`
--

DROP TABLE IF EXISTS `{WINDSFORCE}eventcategory`;
CREATE TABLE `{WINDSFORCE}eventcategory` (
  `eventcategory_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '类型ID',
  `eventcategory_name` varchar(255) NOT NULL COMMENT '类型名称',
  `eventcategory_parentid` int(10) NOT NULL DEFAULT '0' COMMENT '活动上级分类ID',
  `create_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `eventcategory_count` int(10) NOT NULL DEFAULT '0' COMMENT '活动分类数量',
  `eventcategory_sort` smallint(6) NOT NULL DEFAULT '0' COMMENT '活动类型排序',
  PRIMARY KEY (`eventcategory_id`),
  KEY `create_dateline` (`create_dateline`,`eventcategory_sort`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_eventcomment`
--

DROP TABLE IF EXISTS `{WINDSFORCE}eventcomment`;
CREATE TABLE `{WINDSFORCE}eventcomment` (
  `eventcomment_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '评论ID',
  `create_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `user_id` int(10) NOT NULL DEFAULT '0' COMMENT '用户ID，在线用户评论',
  `eventcomment_name` varchar(50) NOT NULL COMMENT '名字',
  `eventcomment_content` varchar(250) NOT NULL COMMENT '内容',
  `eventcomment_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '评论状态',
  `eventcomment_ip` varchar(16) NOT NULL COMMENT 'IP',
  `eventcomment_ismobile` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否为手机评论',
  `event_id` int(10) NOT NULL DEFAULT '0' COMMENT '活动ID',
  `eventcomment_qq` varchar(20) NOT NULL COMMENT 'QQ号码',
  `eventcomment_mobile` varchar(20) NOT NULL COMMENT '手机号',
  PRIMARY KEY (`eventcomment_id`),
  KEY `user_id` (`user_id`),
  KEY `event_id` (`event_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_eventoption`
--

DROP TABLE IF EXISTS `{WINDSFORCE}eventoption`;
CREATE TABLE `{WINDSFORCE}eventoption` (
  `eventoption_name` varchar(32) NOT NULL DEFAULT '' COMMENT '名字',
  `eventoption_value` text NOT NULL COMMENT '值',
  PRIMARY KEY (`eventoption_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_eventuser`
--

DROP TABLE IF EXISTS `{WINDSFORCE}eventuser`;
CREATE TABLE `{WINDSFORCE}eventuser` (
  `event_id` int(10) NOT NULL COMMENT '活动ID',
  `user_id` int(10) NOT NULL COMMENT '用户ID',
  `eventuser_contact` text COMMENT '联系方式',
  `eventuser_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '审核状态',
  `create_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`event_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
