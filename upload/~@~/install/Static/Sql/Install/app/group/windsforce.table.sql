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
-- 表的结构 `windsforce_group`
--

DROP TABLE IF EXISTS `#@__group`;
CREATE TABLE `#@__group` (
  `group_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '小组ID',
  `user_id` int(10) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `group_name` char(32) NOT NULL DEFAULT '' COMMENT '小组英文名称',
  `group_nikename` char(32) NOT NULL DEFAULT '' COMMENT '群组别名',
  `group_sort` smallint(6) NOT NULL DEFAULT '0' COMMENT '群组排序',
  `group_description` text NOT NULL COMMENT '小组介绍',
  `group_listdescription` varchar(300) NOT NULL COMMENT '列表小组介绍',
  `group_path` char(32) NOT NULL DEFAULT '' COMMENT '图标路径',
  `group_icon` char(50) DEFAULT NULL COMMENT '小组图标',
  `group_totaltodaynum` int(10) NOT NULL DEFAULT '0' COMMENT '今日发帖总计',
  `group_topicnum` int(10) NOT NULL DEFAULT '0' COMMENT '帖子统计',
  `group_topictodaynum` int(10) NOT NULL DEFAULT '0' COMMENT '统计今天发帖',
  `group_usernum` int(10) NOT NULL DEFAULT '0' COMMENT '小组成员数',
  `group_topiccomment` int(10) NOT NULL DEFAULT '0' COMMENT '回帖数量',
  `group_topiccommenttodaynum` int(10) NOT NULL DEFAULT '0' COMMENT '今日回帖数量',
  `group_joinway` tinyint(1) NOT NULL DEFAULT '0' COMMENT '加入方式',
  `group_roleleader` char(32) NOT NULL DEFAULT '组长' COMMENT '组长角色名称',
  `group_roleadmin` char(32) NOT NULL DEFAULT '管理员' COMMENT '管理员角色名称',
  `group_roleuser` char(32) NOT NULL DEFAULT '成员' COMMENT '成员角色名称',
  `create_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `group_isrecommend` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否推荐',
  `group_isopen` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否公开或者私密',
  `group_ispost` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否允许会员发帖',
  `group_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否显示,状态',
  `group_latestcomment` varchar(230) NOT NULL COMMENT '最近更新帖子',
  `update_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  `group_audittopic` tinyint(1) NOT NULL DEFAULT '0' COMMENT '发贴是否审核',
  `group_auditcomment` tinyint(1) NOT NULL DEFAULT '0' COMMENT '回帖是否审核',
  `group_color` char(7) NOT NULL COMMENT '小组标题颜色',
  `group_headerbg` char(50) NOT NULL COMMENT '群组背景',
  `groupcategory_id` int(10) NOT NULL DEFAULT '0' COMMENT '小组分类ID',
  PRIMARY KEY (`group_id`),
  KEY `user_id` (`user_id`),
  KEY `group_name` (`group_name`),
  KEY `group_sort` (`group_sort`),
  KEY `create_dateline` (`create_dateline`),
  KEY `groupcategory_id` (`groupcategory_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_groupcategory`
--

DROP TABLE IF EXISTS `#@__groupcategory`;
CREATE TABLE `#@__groupcategory` (
  `groupcategory_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '群组分类ID',
  `groupcategory_name` char(32) NOT NULL DEFAULT '' COMMENT '群组分类名字',
  `groupcategory_parentid` int(10) NOT NULL DEFAULT '0' COMMENT '群组上级分类ID',
  `groupcategory_count` int(10) NOT NULL DEFAULT '0' COMMENT '群组个数',
  `groupcategory_sort` smallint(6) NOT NULL DEFAULT '0' COMMENT '群组分类排序名字',
  `update_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  `create_dateline` int(10) NOT NULL COMMENT '群组创建时间',
  PRIMARY KEY (`groupcategory_id`),
  KEY `groupcategory_parentid` (`groupcategory_parentid`),
  KEY `groupcategory_sort` (`groupcategory_sort`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_groupoption`
--

DROP TABLE IF EXISTS `#@__groupoption`;
CREATE TABLE `#@__groupoption` (
  `groupoption_name` varchar(32) NOT NULL DEFAULT '' COMMENT '名字',
  `groupoption_value` text NOT NULL COMMENT '值',
  PRIMARY KEY (`groupoption_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_groupsearchindex`
--

DROP TABLE IF EXISTS `#@__groupsearchindex`;
CREATE TABLE `#@__groupsearchindex` (
  `groupsearchindex_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '搜索索引ID',
  `groupsearchindex_keywords` varchar(255) NOT NULL DEFAULT '' COMMENT '关键字',
  `create_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_dateline` int(10) NOT NULL COMMENT '更新时间',
  `groupsearchindex_expiration` int(10) NOT NULL COMMENT '过期时间',
  `groupsearchindex_searchstring` text NOT NULL COMMENT '配置字符串',
  `groupsearchindex_totals` smallint(6) NOT NULL DEFAULT '0' COMMENT '总结果数',
  `groupsearchindex_ids` text NOT NULL COMMENT '数据索引ID值',
  `groupsearchindex_ip` varchar(16) NOT NULL DEFAULT '' COMMENT 'IP',
  `user_id` int(10) NOT NULL DEFAULT '0' COMMENT '用户',
  PRIMARY KEY (`groupsearchindex_id`),
  KEY `dateline` (`create_dateline`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_grouptopic`
--

DROP TABLE IF EXISTS `#@__grouptopic`;
CREATE TABLE IF NOT EXISTS `#@__grouptopic` (
  `grouptopic_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '主题ID',
  `grouptopiccategory_id` int(10) NOT NULL DEFAULT '0' COMMENT '帖子分类ID',
  `group_id` int(10) NOT NULL DEFAULT '0' COMMENT '群组ID',
  `user_id` int(10) NOT NULL DEFAULT '0' COMMENT '发布帖子用户ID',
  `grouptopic_username` varchar(50) NOT NULL COMMENT '发布帖子用户名',
  `grouptopic_title` varchar(300) NOT NULL DEFAULT '' COMMENT '帖子标题',
  `grouptopic_comments` int(10) NOT NULL DEFAULT '0' COMMENT '帖子回复统计',
  `grouptopic_views` int(10) NOT NULL DEFAULT '0' COMMENT '帖子浏览数',
  `grouptopic_loves` int(10) NOT NULL DEFAULT '0' COMMENT '帖子喜欢数',
  `grouptopic_sticktopic` tinyint(1) NOT NULL DEFAULT '0' COMMENT '帖子是否置顶',
  `grouptopic_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '帖子是否显示',
  `grouptopic_isclose` int(1) NOT NULL DEFAULT '0' COMMENT '帖子是否关闭帖子',
  `grouptopic_color` varchar(120) NOT NULL DEFAULT '' COMMENT '帖子高亮颜色',
  `grouptopic_iscomment` tinyint(1) NOT NULL DEFAULT '1' COMMENT '帖子是否允许评论',
  `grouptopic_addtodigest` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否精华帖子',
  `grouptopic_allownoticeauthor` tinyint(1) NOT NULL DEFAULT '1' COMMENT '接收回复通知',
  `grouptopic_ordertype` tinyint(1) NOT NULL DEFAULT '0' COMMENT '回帖倒序排列',
  `grouptopic_isanonymous` tinyint(1) NOT NULL DEFAULT '0' COMMENT '使用匿名发帖',
  `grouptopic_usesign` tinyint(1) NOT NULL DEFAULT '1' COMMENT '使用签名',
  `grouptopic_hiddenreplies` tinyint(1) NOT NULL DEFAULT '0' COMMENT '回复仅作者可见',
  `grouptopic_latestcomment` varchar(120) NOT NULL COMMENT '最后回复',
  `grouptopic_updateusername` varchar(50) NOT NULL COMMENT '最后更新用户',
  `create_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `grouptopic_thumb` varchar(100) NOT NULL DEFAULT '' COMMENT '缩略图',
  `grouptopic_isrecommend` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否为推荐主题',
  `grouptopic_onlycommentview` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否进回帖后才能够看到',
  `grouptopic_update` int(10) NOT NULL DEFAULT '0' COMMENT '帖子排序更新时间',
  `grouptopic_isshow` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否显示',
  PRIMARY KEY (`grouptopic_id`),
  KEY `grounptopiccategory_id` (`grouptopiccategory_id`),
  KEY `group_id` (`group_id`),
  KEY `user_id` (`user_id`),
  KEY `create_dateline` (`create_dateline`),
  KEY `grouptopic_addtodigest` (`grouptopic_addtodigest`),
  KEY `grouptopic_update` (`grouptopic_update`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_grouptopiccontent`
--

DROP TABLE IF EXISTS `#@__grouptopiccontent`;
CREATE TABLE IF NOT EXISTS `#@__grouptopiccontent` (
  `grouptopic_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '主题ID',
  `grouptopic_content` text NOT NULL COMMENT '帖子内容',
  PRIMARY KEY (`grouptopic_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_grouptopiccategory`
--

DROP TABLE IF EXISTS `#@__grouptopiccategory`;
CREATE TABLE IF NOT EXISTS `#@__grouptopiccategory` (
  `grouptopiccategory_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '帖子分类ID',
  `group_id` int(10) NOT NULL DEFAULT '0' COMMENT '小组ID',
  `grouptopiccategory_name` char(32) NOT NULL DEFAULT '' COMMENT '帖子分类名称',
  `grouptopiccategory_topicnum` int(10) NOT NULL DEFAULT '0' COMMENT '统计帖子',
  `grouptopiccategory_sort` smallint(6) NOT NULL COMMENT '帖子分类排序',
  `create_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`grouptopiccategory_id`),
  KEY `group_id` (`group_id`),
  KEY `grouptopiccategory_sort` (`grouptopiccategory_sort`),
  KEY `create_dateline` (`create_dateline`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_grouptopiclove`
--

DROP TABLE IF EXISTS `#@__grouptopiclove`;
CREATE TABLE `#@__grouptopiclove` (
  `user_id` int(10) NOT NULL DEFAULT '0',
  `grouptopiclove_username` varchar(50) NOT NULL DEFAULT '' COMMENT '用户名',
  `grouptopic_id` int(10) NOT NULL DEFAULT '0',
  `create_dateline` int(11) NOT NULL DEFAULT '0' COMMENT '喜欢时间',
  `grouptopiclove_note` varchar(300) NOT NULL COMMENT '喜欢帖子注释',
  UNIQUE KEY `usergrouptopic_id` (`user_id`,`grouptopic_id`),
  KEY `user_id` (`user_id`),
  KEY `grouptopic_id` (`grouptopic_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_grouptopiccomment`
--

DROP TABLE IF EXISTS `#@__grouptopiccomment`;
CREATE TABLE `#@__grouptopiccomment` (
  `grouptopiccomment_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '评论ID',
  `grouptopic_id` int(10) NOT NULL DEFAULT '0' COMMENT '话题ID',
  `user_id` int(10) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `grouptopiccomment_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态',
  `grouptopiccomment_title` varchar(300) NOT NULL COMMENT '帖子回复标题',
  `grouptopiccomment_name` varchar(50) NOT NULL COMMENT '评论名字',
  `grouptopiccomment_content` text NOT NULL COMMENT '回复内容',
  `grouptopiccomment_ip` varchar(16) NOT NULL COMMENT '评论IP',
  `create_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '回复时间',
  `update_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `grouptopiccomment_parentid` int(10) NOT NULL DEFAULT '0' COMMENT '帖子评论父级ID',
  `grouptopiccomment_ishide` tinyint(1) NOT NULL DEFAULT '0' COMMENT '帖子是否屏蔽',
  `grouptopiccomment_stickreply` tinyint(1) NOT NULL DEFAULT '0' COMMENT '帖子是否置顶',
  PRIMARY KEY (`grouptopiccomment_id`),
  KEY `user_id` (`user_id`),
  KEY `grouptopic_id` (`grouptopic_id`),
  KEY `create_dateline` (`create_dateline`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_grouptopictag`
--

DROP TABLE IF EXISTS `#@__grouptopictag`;
CREATE TABLE `#@__grouptopictag` (
  `grouptopictag_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '帖子标签',
  `grouptopictag_name` char(32) NOT NULL DEFAULT '' COMMENT '标签名字',
  `grouptopictag_count` int(10) NOT NULL DEFAULT '0' COMMENT '标签字体数量',
  `create_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`grouptopictag_id`),
  KEY `grouptopictag_name` (`grouptopictag_name`),
  KEY `create_dateline` (`create_dateline`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_grouptopictagindex`
--

DROP TABLE IF EXISTS `#@__grouptopictagindex`;
CREATE TABLE `#@__grouptopictagindex` (
  `grouptopic_id` int(10) NOT NULL DEFAULT '0' COMMENT '帖子ID',
  `grouptopictag_id` int(10) NOT NULL DEFAULT '0' COMMENT '标签ID',
  PRIMARY KEY (`grouptopic_id`,`grouptopictag_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `windsforce_groupuser`
--

DROP TABLE IF EXISTS `#@__groupuser`;
CREATE TABLE `#@__groupuser` (
  `user_id` int(10) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `group_id` int(10) NOT NULL DEFAULT '0' COMMENT '群组ID',
  `groupuser_isadmin` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否管理员0会员 1管理员 2创始人',
  `create_dateline` int(10) NOT NULL DEFAULT '0' COMMENT '加入时间',
  PRIMARY KEY (`user_id`,`group_id`),
  KEY `create_dateline` (`create_dateline`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
