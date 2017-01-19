<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   WindsForce 升级程序($$)*/

!defined('Q_PATH') && exit;

class UpdateController extends Controller{

	protected $_sUpdatefile='';

	public function init__(){
		parent::init__();

		exit('升级程序暂时未搞定，后面会提供转换脚本来升级。');
	}

	public function check_update(){
		$this->_sUpdatefile=WINDSFORCE_PATH.'/user/lock/Update.lock.php';

		if(is_file($this->_sUpdatefile)){
			$this->E(Q::L("程序已运行升级，如果你确定要重新升级（可能出现错误），请先从FTP中删除 %s",'Controller/Update',null,str_replace(C::tidyPath(WINDSFORCE_PATH),'{WINDSFORCE_PATH}',C::tidyPath($this->_sUpdatefile))));
		}
	}

	public function get_progress(){
		if(ACTION_NAME==='index'){
			return 20;
		}elseif(ACTION_NAME==='step2'){
			return 40;
		}elseif(ACTION_NAME==='step3'){
			return 60;
		}elseif(in_array(ACTION_NAME,array('first','second','three'))){
			return 80;
		}elseif(ACTION_NAME==='success'){
			return 100;
		}

		return 0;
	}

	public function index(){
		$this->check_update();
		$this->display('update+step1');
	}

	public function step2(){
		$oIndexController=new IndexController();
		$oIndexController->step2();
	}

	public function step3(){
		$this->check_update();

		$sConfigfile=WINDSFORCE_PATH.'/~@~/Config.inc.php';
		if(is_file($sConfigfile)){
			$arrConfig=(array)(include $sConfigfile);
		}else{
			$this->E(Q::L('数据库连接配置文件 %s 不存在','Controller/Update',null,$sConfigfile));
		}

		$this->assign('arrConfig',$arrConfig);

		$this->display('update+step3');
	}

	public function db_connect(){
		global $hConn,$sSql4Tmp,$sDbprefix,$nMysqlVersion;

		$arrConfig=(array)(include WINDSFORCE_PATH.'/~@~/Config.inc.php');
		if(empty($arrConfig['RBAC_DATA_PREFIX'])){
			$this->E(Q::L('Rbac前缀不能为空','Controller/Update'));
		}

		if(empty($arrConfig['COOKIE_PREFIX'])){
			$this->E(Q::L('Cookie前缀不能为空','Controller/Update'));
		}

		if(!$hConn=@mysql_connect($arrConfig['DB_HOST'],$arrConfig['DB_USER'],$arrConfig['DB_PASSWORD'])){
			$this->E(Q::L('数据库服务器或登录密码无效','Controller/Update').",".Q::L('无法连接数据库，请重新设定','Controller/Update'));
		}

		if(!mysql_select_db($arrConfig['DB_NAME'])){
			$this->E(Q::L('选择数据库失败，可能是你没权限，请预先创建一个数据库','Controller/Update'));
		}

		$sRs=Install_Extend::queryString("SELECT VERSION();");
		$arrRow=mysql_fetch_array($sRs);
		$arrMysqlVersions=explode('.',trim($arrRow[0]));
		$nMysqlVersion=$arrMysqlVersions[0].".".$arrMysqlVersions[1];
		Install_Extend::queryString("SET NAMES 'UTF8',character_set_client=binary,sql_mode='';");

		// 保存配置数据
		$arrConfigDefault=(array)(include WINDSFORCE_PATH.'/System/common/ConfigDefault.inc.php');

		// 数据库连接相关
		$arrConfigDefault['DB_PASSWORD']=$arrConfig['DB_PASSWORD'];
		$arrConfigDefault['DB_PREFIX']=$arrConfig['DB_PREFIX'];
		$arrConfigDefault['DB_NAME']=$arrConfig['DB_NAME'];
		$arrConfigDefault['DB_HOST']=$arrConfig['DB_HOST'];
		$arrConfigDefault['DB_USER']=$arrConfig['DB_USER'];

		// 安全配置
		$arrConfigDefault['USER_AUTH_KEY']='authid'.C::randString(6);
		$arrConfigDefault['ADMIN_AUTH_KEY']='admin'.C::randString(6);
		$arrConfigDefault['QEEPHP_AUTH_KEY']='qeephpauthkey'.C::randString(6);
		$arrConfigDefault['RBAC_DATA_PREFIX']='rbac_';
		$arrConfigDefault['COOKIE_PREFIX']='wf'.C::randString(6);

		// 恢复一些其他配置
		$arrConfigDefault['URL_MODEL']=$arrConfig['URL_MODEL'];
		$arrConfigDefault['URL_DOMAIN']=$arrConfig['URL_DOMAIN'];
		$arrConfigDefault['DEFAULT_APP']=$arrConfig['DEFAULT_APP'];

		if(!file_put_contents(WINDSFORCE_PATH.'/~@~/Config.inc.php',
			"<?php\n /* QeePHP Config File,Do not to modify this file! */ \n return ".
			var_export($arrConfigDefault,true).
			"\n?>")
		){
			$this->E(Q::L('写入配置失败，请检查 %s目录是否可写入','Controller/Install',null,WINDSFORCE_PATH.'/Config'));
		}

		// 前缀
		$sDbprefix=$arrConfig['DB_PREFIX'];
	
		// 防止乱码
		$sSql4Tmp='';
		if($nMysqlVersion>=4.1){
			$sSql4Tmp="ENGINE=MyISAM DEFAULT CHARSET=UTF8";
		}
	}

	public function first(){
		global $hConn,$sSql4Tmp,$sDbprefix,$nMysqlVersion;

		$this->check_update();
		$this->db_connect();

		// 加载升级界面
		$this->assign('sUpdateTitle',Q::L('数据库结构添加与更新','Controller/Update'));
		$this->display('update_message');

		// 开始执行数据库结构升级
		Install_Extend::showJavascriptMessage('<h3>'.Q::L('数据库结构添加与更新','Controller/Update').'</h3>');

		// 更新系统版本信息
		Install_Extend::queryString("UPDATE  `{$sDbprefix}option` SET  `option_value` =  '1.2' WHERE  `windsforce_option`.`option_name` =  'windsforce_program_version';");
		
		Install_Extend::queryString("UPDATE  `{$sDbprefix}option` SET  `option_value` =  '2012-2014' WHERE  `windsforce_option`.`option_name` =  'windsforce_program_year';");
		
		Install_Extend::queryString("UPDATE  `{$sDbprefix}option` SET  `option_value` =  '2012-2014' WHERE  `windsforce_option`.`option_name` =  'windsforce_program_year';");

		// 结构变更
		Install_Extend::queryString("ALTER TABLE  `{$sDbprefix}group` CHANGE  `group_icon`  `group_icon` CHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT  '小组图标';");

		Install_Extend::queryString("ALTER TABLE  `{$sDbprefix}group` CHANGE  `group_headerbg`  `group_headerbg` CHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT  '群组背景';");

		if(Install_Extend::columnExists('groupcategory','groupcategory_groupmaxnum')){
			Install_Extend::queryString("ALTER TABLE `{$sDbprefix}groupcategory` DROP `groupcategory_groupmaxnum`;");
		}
		
		if(Install_Extend::columnExists('groupcategory','groupcategory_columns')){
			Install_Extend::queryString("ALTER TABLE `{$sDbprefix}groupcategory` DROP `groupcategory_columns`;");
		}
		
		if(Install_Extend::columnExists('sociauser','sociauser_appid')){
			Install_Extend::queryString("ALTER TABLE `{$sDbprefix}sociauser` DROP `sociauser_appid`;");
		}
		
		if(Install_Extend::columnExists('sociauser','sociauser_keys')){
			Install_Extend::queryString("ALTER TABLE `{$sDbprefix}sociauser` DROP `sociauser_keys`;");
		}

		if(!Install_Extend::columnExists('homefresh','homefresh_type')){
			Install_Extend::queryString("ALTER TABLE  `{$sDbprefix}homefresh` ADD  `homefresh_type` TINYINT( 1 ) NOT NULL DEFAULT  '1' COMMENT '新鲜事类型：1文字,2音乐,3图片,4视频,5电影,6购物' AFTER  `homefresh_status`;");
		}
		
		if(!Install_Extend::columnExists('homefresh','homefresh_attribute')){
			Install_Extend::queryString("ALTER TABLE  `{$sDbprefix}homefresh` ADD `homefresh_attribute` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT  '新鲜事扩展属性值' AFTER  `homefresh_type`;");
		}
		
		if(!Install_Extend::columnExists('session','create_dateline')){
			Install_Extend::queryString("ALTER TABLE  `{$sDbprefix}session` ADD  `create_dateline` INT( 10 ) NOT NULL COMMENT  '创建时间' AFTER  `session_ip`;");
		}
		
		Install_Extend::queryString("DELETE FROM `{$sDbprefix}groupoption` WHERE 
		`groupoption_name`='group_indexgroupmaxnum';");
		
		// 执行结束
		Install_Extend::showJavascriptMessage('');
		Install_Extend::showJavascriptMessage('<h3>'.Q::L('数据库结构添加与更新执行完毕','Controller/Update').'</h3>');
		Install_Extend::showJavascriptMessage('<h3 style="color:red;">'.Q::L('程序将会在3秒后继续执行，请勿关闭窗口','Controller/Update').'</h3>');

		// 系统跳转
		echo<<<WINDSFORCE
		<script type="text/javascript">
			function setLaststep(){
				setTimeout(function(){
					$WF("laststep").disabled=false;
					window.location=Q.U('update/second');
				},3000);
			}
			setLaststep();
		</script>
WINDSFORCE;

		exit();
	}

	public function second(){
		global $hConn,$sSql4Tmp,$sDbprefix,$nMysqlVersion;

		$this->check_update();

		$this->db_connect();
		
		// 加载升级界面
		$this->assign('sUpdateTitle',Q::L('数据库数据添加与更新','Controller/Update'));
		$this->display('update_message');

		$sLangCookieName=$GLOBALS['_commonConfig_']['COOKIE_LANG_TEMPLATE_INCLUDE_APPNAME']===true?APP_NAME.'_language':'language';
		$sWindsForceDatadir=APP_PATH.'/Static/Sql/Update';
		
		// 开始写入和更新数据库数据
		Install_Extend::showJavascriptMessage('<h3>'.Q::L('数据库数据添加与更新','Controller/Update').'</h3>');

		$sWindsForceDatapath=$sWindsForceDatadir.'/'.ucfirst(Q::cookie($sLangCookieName)).'/windsforce.data.sql';
		if(!is_file($sWindsForceDatapath)){
			$sWindsForceDatapath=$sWindsForceDatadir.'/Zh-cn/windsforce.data.sql';
		}
		Install_Extend::runQuery($sWindsForceDatapath);

		// 执行结束
		Install_Extend::showJavascriptMessage('');
		Install_Extend::showJavascriptMessage('<h3>'.Q::L('数据库数据添加与更新执行完毕','Controller/Update').'</h3>');
		Install_Extend::showJavascriptMessage('<h3 style="color:red;">'.Q::L('程序将会在3秒后继续执行，请勿关闭窗口','Controller/Update').'</h3>');

		// 系统跳转
		echo<<<WINDSFORCE
		<script type="text/javascript">
			function setLaststep(){
				setTimeout(function(){
					$WF("laststep").disabled=false;
					window.location=Q.U('update/three');
				},3000);
			}
			setLaststep();
		</script>
WINDSFORCE;

		exit();
	}

	public function three(){
		global $hConn,$sSql4Tmp,$sDbprefix,$nMysqlVersion;

		$this->check_update();
		$this->db_connect();
		
		// 加载升级界面
		$this->assign('sUpdateTitle',Q::L('数据库数据添加与更新','Controller/Update'));
		$this->display('update_message');

		// 开始清理数据库数据
		Install_Extend::showJavascriptMessage('<h3>'.Q::L('多余数据库结构删除','Controller/Update').'</h3>');

		if(Install_Extend::indexExists('access','access_status')){
			Install_Extend::queryString("ALTER TABLE `{$sDbprefix}access` DROP INDEX `access_status`;");
		}

		if(Install_Extend::indexExists('adminctrlmenu','adminctrlmenu_sort')){
			Install_Extend::queryString("ALTER TABLE `{$sDbprefix}adminctrlmenu` DROP INDEX adminctrlmenu_sort;");
		}

		if(Install_Extend::indexExists('attachmentcategory','attachmentcategory_recommend')){
			Install_Extend::queryString("ALTER TABLE `{$sDbprefix}attachmentcategory` DROP INDEX attachmentcategory_recommend;");
		}

		if(Install_Extend::indexExists('attachmentcomment','attachmentcomment_status')){
			Install_Extend::queryString("ALTER TABLE `{$sDbprefix}attachmentcomment` DROP INDEX attachmentcomment_status;");
		}

		if(Install_Extend::indexExists('creditrulelog','update_dateline')){
			Install_Extend::queryString("ALTER TABLE `{$sDbprefix}creditrulelog` DROP INDEX update_dateline;");
		}

		if(Install_Extend::indexExists('friend','friend_status')){
			Install_Extend::queryString("ALTER TABLE `{$sDbprefix}friend` DROP INDEX friend_status;");
		}

		if(Install_Extend::indexExists('group','group_status')){
			Install_Extend::queryString("ALTER TABLE `{$sDbprefix}group` DROP INDEX group_status;");
		}

		if(Install_Extend::indexExists('grouptopic','grouptopic_status')){
			Install_Extend::queryString("ALTER TABLE `{$sDbprefix}grouptopic` DROP INDEX grouptopic_status;");
		}

		if(Install_Extend::indexExists('grouptopiccomment','grouptopiccoment_status')){
			Install_Extend::queryString("ALTER TABLE `{$sDbprefix}grouptopiccomment` DROP INDEX grouptopiccoment_status;");
		}

		if(Install_Extend::indexExists('homefresh','homefresh_status')){
			Install_Extend::queryString("ALTER TABLE `{$sDbprefix}homefresh` DROP INDEX homefresh_status;");
		}

		if(Install_Extend::indexExists('homefreshcomment','homefreshcomment_status')){
			Install_Extend::queryString("ALTER TABLE `{$sDbprefix}homefreshcomment` DROP INDEX homefreshcomment_status;");
		}

		if(Install_Extend::indexExists('grouptopiccomment','homefreshtag_status')){
			Install_Extend::queryString("ALTER TABLE `{$sDbprefix}grouptopiccomment` DROP INDEX homefreshtag_status;");
		}

		if(Install_Extend::indexExists('homehelp','homehelp_status')){
			Install_Extend::queryString("ALTER TABLE `{$sDbprefix}homehelp` DROP INDEX homehelp_status;");
		}

		if(Install_Extend::indexExists('link','link_status')){
			Install_Extend::queryString("ALTER TABLE `{$sDbprefix}link` DROP INDEX link_status;");
		}

		if(Install_Extend::indexExists('loginlog','loginlog_status')){
			Install_Extend::queryString("ALTER TABLE `{$sDbprefix}loginlog` DROP INDEX loginlog_status;");
		}

		if(Install_Extend::indexExists('nav','nav_status')){
			Install_Extend::queryString("ALTER TABLE `{$sDbprefix}nav` DROP INDEX nav_status;");
		}

		if(Install_Extend::indexExists('node','node_level')){
			Install_Extend::queryString("ALTER TABLE `{$sDbprefix}node` DROP INDEX node_level;");
		}
		
		if(Install_Extend::indexExists('node','node_status')){
			Install_Extend::queryString("ALTER TABLE `{$sDbprefix}node` DROP INDEX node_status;");
		}
		
		if(Install_Extend::indexExists('node','node_name')){
			Install_Extend::queryString("ALTER TABLE `{$sDbprefix}node` DROP INDEX node_name;");
		}
		
		if(Install_Extend::indexExists('nodegroup','nodegroup_status')){
			Install_Extend::queryString("ALTER TABLE `{$sDbprefix}nodegroup` DROP INDEX nodegroup_status;");
		}
		
		if(Install_Extend::indexExists('nodegroup','nodegroup_name')){
			Install_Extend::queryString("ALTER TABLE `{$sDbprefix}nodegroup` DROP INDEX nodegroup_name;");
		}
		
		if(Install_Extend::indexExists('notice','notice_isread')){
			Install_Extend::queryString("ALTER TABLE `{$sDbprefix}notice` DROP INDEX notice_isread;");
		}
		
		if(Install_Extend::indexExists('pm','pm_status')){
			Install_Extend::queryString("ALTER TABLE `{$sDbprefix}pm` DROP INDEX pm_status;");
		}
		
		if(Install_Extend::indexExists('rating','rating_nikename')){
			Install_Extend::queryString("ALTER TABLE `{$sDbprefix}rating` DROP INDEX rating_nikename;");
		}
		
		if(Install_Extend::indexExists('rating_name','rating_name')){
			Install_Extend::queryString("ALTER TABLE `{$sDbprefix}rating` DROP INDEX rating_name;");
		}
		
		if(Install_Extend::indexExists('ratinggroup','ratinggroup_status')){
			Install_Extend::queryString("ALTER TABLE `{$sDbprefix}ratinggroup` DROP INDEX ratinggroup_status;");
		}
		
		if(Install_Extend::indexExists('ratinggroup','ratinggroup_name')){
			Install_Extend::queryString("ALTER TABLE `{$sDbprefix}ratinggroup` DROP INDEX ratinggroup_name;");
		}
		
		if(Install_Extend::indexExists('role','role_status')){
			Install_Extend::queryString("ALTER TABLE `{$sDbprefix}role` DROP INDEX role_status;");
		}
		
		if(Install_Extend::indexExists('role','role_name')){
			Install_Extend::queryString("ALTER TABLE `{$sDbprefix}role` DROP INDEX role_name;");
		}
		
		if(Install_Extend::indexExists('role','role_nikename')){
			Install_Extend::queryString("ALTER TABLE `{$sDbprefix}role` DROP INDEX role_nikename;");
		}
		
		if(Install_Extend::indexExists('rolegroup','rolegroup_status')){
			Install_Extend::queryString("ALTER TABLE `{$sDbprefix}rolegroup` DROP INDEX rolegroup_status;");
		}
		
		if(Install_Extend::indexExists('rolegroup','rolegroup_name')){
			Install_Extend::queryString("ALTER TABLE `{$sDbprefix}rolegroup` DROP INDEX rolegroup_name;");
		}
		
		if(Install_Extend::indexExists('slide','slide_status')){
			Install_Extend::queryString("ALTER TABLE `{$sDbprefix}slide` DROP INDEX slide_status;");
		}
		
		if(Install_Extend::indexExists('grouptopiccomment','grouptopiccoment_status')){
			Install_Extend::queryString("ALTER TABLE `{$sDbprefix}sociatype` DROP INDEX status;");
		}
		
		if(Install_Extend::indexExists('user','user_status')){
			Install_Extend::queryString("ALTER TABLE `{$sDbprefix}user` DROP INDEX user_status;");
		}
		
		if(Install_Extend::indexExists('user','user_password')){
			Install_Extend::queryString("ALTER TABLE `{$sDbprefix}user` DROP INDEX user_password;");
		}
		
		if(Install_Extend::indexExists('userguestbook','userguestbook_status')){
			Install_Extend::queryString("ALTER TABLE `{$sDbprefix}userguestbook` DROP INDEX userguestbook_status;");
		}
		
		if(Install_Extend::indexExists('sociauser','create_dateline')){
			Install_Extend::queryString("ALTER TABLE `{$sDbprefix}sociauser` DROP INDEX create_dateline;");
		}

		// Event应用
		if(Install_Extend::tableExists('event')){
			if(Install_Extend::indexExists('event','event_status')){
				Install_Extend::queryString("ALTER TABLE `{$sDbprefix}event` DROP INDEX event_status;");
			}
		}

		if(Install_Extend::tableExists('eventcategory')){
			if(!Install_Extend::indexExists('eventcategory','grouptopiccoment_status') && !Install_Extend::indexExists('eventcategory','eventcategory_sort')){
				Install_Extend::queryString("ALTER TABLE  `{$sDbprefix}eventcategory` ADD INDEX (`create_dateline` ,  `eventcategory_sort`);");
			}
		}

		if(Install_Extend::tableExists('eventcomment')){
			if(Install_Extend::indexExists('eventcomment','eventcomment_status')){
				Install_Extend::queryString("ALTER TABLE `{$sDbprefix}eventcomment` DROP INDEX eventcomment_status;");
			}
		}

		// 写入锁定文件
		if(!file_put_contents($this->_sUpdatefile,'ok')){
			$this->E(Q::L('写入升级锁定文件失败，请检查%s目录是否可写入','Controller/Update',null,WINDSFORCE_PATH.'/data'));
		}
		Install_Extend::showJavascriptMessage(Q::L('写入升级程序锁定文件','Controller/Update').'... '.Q::L('成功','Controller/Common'));
		Install_Extend::showJavascriptMessage(' ');

		// 执行清理
		Install_Extend::showJavascriptMessage('<h3>'.Q::L('清理系统缓存目录','Controller/Install').'</h3>');
		foreach(array('app','data','field','style_') as $sCacheDir){
			if(is_dir(WINDSFORCE_PATH.'/~@~/'.$sCacheDir)){
				Install_Extend::removeDir(WINDSFORCE_PATH.'/~@~/'.$sCacheDir);
			}
		}

		// 初始化系统和跳转
		$sInitsystemUrl=trim(Q::G('baseurl')).'/index.php?app=home&c=misc&a=init_system&update=1&l='.strtolower($arrConfig['FRONT_LANGUAGE_DIR']);

		// 将升级数据传回官方服务器以便于统计用户
		$sIp=C::getIp();
		$sDomain=$_SERVER['HTTP_HOST'];
		$sServUrl='http://qeephp.114.ms/index.php?app=service&c=install&a=index&ip='.urlencode($sIp).'&domain='.urlencode($sDomain).'&version='.urlencode(WINDSFORCE_SERVER_VERSION).'&release='.urlencode(WINDSFORCE_SERVER_RELEASE).'&bug='.urlencode(WINDSFORCE_SERVER_BUG).'&update=1';
		
		echo<<<WINDSFORCE
		<script type="text/javascript">
			function setLaststep(){
				setTimeout(function(){
					$WF("laststep").disabled=false;
					window.location=Q.U('update/success');
				},1000);
			}
		</script>
		<script type="text/javascript" src="{$sServUrl}"></script>
		<script type="text/javascript">setTimeout(function(){window.location=window.location=Q.U('update/success');},20000);
		</script>
		<iframe src="{$sInitsystemUrl}" style="display:none;" onload="setLaststep()"></iframe>
WINDSFORCE;

		exit();
	}

	public function success(){
		$this->display('update+success');
	}

}
