-- WINDSFORCE 數據庫數據
-- version 2.0
-- http://windsforce.114.ms
--
-- 開發: Windsforce TEAM
-- 網站: http://windsforce.114.ms

--
-- 數據庫: 升級數據
--

-- --------------------------------------------------------

--
-- 更新表中的數據 `windsforce_groupoption`
--

DELETE FROM `#@__groupoption` WHERE `groupoption_name`='group_homepagestyle_on';
DELETE FROM `#@__groupoption` WHERE `groupoption_name`='group_homepagestyle';
DELETE FROM `#@__groupoption` WHERE `groupoption_name`='group_homepagestyle_on';
DELETE FROM `#@__groupoption` WHERE `groupoption_name`='group_homepagestyle';

-- --------------------------------------------------------

--
-- 更新表中的數據 `windsforce_option`
--

DELETE FROM `#@__option` WHERE `option_name` = 'allowed_view_siteexplore';
DELETE FROM `#@__option` WHERE `option_name` = 'home_newattachment_num';
DELETE FROM `#@__option` WHERE `option_name` = 'home_newtopic_num';
DELETE FROM `#@__option` WHERE `option_name` = 'home_hottopic_num';
DELETE FROM `#@__option` WHERE `option_name` = 'home_hottopic_date';
DELETE FROM `#@__option` WHERE `option_name` = 'home_recommendgroup_num';
DELETE FROM `#@__option` WHERE `option_name` = 'allowed_view_siteexplore';

-- --------------------------------------------------------

--
-- 更新表中的數據 `windsforce_node`
--

UPDATE `#@__node` SET `node_name` = 'home@ucenter@add_homefresh|post' WHERE `windsforce_node`.`node_id` =45;

-- --------------------------------------------------------

--
-- 更新表中的數據 `windsforce_stylevar`
--

TRUNCATE TABLE  `#@__stylevar`;

INSERT INTO `#@__stylevar` (`stylevar_id`, `style_id`, `stylevar_variable`, `stylevar_substitute`) VALUES
(1, 1, 'img_dir', ''),
(2, 1, 'style_img_dir', ''),
(3, 1, 'logo', 'logo.gif'),
(4, 1, 'header_border_width', '1px'),
(5, 1, 'header_border_color', '#ebebeb'),
(6, 1, 'header_text_color', '#333333'),
(7, 1, 'footer_text_color', '#AAAAAA'),
(8, 1, 'normal_font', '"Helvetica Neue", Helvetica, Arial, sans-serif'),
(9, 1, 'normal_fontsize', '13px'),
(10, 1, 'small_font', 'Verdana,Lucida Grande, Lucida Sans Unicode, Lucida Sans, Helvetica, Arial, sans-serif'),
(11, 1, 'small_fontsize', '0.83em'),
(12, 1, 'big_font', 'Verdana,Lucida Grande, Lucida Sans Unicode, Lucida Sans, Helvetica, Arial, sans-serif'),
(13, 1, 'big_fontsize', '20px'),
(14, 1, 'normal_color', '#333333'),
(15, 1, 'medium_textcolor', '#333333'),
(16, 1, 'light_textcolor', '#999999'),
(17, 1, 'link_color', '#4298BA'),
(18, 1, 'highlightlink_color', '#4298BA'),
(19, 1, 'wrap_table_width', '960px'),
(20, 1, 'wrap_table_bg', '#FFFFFF'),
(21, 1, 'wrap_border_width', '1px'),
(22, 1, 'wrap_border_color', '#FFFFFF'),
(23, 1, 'content_fontsize', '14px'),
(24, 1, 'content_big_size', '16px'),
(25, 1, 'content_width', '90%'),
(26, 1, 'content_separate_color', ''),
(27, 1, 'menu_border_color', '#438079'),
(28, 1, 'menu_text_color', '#d9e7e5'),
(29, 1, 'menu_hover_bg_color', '#A7BBB6'),
(30, 1, 'menu_hover_text_color', '#b0d417'),
(31, 1, 'input_border', '#ccc'),
(32, 1, 'input_border_dark_color', '#c4c4c4'),
(33, 1, 'input_bg', '#FFFFFF'),
(34, 1, 'drop_menu_border', '#FFFFFF'),
(35, 1, 'interval_line_color', '#E6E7E1'),
(36, 1, 'common_background_color', '#f1f1f1'),
(37, 1, 'special_border', '#DEDEDE'),
(38, 1, 'special_bg', '#95B93D'),
(39, 1, 'interleave_color', '#DEDEDE'),
(40, 1, 'noticetext_color', '#FF2B00'),
(41, 1, 'noticetext_border_color', ''),
(42, 1, 'menu_bg_color', '#F6F7F1'),
(43, 1, 'menu_bg_img', ''),
(44, 1, 'menu_bg_extra', ''),
(45, 1, 'header_bg_color', '#438079'),
(46, 1, 'header_bg_img', ''),
(47, 1, 'header_bg_extra', 'none repeat scroll 0 0'),
(48, 1, 'side_bg_color', '#FFFFFF'),
(49, 1, 'side_bg_img', ''),
(50, 1, 'side_bg_extra', ''),
(51, 1, 'bg_color', '#FFFFFF'),
(52, 1, 'bg_img', 'bg.png'),
(53, 1, 'bg_extra', 'repeat'),
(54, 1, 'drop_menu_bg_color', '#CCCCCC'),
(55, 1, 'drop_menu_bg_img', ''),
(56, 1, 'drop_menu_bg_extra', ''),
(57, 1, 'footer_bg_color', '#5B6566'),
(58, 1, 'footer_bg_img', ''),
(59, 1, 'footer_bg_extra', 'none repeat scroll'),
(60, 1, 'float_bg_color', '#FFFFFF'),
(61, 1, 'float_bg_img', ''),
(62, 1, 'float_bg_extra', ''),
(63, 1, 'float_mask_bg_color', '#FFFFFF'),
(64, 1, 'float_mask_bg_img', ''),
(65, 1, 'float_mask_bg_extra', '');
