<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   Public控制器($$)*/

!defined('Q_PATH') && exit;

/** 导入Home模型 */
Q::import(WINDSFORCE_PATH.'/System/app/home/App/Class/Model');

/** 定义Home的语言包 */
define('__APPHOME_COMMON_LANG__',WINDSFORCE_PATH.'/System/app/home/App/Lang/Admin');

// 导入社会化登录组件
Q::import(WINDSFORCE_PATH.'/System/extension/socialization');

class PublicController extends AController{

	public function fmain(){
		$this->isLogin_();

		// 系统统计信息
		Core_Extend::loadCache('site');
		$arrStaticInfo=array(
			array(Q::L('用户数量','Controller'),$GLOBALS['_cache_']['site']['user'],Core_Extend::windsforceOuter('app=home&c=stat&a=userlist')),
			array(Q::L('新注册用户数量','Controller'),$GLOBALS['_option_']['todayusernum'],Core_Extend::windsforceOuter('app=home&c=stat&a=newuser')),
			array(Q::L('应用数量','Controller'),$GLOBALS['_cache_']['site']['app'],Core_Extend::windsforceOuter('app=home&c=apps&a=index')),
			array(Q::L('新鲜事数量','Controller'),$GLOBALS['_cache_']['site']['homefresh'],Core_Extend::windsforceOuter('app=home&c=stat&a=explore')),
		);
		$this->assign('arrStaticInfo',$arrStaticInfo);

		// 服务器信息监测
		$oDb=Db::RUN();
		$arrInfo=array(
			Q::L('操作系统','Controller')=>PHP_OS,
			Q::L('运行环境','Controller')=>$_SERVER["SERVER_SOFTWARE"],
			Q::L('PHP运行方式','Controller')=>php_sapi_name(),
			Q::L('数据库类型','Controller')=>$GLOBALS['_commonConfig_']['DB_TYPE'],
			Q::L('数据库版本','Controller')=>$oDb->getConnect()->getVersion(),
			Q::L('上传附件限制','Controller')=>ini_get('upload_max_filesize'),
			Q::L('执行时间限制','Controller')=>ini_get('max_execution_time').' Secconds',
			Q::L('服务器时间','Controller')=>date('Y-n-j H:i:s'),
			Q::L('北京时间','Controller')=>gmdate('Y-n-j H:i:s',time()+8*3600),
			Q::L('服务器域名/IP','Controller')=>$_SERVER['SERVER_NAME'].' [ '.gethostbyname($_SERVER['SERVER_NAME']).' ]',
			Q::L('剩余空间','Controller')=>round((@disk_free_space(".")/(1024*1024)),2).'M',
			'register_globals'=>get_cfg_var("register_globals")=="1"?"ON":"OFF",
			'magic_quotes_gpc'=>(1===get_magic_quotes_gpc())?'YES':'NO',
			'magic_quotes_runtime'=>(1===get_magic_quotes_runtime())?'YES':'NO',
		);
		$this->assign('arrInfo',$arrInfo);

		// 系统文件权限检查
		$arrTestDirs=(array)(include WINDSFORCE_PATH.'/System/common/Cache.php');
		$this->assign('arrTestDirs',$arrTestDirs);

		// 程序信息
		$arrVersionInfo=array(
			'WindsForce '.Q::L('程序版本','Controller')=>"WindsForce " .WINDSFORCE_SERVER_VERSION. "  Release ".
			WINDSFORCE_SERVER_RELEASE.(WINDSFORCE_SERVER_BUG?' Bug-'.WINDSFORCE_SERVER_BUG:'')." [ <a href=\"http://windsforce.114.ms\" target=\"_blank\">".
			Q::L('查看最新版本','Controller')."</a>  <span id=\"newest_version\">".Q::L('读取中','Controller')."...</span> ]&nbsp;"."<a href=\"http://windsforce.114.ms\" target=\"_blank\">".
			Q::L('专业支持与服务','Controller')."</a>",
			'QeePHP'.Q::L('版本','Controller')=>QEEPHP_VERSION.
			' [ <a href="http://qeephp.114.ms" target="_blank">'.Q::L('查看最新版本','Controller').'</a> <span id="newest_frameworkversion">'.Q::L('读取中','Controller').'...</span> ] &nbsp;'.
			Q::L('QeePHP 是一款性能卓越的PHP 开发框架','Controller').' <img src="'.__ROOT__.'/System/include/QeePHP/Q-powered.png" />',
		);
		$this->assign('arrVersionInfo',$arrVersionInfo);

		// 版权信息
		if(is_file(WINDSFORCE_PATH."/user/language/".LANG_NAME."/LICENSE.txt")){
			$sCopyTxt=nl2br(file_get_contents(WINDSFORCE_PATH."/user/language/".LANG_NAME."/LICENSE.txt"));
		}else{
			$sCopyTxt=nl2br(file_get_contents(WINDSFORCE_PATH."/user/language/Zh-cn/LICENSE.txt"));
		}
		$this->assign('sCopyTxt',$sCopyTxt);

		// 提示消息
		$arrTipsTxt=array();
		if(is_file(APP_PATH."/App/Lang/".LANG_NAME."/Tips.txt")){
			$tipsTxt=nl2br(file_get_contents(APP_PATH."/App/Lang/".LANG_NAME."/Tips.txt"));
		}else{
			$tipsTxt='Hello World!';
		}

		$tipsTxt=explode("\r\n",$tipsTxt);
		foreach($tipsTxt as $sValue){
			if(strlen($sValue)==6 || strpos($sValue,'###')===0){
				continue;
			}
			$nValuePos=strpos($sValue,',');
			if($nValuePos<=4){
				$sValue=C::subString($sValue,$nValuePos);
				$sValue=C::subString($sValue,0,-6);
				$sValue=trim($sValue,',');
			}
			$arrTipsTxt[]=$sValue;
		}

		$nTips=mt_rand(0,count($arrTipsTxt)-1);
		$tipsTxt=$arrTipsTxt[$nTips];
		$this->assign('sTipsTxt',$tipsTxt);
		
		// 升级服务端信息
		$sUpdateUrl='http://www.114.ms/index.php?app=service&c=update&a=index&version='.urlencode(WINDSFORCE_SERVER_VERSION).
			'&release='.urlencode(WINDSFORCE_SERVER_RELEASE).'&bug='.urlencode(WINDSFORCE_SERVER_BUG).'&hostname='.
			urlencode($_SERVER['HTTP_HOST']).'&url='.urlencode($GLOBALS['_option_']['site_name']);
		$this->assign('sUpdateUrl',$sUpdateUrl);

		// 后台在线用户
		$arrUserids=array();
		$sUid=Q::C('ADMIN_USERID');

		$arrOnlines=Model::F_('online')->where(array('user_id'=>array('in',$sUid)))->setColumns('user_id')->getAll();
		if(!empty($arrOnlines)){
			foreach($arrOnlines as $arrOnline){
				$arrUserids[]=$arrOnline['user_id'];
			}
		}
		$arrUsers=UserModel::F()->where(array('user_id'=>array('in',$arrUserids?$arrUserids:'0')))->order('user_id DESC')->getAll();
		$this->assign('arrUsers',$arrUsers);

		// 待处理事情
		$arrTodos=array();
		$nTotalAppeal=AppealModel::F()->where(array('appeal_progress'))->all()->getCounts();

		$arrTodos=array(
			array($nTotalAppeal,Q::L('待申诉用户','Controller'),Q::U('appeal/index?type=0')),
		);
		$this->assign('arrTodos',$arrTodos);
		$this->display();
	}

	public function login(){
		$sReferer=trim(Q::G('referer'));
		$nRbac=intval(Q::G('rbac','G'));

		if($GLOBALS['___login___']!==false){
			$this->U('index/index');
		}

		$this->assign('sReferer',$sReferer);
		$this->assign('nRbac',$nRbac);
		$this->display();
	}

	public function fheader(){
		$this->isLogin_();

		$arrMenuList=Model::F_('nodegroup','nodegroup_status=?',1)
			->order('`nodegroup_sort` ASC')
			->setColumns('nodegroup_id,nodegroup_title')
			->getAll();

		$this->assign('sUserName',$GLOBALS['___login___']['user_name']);
		$this->assign('arrListMenu',$arrMenuList);
		$this->display();
	}

	public function index($sModel=null,$bDisplay=true){
		$this->isLogin_();
		
		// 后台页面跳转计算
		unset($_GET['c']);
		unset($_GET['a']);

		// fheader跳转地址
		if(isset($_GET['fheader'])){
			$nFheader=intval($_GET['fheader']);
			unset($_GET['fheader']);
			$this->assign('nFheader',$nFheader);
		}

		// fmenu跳转地址
		if(isset($_GET['fmenu'])){
			$nFmenu=intval($_GET['fmenu']);
			unset($_GET['fmenu']);

			if(isset($_GET['fmenucurid'])){
				$nCurrentid=intval($_GET['fmenucurid']);
				unset($_GET['fmenucurid']);
			}else{
				$nCurrentid=0;
			}

			if(isset($_GET['fmenutitle'])){
				$sFmenutitle=trim($_GET['fmenutitle']);
				unset($_GET['fmenutitle']);
			}else{
				$sFmenutitle=Q::L("应用",'Template');
			}

			$this->assign('nCurrentid',$nCurrentid);
			$this->assign('nFmenu',$nFmenu);
			$this->assign('sFmenutitle',$sFmenutitle);
		}

		// fmain跳转地址
		$sFmainController=trim(Q::G('fmainc','G'));
		$sFmainAction=trim(Q::G('fmaina','G'));

		if(!empty($sFmainController)){
			if(empty($sFmainAction)){
				$sFmainAction='index';
			}
			if(isset($_GET['fmainc'])){
				unset($_GET['fmainc']);
			}
			if(isset($_GET['fmaina'])){
				unset($_GET['fmaina']);
			}

			$sFmainUrl=Q::U($sFmainController.'/'.$sFmainAction,$_GET);
			$this->assign('sFmainUrl',$sFmainUrl);
		}

		$this->display('index+index');
	}

	public function check_login(){
		$this->check_seccode(true);
		$sUserName=trim(Q::G('user_name','P'));
		$sPassword=trim(Q::G('user_password','P'));

		if(empty($sUserName)){
			$this->E(Q::L('帐号或者E-mail不能为空','Controller'));
		}elseif(empty($sPassword)){
			$this->E(Q::L('密码不能为空','Controller'));
		}

		Check::RUN();
		if(Check::C($sUserName,'email')){
			$bEmail=true;
			unset($_POST['user_name']);
		}else{
			$bEmail=false;
		}

		$oUserModel=Q::instance('UserModel');
		$oUserModel->checkLoginCommon($sUserName,$sPassword,$bEmail);
		if($oUserModel->isError()){
			$this->E($oUserModel->getErrorMessage());
		}else{
			$sUrl=Q::U('index/index');
			$this->A(array('url'=>$sUrl),Q::L('Hello %s,你成功登录','Controller',null,$sUserName),1);
		}
	}

	public function password(){
		$this->isLogin_();

		$arrUserData=$GLOBALS['___login___'];
		$this->assign('nUserId',$arrUserData['user_id']);
		$this->display();
	}

	public function change_pass(){
		$this->isLogin_();
		$this->check_seccode(true);

		$sPassword=trim(Q::G('user_password','P'));
		$sNewPassword=trim(Q::G('new_password','P'));
		$sOldPassword=trim(Q::G('old_password','P'));

		$oUserModel=Q::instance('UserModel');
		$oUserModel->changePassword($sPassword,$sNewPassword,$sOldPassword);
		if($oUserModel->isError()){
			$this->E($oUserModel->getErrorMessage());
		}else{
			$this->S(Q::L('密码修改成功，你需要重新登录','Controller'));
		}
	}

	public function information(){
		$this->isLogin_();

		$arrUserInfo=Model::F_('user','user_id=?',$GLOBALS['___login___']['user_id'])->getOne();
		$this->assign('arrUserInfo',$arrUserInfo);
		$this->display();
	}

	public function change_info(){
		$this->isLogin_();
		$this->check_seccode(true);

		$nUserId=intval(Q::G('user_id','P'));
		$oUser=UserModel::F('user_id=?',$nUserId)->query();
		$oUser->save('update');
		if($oUser->isError()){
			$this->E($oUser->getErrorMessage());
		}else{
			$this->S(Q::L('修改用户资料成功','Controller'));
		}
	}

	public function check_email(){
		$sUserEmail=trim(Q::G('user_email'));
		if(!$sUserEmail){
			exit('false');
		}

		// 查询条件
		$arrWhere=array();
		$arrWhere['user_email']=$sUserEmail;
		$arrWhere['user_id']=array('neq',$GLOBALS['___login___']['user_id']);

		$oUser=UserModel::F()->where($arrWhere)->setColumns('user_id')->getOne();
		if(empty($oUser['user_id'])){
			exit('true');
		}else{
			exit('false');
		}
	}

	public function logout(){
		if(!empty($GLOBALS['___login___'])){
			if(!Q::classExists('Auth')){
				require_once(Core_Extend::includeFile('class/Auth'));
			}

			Auth::loginOut();
			Socia::clearCookie();

			$this->assign("__JumpUrl__",Q::U('public/login'));
			$this->S(Q::L('登出成功','Controller'));
		}else{
			$this->E(Q::L('已经登出','Controller'));
		}
	}

	public function fmenu(){
		$this->isLogin_();

		$sTag=Q::G('tag');
		if($sTag===null){
			$sTag='';
			Core_Extend::loadCache('adminctrlmenu');
			$this->assign('arrAdminctrlmenus',$GLOBALS['_cache_']['adminctrlmenu']);
		}

		// 菜单列表
		$arrMenuList=array();
		$arrWhere['node_level']=2;
		$arrWhere['node_status']=1;
		$arrWhere['nodegroup_id']=$sTag;
		$arrMenuList=Model::F_('node')
			->setColumns('node_id,node_name,nodegroup_id,node_title')
			->order('`node_sort` ASC')
			->where($arrWhere)
			->getAll();

		$arrAccessList=Rbac::getUserRbac($GLOBALS['___login___']['user_id']);

		foreach($arrMenuList as $sKey=>$arrModule){
			if(!empty($GLOBALS['___login___']['is_admin']) OR (is_array($arrAccessList) && isset($arrAccessList[strtolower(APP_NAME)][strtolower($arrModule['node_name'])]))){
				$arrModule['node_access']=1;
				$arrMenuList[$sKey]=$arrModule;
			}
		}

		$this->assign('sMenuTag',$sTag);
		$this->assign('arrMenuList',$arrMenuList);
		$this->display();
	}

	public function program_update(){
		$sUpdateUrl='http://www.114.ms/index.php?app=service&c=update&a=index&version='.urlencode(WINDSFORCE_SERVER_VERSION).
			'&release='.urlencode(WINDSFORCE_SERVER_RELEASE).'&bug='.urlencode(WINDSFORCE_SERVER_BUG).'&hostname='.
			urlencode($_SERVER['HTTP_HOST']).'&url='.urlencode($GLOBALS['_option_']['site_name']).'&infolist=1';
		$this->assign('sUpdateUrl',$sUpdateUrl);

		$arrOptionData=$GLOBALS['_option_'];
		$this->assign('arrOptions',$arrOptionData);
		$this->display();
	}
	
	public function programeupdate_option(){
		$oOptionController=new OptionController();
		$oOptionController->update_option();
	}

	public function close_updateinfo(){
		$oOptionModel=OptionModel::F('option_name=?','programeupdate_on')->getOne();
		$oOptionModel->option_value=0;
		$oOptionModel->save('update');
		if($oOptionModel->isError()){
			$this->E($oOptionModel->getErrorMessage());
		}

		if(!Q::classExists('Cache_Extend')){
			require_once(Core_Extend::includeFile('function/Cache_Extend'));
		}
		Cache_Extend::updateCache('option');

		$this->S(Q::L('升级提醒信息成功关闭','Controller'));
	}

	public function profile(){
		$arrUserData=$GLOBALS['___login___'];
		$sUserName=isset($arrUserData['user_nikename']) && $arrUserData['user_nikename']?$arrUserData['user_nikename']:$arrUserData['user_name'];
		$this->assign('sUserName',$sUserName);
		$this->display();
	}
	
	public function validate_seccode(){
		$sSeccode=trim(Q::G('seccode'));
		if(empty($sSeccode)){
			exit('false');
		}
		
		$bResult=Core_Extend::checkSeccode($sSeccode);
		if(!$bResult){
			exit('false');
		}
		
		exit('true');
	}

	protected function isLogin_(){
		if($GLOBALS['___login___']===false){
			$this->assign('__JumpUrl__',Q::U('public/login'));
			$this->E(Q::L('你没有登录','Controller'));
		}
	}

}
