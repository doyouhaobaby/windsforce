<?php
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   全局控制器($$)*/

!defined('Q_PATH') && exit;

class App{

	static private function init_(){
		// 移除自动转义
		C::stripslashesMagicquotegpc();
		if(isset($_REQUEST['GLOBALS']) OR isset($_FILES['GLOBALS'])){
			E('GLOBALS not allowed!');
		}

		// 载入QeePHP框架
		Q::import(Q_PATH);
		// 载入应用
		Q::import(APP_PATH.'/App/Class');

		// 初始化时区和GZIP压缩
		if(function_exists('date_default_timezone_set')){
			date_default_timezone_set($GLOBALS['_commonConfig_']['TIME_ZONE']);
		}
		if($GLOBALS['_commonConfig_']['START_GZIP'] && function_exists('gz_handler')){
			ob_start('gz_handler');
		}else{
			ob_start();
		}

		// 解析系统URL
		$oUrl=new Url();
		$oUrl->parseUrl();

		// 检查语言包和模板以及定义系统常量
		self::checkTemplate();
		self::checkLanguage();
 		self::constantDefine();

		// 载入项目初始化文件
		if(!defined('APP_INIT_PATH')){
			define('APP_INIT_PATH',APP_PATH.'/App/QeePHP.php');
		}
		if(file_exists(APP_INIT_PATH)){
			require(APP_INIT_PATH);
		}
		return;
	}

	static public function RUN(){
		self::init_();
		self::execute();
		if($GLOBALS['_commonConfig_']['LOG_RECORD']){
			Log::S();
		}

		return;
	}

	static public function execute($sModule=MODULE_NAME,$sAction=ACTION_NAME){
		// 读取模块资源
		$sModule=ucfirst($sModule)."Controller";
		if(Q::classExists($sModule,false,true)){
			$oModule=new $sModule();
		}else{
			Q::E(Q::L('模块%s 不存在','__QEEPHP__@Q',null,$sModule));
		}

		// 执行控制器公用初始化函数
		if(method_exists($oModule,'init__')){
			call_user_func(array($oModule,'init__'));
		}

		// 执行控制器方法
		if(method_exists($oModule,'b'.ucfirst($sAction).'_')){
			call_user_func(array($oModule,'b'.ucfirst($sAction).'_'));
		}

		if(method_exists($oModule,$sAction)){
			call_user_func(array($oModule,$sAction));
		}else{
			Q::E(Q::L('模块%s 不存在的方法%s 不存在','__QEEPHP__@Q',null,$sModule,$sAction));
		}

		if(method_exists($oModule,'a'.ucfirst($sAction).'_')){
			call_user_func(array($oModule,'a'.ucwords($sAction).'_'));
		}
	}

	static private function checkTemplate(){
		if(!defined('APP_TEMPLATE_PATH')){
			define('APP_TEMPLATE_PATH',APP_PATH.'/Theme');
		}

		if($GLOBALS['_commonConfig_']['COOKIE_LANG_TEMPLATE_INCLUDE_APPNAME']===TRUE){
			$sCookieName=APP_NAME.'_template';
		}else{
			$sCookieName='template';
		}

		if(!$GLOBALS['_commonConfig_']['THEME_SWITCH']){
			$sTemplateSet=ucfirst(strtolower($GLOBALS['_commonConfig_']['TPL_DIR']));
		}elseif(isset($_GET['t'])){
			$sTemplateSet=ucfirst(strtolower($_GET['t']));
		}else{
			if(Q::cookie($sCookieName)){
				$sTemplateSet=Q::cookie($sCookieName);
			}else{
				$sTemplateSet=ucfirst(strtolower($GLOBALS['_commonConfig_']['TPL_DIR']));
			}
		}

		Q::cookie($sCookieName,$sTemplateSet);

		define('TEMPLATE_NAME',$sTemplateSet);
		define('TEMPLATE_PATH',APP_TEMPLATE_PATH.'/'.TEMPLATE_NAME);
		define('TEMPLATE_PATH_DEFAULT',APP_TEMPLATE_PATH.'/Default');

		if(!is_dir(TEMPLATE_PATH)){
			$sTemplatePath=APP_TEMPLATE_PATH.'/Default';
		}else{
			$sTemplatePath=TEMPLATE_PATH;
		}
		Template::setTemplateDir($sTemplatePath);

		return;
	}

	static private function checkLanguage(){
		if(!defined('APP_LANG_PATH')){
			define('APP_LANG_PATH',APP_PATH.'/App/Lang');
		}

		if($GLOBALS['_commonConfig_']['COOKIE_LANG_TEMPLATE_INCLUDE_APPNAME']===TRUE){
			$sCookieName=APP_NAME.'_language';
		}else{
			$sCookieName='language';
		}

		if(!$GLOBALS['_commonConfig_']['LANG_SWITCH']){
			$sLangSet=ucfirst(strtolower($GLOBALS['_commonConfig_']['LANG']));
		}elseif(isset($_GET['l'])){
			$sLangSet=ucfirst(strtolower($_GET['l']));
		}elseif($sCookieName){
			$sLangSet=Q::cookie($sCookieName);
			if(empty($sLangSet)){
				$sLangSet=ucfirst(strtolower($GLOBALS['_commonConfig_']['LANG']));
			}
		}elseif($GLOBALS['_commonConfig_']['AUTO_ACCEPT_LANGUAGE'] && isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
			preg_match('/^([a-z\-]+)/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'],$arrMatches);
			$sLangSet=ucfirst(strtolower($arrMatches[1]));
		}else{
			$sLangSet=ucfirst(strtolower($GLOBALS['_commonConfig_']['LANG']));
		}

		Q::cookie($sCookieName,$sLangSet);

		define('LANG_NAME',$sLangSet);
		Lang::setCurrentLang($sLangSet);
		define('LANG_PATH',APP_LANG_PATH.'/'.LANG_NAME);
		define('LANG_PATH_DEFAULT',APP_LANG_PATH.'/'.ucfirst(strtolower($GLOBALS['_commonConfig_']['LANG'])));

		return;
	}

	static private function constantDefine(){
		define('__ENTER__',basename(__APP__));

		// 项目入口公用静态资源目录(也叫做公共目录)
		if(defined('__STATICS__')){
			define('__APPPUB__',__ROOT__.'/'.__STATICS__);
		}else{
			define('__APPPUB__',__ROOT__.'/'.APP_NAME.'/Static');
		}

		// 模板目录
		if(defined('__THEMES__')){
			define('__THEME__',__ROOT__.'/'.__THEMES__);
		}else{
			define('__THEME__',__ROOT__.'/'.APP_NAME.'/Theme');
		}

		// 项目资源目录
		define('__TMPL__',__THEME__.'/'.TEMPLATE_NAME);
		define('__TMPL_DEFAULT__',__THEME__.'/Default');

		// 网站公共文件目录
		if(defined('__PUBLICS__')){
			define('__PUBLIC__',__ROOT__.'/'.__PUBLICS__);
		}else{
			define('__PUBLIC__',__ROOT__.'/Public');
		}

		// 项目公共文件目录
		define('__TMPLPUB__',__TMPL__.'/Public');
		define('__TMPLPUB_DEFAULT__',__TMPL_DEFAULT__.'/Public');

		// 框架一个特殊的模块定义
		define('MODULE_NAME2',$GLOBALS['_commonConfig_']['TMPL_MODULE_ACTION_DEPR']=='/' && MODULE_NAME==='public'?'Public':MODULE_NAME);

		// 当前文件路径
		define('__TMPL_FILE_NAME__',__TMPL__.'/'.MODULE_NAME2.$GLOBALS['_commonConfig_']['TMPL_MODULE_ACTION_DEPR'].
			ACTION_NAME.$GLOBALS['_commonConfig_']['TEMPLATE_SUFFIX']
		);
		define('__TMPL_FILE_PATH__',TEMPLATE_PATH.'/'.MODULE_NAME2.$GLOBALS['_commonConfig_']['TMPL_MODULE_ACTION_DEPR'].
			ACTION_NAME.$GLOBALS['_commonConfig_']['TEMPLATE_SUFFIX']
		);
	}

	static public function U(){
		return "var _ROOT_='".__ROOT__."',_SELF_='".__SELF__."',_MODULE_NAME_='".MODULE_NAME."',_ACTION_NAME_='".ACTION_NAME."',_APP_NAME_ ='".APP_NAME."',_ENTER_ ='".__ENTER__.
			"',_APP_VAR_NAME_='app',_CONTROL_VAR_NAME_='c',_ACTION_VAR_NAME_='a',_URL_HTML_SUFFIX_='".
			$GLOBALS['_commonConfig_']['URL_HTML_SUFFIX']."',_LANG_NAME_='".LANG_NAME."';";
	}

}
