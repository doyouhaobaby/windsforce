<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   WindsForce 安装程序($$)*/

!defined('Q_PATH') && exit;

/** 配置WindsForce默认数据库名字 */
define('WINDSFORCE_DATABASE','windsforce_v'.WINDSFORCE_SERVER_RELEASE);

class PublicController extends Controller{

	protected $_sLockfile='';

	public function init__(){
		parent::init__();

		if(ACTION_NAME!=='success'){
			$this->check();
		}
	}

	public function check(){
		$sInstallLockfile=WINDSFORCE_PATH.'/user/lock/Install.lock.php';
		$sUpdateLockfile=WINDSFORCE_PATH.'/user/lock/Update.lock.php';

		if(is_file($sInstallLockfile) && is_file($sUpdateLockfile)){
			$this->E(Q::L("程序已经锁定，既不需要安装也不需要升级，如有需要请删除安装锁定文件 %s 或者升级锁定文件 %s",'Controller/Common',null,$sInstallLockfile,$sUpdateLockfile));
		}
	}

	public function check_install(){
		$this->_sLockfile=WINDSFORCE_PATH.'/user/lock/Install.lock.php';

		if(is_file($this->_sLockfile)){
			$this->E(Q::L("程序已运行安装，如果你确定要重新安装，请先从FTP中删除 %s",'Controller/Install',null,str_replace(C::tidyPath(WINDSFORCE_PATH),'{WINDSFORCE_PATH}',C::tidyPath($this->_sLockfile))));
		}
	}

	public function index(){
		$this->assign('arrInstallLangs',C::listDir(APP_PATH.'/App/Lang'));
		$this->display('step+language');
	}

	public function select(){
		$this->display('step+select');
	}

	public function get_progress(){
		if(ACTION_NAME==='step1'){
			return 20;
		}elseif(ACTION_NAME==='step2'){
			return 40;
		}elseif(ACTION_NAME==='step3'){
			return 60;
		}elseif(ACTION_NAME==='install'){
			return 80;
		}elseif(ACTION_NAME==='success'){
			return 100;
		}

		return 0;
	}

	public function step1(){
		$this->check_install();

		// 版权信息
		if(is_file(WINDSFORCE_PATH."/user/language/".LANG_NAME."/LICENSE.txt")){
			$sCopyTxt=nl2br(file_get_contents(WINDSFORCE_PATH."/user/language/".LANG_NAME."/LICENSE.txt"));
		}else{
			$sCopyTxt=nl2br(file_get_contents(WINDSFORCE_PATH."/user/language/Zh-cn/LICENSE.txt"));
		}
		$this->assign('sCopyTxt',$sCopyTxt);
		$this->display('install+step1');
	}

	public function step2(){
		// 取得服务器相关信息
		$arrInfo=array();

		$arrInfo['phpv']=phpversion();
		$arrInfo['sp_os']=PHP_OS;
		
		$arrInfo['sp_gd']=Install_Extend::gdVersion();
		
		$arrInfo['sp_server']=$_SERVER['SERVER_SOFTWARE'];
		$arrInfo['sp_host']=(empty($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_HOST']:$_SERVER['REMOTE_ADDR']);
		$arrInfo['sp_name']=$_SERVER['SERVER_NAME'];
		$arrInfo['sp_max_execution_time']=ini_get('max_execution_time');
		
		$arrInfo['sp_allow_reference']=(ini_get('allow_call_time_pass_reference')?'<font color=green>[√]On</font>':'<font color=red>[×]Off</font>');
		$arrInfo['sp_allow_url_fopen']=(ini_get('allow_url_fopen')?'<font color=green>[√]On</font>':'<font color=red>[×]Off</font>');
		
		$arrInfo['sp_safe_mode']=(ini_get('safe_mode')?'<font color=red>[×]On</font>':'<font color=green>[√]Off</font>');
		$arrInfo['sp_gd']=($arrInfo['sp_gd']>0?'<font color=green>[√]On</font>':'<font color=red>[×]Off</font>');
		
		$arrInfo['sp_mysql']=(function_exists('mysql_connect')?'<font color=green>[√]On</font>':'<font color=red>[×]Off</font>');
		if($arrInfo['sp_mysql']=='<font color=red>[×]Off</font>'){
			$bSpMysqlErr=TRUE;
		}else{
			$bSpMysqlErr=FALSE;
		}

		// 系统安装权限检查
		$arrSpTestDirs=(array)(include WINDSFORCE_PATH.'/System/common/Cache.php');

		$this->assign('arrInfo',$arrInfo);
		$this->assign('bSpMysqlErr',$bSpMysqlErr);
		$this->assign('arrSpTestDirs',$arrSpTestDirs);
		$this->display('install+step2');
	}

	public function step3(){
		$this->check_install();

		if(!empty($_SERVER['HTTP_HOST'])){
			$sBaseurl='http://'.$_SERVER['HTTP_HOST'];
		}else{
			$sBaseurl="http://".$_SERVER['SERVER_NAME'];
		}

		$arrApps=C::listDir(APP_PATH.'/Static/Sql/Install/Zh-cn/App');

		$this->assign('sBasepath',$sBaseurl);
		$this->assign('sBaseurl',$sBaseurl);
		$this->assign('arrApps',$arrApps);
		$this->display('install+step3');
	}

	public function install(){
		global $hConn,$sSql4Tmp,$sDbprefix,$nMysqlVersion;

		$this->check_install();

		// 获取表单数据
		$sDbhost=trim(Q::G('dbhost'));
		$sDbuser=trim(Q::G('dbuser'));
		$sDbpwd=trim(Q::G('dbpwd'));
		$sDbname=trim(Q::G('dbname'));
		$sDbprefix=trim(Q::G('dbprefix'));
		$sAdminuser=trim(Q::G('adminuser'));
		$sAdminpwd=trim(Q::G('adminpwd'));
		$sCookieprefix=trim(Q::G('cookieprefix'));
		$sRbacprefix=trim(Q::G('rbacprefix'));

		// 验证表单
		if(empty($sAdminuser)){
			$this->E(Q::L('管理员帐号不能为空','Controller/Install'));
		}

		if(!preg_match('/^[a-z0-9\-\_]*[a-z\-_]+[a-z0-9\-\_]*$/i',$sAdminuser)){
			$this->E(Q::L('管理员帐号只能是由英文,字母和下划线组成','Controller/Install'));
		}

		if(empty($sAdminpwd)){
			$this->E(Q::L('管理员密码不能为空','Controller/Install'));
		}

		if(empty($sRbacprefix)){
			$this->E(Q::L('Rbac前缀不能为空','Controller/Install'));
		}

		if(empty($sCookieprefix)){
			$this->E(Q::L('Cookie前缀不能为空','Controller/Install'));
		}

		if((!$hConn=@mysql_connect($sDbhost,$sDbuser,$sDbpwd))){
			$this->E(Q::L('数据库服务器或登录密码无效','Controller/Install').",".Q::L('无法连接数据库，请重新设定','Controller/Install'));
		}

		Install_Extend::queryString("CREATE DATABASE IF NOT EXISTS `".$sDbname."`;");

		if(!mysql_select_db($sDbname)){
			$this->E(Q::L('选择数据库失败，可能是你没权限，请预先创建一个数据库','Controller/Install'));
		}

		// 取得数据库版本
		$sRs=Install_Extend::queryString("SELECT VERSION();");
		$arrRow=mysql_fetch_array($sRs);
		$arrMysqlVersions=explode('.',trim($arrRow[0]));
		$nMysqlVersion=$arrMysqlVersions[0].".".$arrMysqlVersions[1];

		Install_Extend::queryString("SET NAMES 'UTF8',character_set_client=binary,sql_mode='';");

		// 系统初始化文件
		$sLangCookieName=$GLOBALS['_commonConfig_']['COOKIE_LANG_TEMPLATE_INCLUDE_APPNAME']===true?APP_NAME.'_language':'language';
		$sLangcurrent=strtolower(Q::cookie($sLangCookieName));
		$sWindsForceDatadir=APP_PATH.'/Static/Sql/Install';

		$sWindsForceDatapath=$sWindsForceDatadir.'/'.ucfirst(Q::cookie($sLangCookieName)).'/windsforce.data.sql';
		if(!is_file($sWindsForceDatapath)){
			$sWindsForceDatapath=$sWindsForceDatadir.'/Zh-cn/windsforce.data.sql';
			$sLangcurrent='zh-cn';
		}

		// 写入配置文件
		$arrConfig=(array)(include WINDSFORCE_PATH.'/System/common/ConfigDefault.inc.php');
		$arrConfig['DB_HOST']=$sDbhost;
		$arrConfig['DB_USER']=$sDbuser;
		$arrConfig['DB_PASSWORD']=$sDbpwd;
		$arrConfig['DB_NAME']=$sDbname;
		$arrConfig['DB_PREFIX']=$sDbprefix;
		$arrConfig['RBAC_DATA_PREFIX']=$sRbacprefix;
		$arrConfig['COOKIE_PREFIX']=$sCookieprefix;

		if($sLangcurrent!='zh-cn'){
			$arrConfig['FRONT_LANGUAGE_DIR']=ucfirst($sLangcurrent);
			$arrConfig['ADMIN_LANGUAGE_DIR']=ucfirst($sLangcurrent);
		}

		// 安全配置
		$arrConfig['USER_AUTH_KEY']='authid'.C::randString(6);
		$arrConfig['ADMIN_AUTH_KEY']='admin'.C::randString(6);
		$arrConfig['QEEPHP_AUTH_KEY']='qeephpauthkey'.C::randString(6);
		
		if(!file_put_contents(WINDSFORCE_PATH.'/~@~/Config.inc.php',
			"<?php\n /* QeePHP Config File,Do not to modify this file! */ \n return ".
			var_export($arrConfig,true).
			"\n?>")
		){
			$this->E(Q::L('写入配置失败，请检查 %s目录是否可写入','Controller/Install',null,WINDSFORCE_PATH.'/Config'));
		}

		// 输出消息框
		$this->display('install+message');

		// 防止乱码
		$sSql4Tmp='';
		if($nMysqlVersion>=4.1){
			$sSql4Tmp="ENGINE=MyISAM DEFAULT CHARSET=UTF8";
		}
		
		// 创建系统表
		Install_Extend::showJavascriptMessage('<h3>'.Q::L('创建系统数据库表','Controller/Install').'</h3>');
		Install_Extend::importTable(APP_PATH.'/Static/Sql/Install/windsforce.table.sql');
		Install_Extend::showJavascriptMessage(' ');

		// 执行系统初始化数据
		Install_Extend::showJavascriptMessage('<h3>'.Q::L('初始化系统数据库数据','Controller/Install').'</h3>');
		Install_Extend::runQuery($sWindsForceDatapath);
		Install_Extend::showJavascriptMessage(' ');

		// 导入地理数据
		Install_Extend::showJavascriptMessage('<h3>'.Q::L('导入地理数据库数据','Controller/Install').'</h3>');
		for($nI=1;$nI<=6;$nI++){
			Install_Extend::showJavascriptMessage(Q::L('导入地理数据库数据','Controller/Install').$nI);
			Install_Extend::runQuery($sWindsForceDatadir.'/district/'.$nI.'.sql',false);
		}
		Install_Extend::showJavascriptMessage(' ');

		// 安装系统预置应用
		Install_Extend::showJavascriptMessage('<h3>'.Q::L('安装系统预置应用','Controller/Install').'</h3>');
		
		$arrApps=Q::G('app','P');
		if(empty($arrApps)){
			Install_Extend::showJavascriptMessage(Q::L('没有发现需要安装的应用','Controller/Install'));
			Install_Extend::showJavascriptMessage(' ');
		}else{
			foreach($arrApps as $sApp){
				Install_Extend::showJavascriptMessage(Q::L('创建应用 %s 的数据库表','Controller/Install',null,$sApp));
				Install_Extend::importTable($sWindsForceDatadir.'/app/'.$sApp.'/windsforce.table.sql');
				Install_Extend::showJavascriptMessage(' ');

				$sWindsForceAppDatapath=$sWindsForceDatadir.'/'.ucfirst(Q::cookie($sLangCookieName)).'/App/'.$sApp.'/windsforce.data.sql';
				if(!is_file($sWindsForceAppDatapath)){
					$sWindsForceAppDatapath=$sWindsForceDatadir.'/Zh-cn/App/'.$sApp.'/windsforce.data.sql';
				}
				Install_Extend::showJavascriptMessage(Q::L('导入应用 %s 的数据库数据','Controller/Install',null,$sApp));
				Install_Extend::runQuery($sWindsForceAppDatapath);
				Install_Extend::showJavascriptMessage(' ');
			}
		}

		// 初始化安装程序设置
		Install_Extend::showJavascriptMessage('<h3>'.Q::L('初始化安装程序设置','Controller/Install').'</h3>');
		
		Install_Extend::queryString("Update `{$sDbprefix}option` set option_value='".trim(Q::G('baseurl'))."' where option_name='site_url';");
		Install_Extend::showJavascriptMessage(Q::L('写入社区地址','Controller/Install').' '.trim(Q::G('baseurl')).' ... '.Q::L('成功','Controller/Common'));

		Install_Extend::queryString("Update `{$sDbprefix}option` set option_value='".trim(Q::G('webname'))."' where option_name='site_name';");
		Install_Extend::showJavascriptMessage(Q::L('写入社区名称','Controller/Install').' '.trim(Q::G('webname')).' ... '.Q::L('成功','Controller/Common'));

		Install_Extend::queryString("Update `{$sDbprefix}option` set option_value='".trim(Q::G('adminmail'))."' where option_name='admin_email';");
		Install_Extend::showJavascriptMessage(Q::L('写入管理员邮件','Controller/Install').' '.trim(Q::G('adminmail')).' ... '.Q::L('成功','Controller/Common'));
		Install_Extend::showJavascriptMessage(' ');

		// 语言包
		if($sLangcurrent!='zh-cn'){
			Install_Extend::showJavascriptMessage('<h3>Init language...</h3>');
			Install_Extend::queryString("Update `{$sDbprefix}option` set option_value='".$sLangcurrent."' where option_name='admin_language_name';");
			Install_Extend::queryString("Update `{$sDbprefix}option` set option_value='".$sLangcurrent."' where option_name='front_language_name';");
		}

		// 初始化管理员信息
		Install_Extend::showJavascriptMessage('<h3>'.Q::L('初始化管理员信息','Controller/Install').'</h3>');
		
		$sRandom=C::randString(6);
		$sPassword=md5(md5($sAdminpwd).trim($sRandom));
		Install_Extend::queryString("Update `{$sDbprefix}user` set user_name='".$sAdminuser."',user_password='".$sPassword."',user_random='".$sRandom."',user_password='".$sPassword."',user_registerip='".C::getIp()."',user_email='".trim(Q::G('adminmail'))."',user_lastloginip='".C::getIp()."',create_dateline='".CURRENT_TIMESTAMP."' where user_id=1;");
		Install_Extend::showJavascriptMessage(Q::L('初始化超级管理员帐号','Controller/Install').'... '.Q::L('成功','Controller/Common'));
		Install_Extend::showJavascriptMessage(' ');

		// 写入锁定文件
		if(!file_put_contents($this->_sLockfile,'ok')){
			$this->E(Q::L('写入安装锁定文件失败，请检查%s目录是否可写入','Controller/Install',null,WINDSFORCE_PATH.'/data'));
		}
		Install_Extend::showJavascriptMessage(Q::L('写入安装程序锁定文件','Controller/Install').'... '.Q::L('成功','Controller/Common'));
		Install_Extend::showJavascriptMessage(' ');

		// 执行清理
		Install_Extend::showJavascriptMessage('<h3>'.Q::L('清理系统缓存目录','Controller/Install').'</h3>');
		foreach(array('app','data','field','style_') as $sCacheDir){
			if(is_dir(WINDSFORCE_PATH.'/~@~/'.$sCacheDir)){
				Install_Extend::removeDir(WINDSFORCE_PATH.'/~@~/'.$sCacheDir);
			}
		}

		// 初始化系统和跳转
		$sInitsystemUrl=trim(Q::G('baseurl')).'/index.php?app=home&c=misc&a=init_system&?l='.$sLangcurrent;

		// 将安装数据传回官方服务器以便于统计用户
		$sIp=C::getIp();
		$sDomain=$_SERVER['HTTP_HOST'];
		$sServUrl='http://qeephp.114.ms/index.php?app=service&c=install&a=index&ip='.urlencode($sIp).'&domain='.urlencode($sDomain).'&version='.urlencode(WINDSFORCE_SERVER_VERSION).'&release='.urlencode(WINDSFORCE_SERVER_RELEASE).'&bug='.urlencode(WINDSFORCE_SERVER_BUG).'&update=0';

		echo<<<WINDSFORCE
		<script type="text/javascript">
			function setLaststep(){
				setTimeout(function(){
					$WF("laststep").disabled=false;
					window.location=Q.U('index/success');
				},1000);
			}
		</script>
		<script type="text/javascript" src="{$sServUrl}"></script>
		<script type="text/javascript">setTimeout(function(){window.location=window.location=Q.U('index/success');},20000);
		</script>
		<iframe src="{$sInitsystemUrl}" style="display:none;" onload="setLaststep()"></iframe>
WINDSFORCE;

		exit();
	}

	public function success(){
		if(!empty($_SERVER['HTTP_HOST'])){
			$sBaseurl='http://'.$_SERVER['HTTP_HOST'];
		}else{
			$sBaseurl="http://".$_SERVER['SERVER_NAME'];
		}

		$this->assign('sBaseurl',$sBaseurl);
		$this->display('install+success');
	}

	public function check_database(){
		$this->check_install();

		header("Pragma:no-cache\r\n");
		header("Cache-Control:no-cache\r\n");
		header("Expires:0\r\n");

		$sDbhost=Q::G('dbhost');
		$sDbuser=Q::G('dbuser');
		$sDbpwd=Q::G('dbpwd');
		$sDbname=Q::G('dbname');

		try{
			$hConn=@mysql_connect($sDbhost,$sDbuser,$sDbpwd);
			if($hConn){
				if(empty($sDbname)){
					$this->S("<font color='green'>".Q::L('数据库连接成功','Controller/Install')."</font>",0);
				}else{
					if(mysql_select_db($sDbname,$hConn)){
						$this->E("<font color='red'>".Q::L('数据库已经存在,系统将覆盖数据库','Controller/Install')."</font>",0);
					}else{
						$this->S("<font color='green'>".Q::L('数据库不存在,系统将自动创建','Controller/Install')."</font>",0);
					}
				}
			}else{
				$this->E("<font color='red'>".Q::L('数据库连接失败','Controller/Install')."</font>",0);
			}

			@mysql_close($hConn);
		}catch(Exception $e){
			$this->E("<font color='red'>".$e->getMessage()."</font>",0);
			exit();
		}

		exit();
	}

}
