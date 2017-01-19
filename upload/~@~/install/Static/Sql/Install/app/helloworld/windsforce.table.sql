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
-- 表的结构 `windsforce_helloworldoption`
--

DROP TABLE IF EXISTS `#@__helloworldoption`;
CREATE TABLE `#@__helloworldoption` (
  `helloworldoption_name` varchar(32) NOT NULL DEFAULT '' COMMENT '名字',
  `helloworldoption_value` text NOT NULL COMMENT '值',
  PRIMARY KEY (`helloworldoption_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
