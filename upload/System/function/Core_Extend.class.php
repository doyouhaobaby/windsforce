<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   系统核心函数文件($$)*/

!defined('Q_PATH') && exit;

class Core_Extend{

	static public function loginInformation(){
		$sAuthData=Q::cookie($GLOBALS['_commonConfig_']['RBAC_DATA_PREFIX'].'auth');
		list($nUserId,$sIsAdmin,$sPassword)=$sAuthData?explode("\t",C::authCode($sAuthData)):array('','','');

		// 用户信息容器
		$arrUser=false;
		$bIsLogin=false;
		if($nUserId && $sPassword){
			$arrUser=Model::F_('user','user_id=? AND user_password=? AND user_status=1',$nUserId,$sPassword)->setColumns('user_id,user_name,user_nikename,user_email,user_sign,user_extendstyle,user_lastlogintime')->asArray()->getOne();
			if(!empty($arrUser['user_id'])){
				$arrUser['is_admin']=$sIsAdmin=='y'?true:false;
			}
		}else{
			Q::cookie($GLOBALS['_commonConfig_']['RBAC_DATA_PREFIX'].'auth',null,-1);
		}

		$GLOBALS['___login___']=$arrUser;
		return $arrUser;
	}

	static public function isAdmin(){
		if($GLOBALS['___login___']===false){
			return false;
		}
		if($GLOBALS['___login___']['is_admin']){
			return true;
		}else{
			return false;
		}
	}

	static public function page404($oThis){
		$arrAllMethod=get_class_methods($oThis);
		if(!in_array(ACTION_NAME,$arrAllMethod)){
			$oThis->page404();
		}
	}

	static public function seccode(){
		$arrOption=array(
			'seccode_image_width_size'=>$GLOBALS['_option_']['seccode_image_width_size'],
			'seccode_image_height_size'=>$GLOBALS['_option_']['seccode_image_height_size'],
			'seccode_adulterate'=>$GLOBALS['_option_']['seccode_adulterate'],
			'seccode_ttf'=>$GLOBALS['_option_']['seccode_ttf'],
			'seccode_tilt'=>$GLOBALS['_option_']['seccode_tilt'],
			'seccode_background'=>$GLOBALS['_option_']['seccode_background'],
			'seccode_image_background'=>$GLOBALS['_option_']['seccode_image_background'],
			'seccode_color'=>$GLOBALS['_option_']['seccode_color'],
			'seccode_size'=>$GLOBALS['_option_']['seccode_size'],
			'seccode_shadow'=>$GLOBALS['_option_']['seccode_shadow'],
			'seccode_animator'=>$GLOBALS['_option_']['seccode_animator'],
			'seccode_norise'=>$GLOBALS['_option_']['seccode_norise'],
			'seccode_curve'=>$GLOBALS['_option_']['seccode_curve'],
		);

		if($GLOBALS['_option_']['seccode_type']==3){
			$arrOption['seccode_bitmap']=1;
		}elseif($GLOBALS['_option_']['seccode_type']==2){
			$arrOption['seccode_chinesecode']=1;
		}
		
		C::seccode($arrOption,($GLOBALS['_option_']['seccode_type']==2?1:0),($GLOBALS['_option_']['seccode_ttf']?1:2));
	}

	static public function checkSeccode($sSeccode){
		return C::checkSeccode($sSeccode);
	}
	
	static public function avatar($nUid,$sType='middle',$bOuter=false){
		$sPath=C::getAvatar($nUid,$sType);
		return is_file(WINDSFORCE_PATH.'/user/avatar/'.$sPath)?
			($bOuter===false?__ROOT__:self::getSiteurl()).'/user/avatar/'.$sPath:
			($bOuter===false?__PUBLIC__:self::getSiteurl().'/Public/').'/images/avatar/noavatar_'.$sType.'.gif';
	}

	static public function avatars($nUserId=null){
		if($nUserId===null){
			$nUserId=$GLOBALS['___login___']['user_id'];
		}

		$arrAvatarInfo=array();
		$arrAvatarInfo['exist']=is_file(WINDSFORCE_PATH.'/user/avatar/'.C::getAvatar($nUserId,'big'))?true:false;
		$arrAvatarInfo['big']=Core_Extend::avatar($nUserId,'big');
		$arrAvatarInfo['middle']=Core_Extend::avatar($nUserId,'middle');
		$arrAvatarInfo['small']=Core_Extend::avatar($nUserId,'small');

		return $arrAvatarInfo;
	}

	static public function cacheSub($sCacheName){
		$sCacheSubdir='';

		// 全局子目录分析
		if(strpos($sCacheName,'@')){
			$arrTemp=explode('@',$sCacheName);
			$sCacheSubdir='/'.$arrTemp[0];
		}else{// app应用子目录分析
			$sApp=$sChild='';
			if(strpos($sCacheName,'_')){
				$arrTemp=explode('_',$sCacheName);
				$sApp=$arrTemp[0];
				if(!empty($arrTemp[2])){
					$sChild=$arrTemp[1];
				}
			}

			$sCacheSubdir=($sApp?'/'.$sApp:'').($sChild?'/'.$sChild:'');
		}

		return $sCacheSubdir;
	}

	static public function loadCache($CacheNames,$bForce=false,$sForcetype=null){
		static $arrLoadedCache=array();

		$CacheNames=is_array($CacheNames)?$CacheNames:array($CacheNames);
		$arrCaches=array();
		foreach($CacheNames as $sCacheName){
			if(!isset($arrLoadedCache[$sCacheName]) || $bForce){
				$arrCaches[]=$sCacheName;
				$arrLoadedCache[$sCacheName]=true;
			}
		}

		if(!empty($arrCaches)){
			$arrCacheDatas=self::cacheData($arrCaches,$sForcetype);
			foreach($arrCacheDatas as $sCacheName=>$data){
				if($sCacheName=='option'){
					$GLOBALS['_option_']=$data;
				}else{
					$GLOBALS['_cache_'][$sCacheName]=$data;
				}
			}
		}

		return true;
	}

	static public function cacheData($CacheNames,$sForcetype=null){
		if($sForcetype===null && empty($sForcetype)){
			if(!isset($bIsFilecache)){
				$bIsFilecache=$GLOBALS['_commonConfig_']['RUNTIME_CACHE_BACKEND']=='FileCache';
				$bAllowMem=self::memory('check');
			};
		}else{
			if($sForcetype=='FileCache'){
				$bIsFilecache=true;
				$bAllowMem=false;
			}elseif($sForcetype=='db'){
				$bIsFilecache=false;
				$bAllowMem=false;
			}else{
				$bIsFilecache=false;
				$bAllowMem=true;
			}
		}

		$arrData=array();
		$CacheNames=is_array($CacheNames)?$CacheNames:array($CacheNames);
		
		// Memcached 等支持
		if($bAllowMem){
			$arrNew=array();
			if(!Q::classExists('Cache_Extend')){
				require_once(Core_Extend::includeFile('function/Cache_Extend'));
			}
			foreach($CacheNames as $sCacheName){
				$arrData[$sCacheName]=self::memory('get',$sCacheName);
				if($arrData[$sCacheName]===false){
					$arrData[$sCacheName]=false;
					$arrNew[]=$sCacheName;
					Cache_Extend::updateCache($sCacheName);
				}
			}

			if(empty($arrNew)){
				return $arrData;
			}else{
				$CacheNames=$arrNew;
			}
		}elseif($bIsFilecache){
			$arrLostCaches=array();
			if(!Q::classExists('Cache_Extend')){
				require_once(Core_Extend::includeFile('function/Cache_Extend'));
			}
			foreach($CacheNames as $sCacheName){
				$arrData[$sCacheName]=Q::cache($sCacheName,'',array('cache_path'=>WINDSFORCE_PATH.'/~@~/data'.self::cacheSub($sCacheName)));
				if($arrData[$sCacheName]===false){
					$arrLostCaches[]=$sCacheName;
					Cache_Extend::updateCache($sCacheName);
				}
			}

			if(!$arrLostCaches){
				return $arrData;
			}
			$CacheNames=$arrLostCaches;
			unset($arrLostCaches);
		}

		$arrSyscaches=Model::F_('syscache',array('syscache_name'=>array('in',$CacheNames)))->getAll();
		foreach($arrSyscaches as $arrSyscache){
			$arrData[$arrSyscache['syscache_name']]=$arrSyscache['syscache_type']?unserialize($arrSyscache['syscache_data']):$arrSyscache['syscache_data'];
			$bAllowMem && (self::memory('set',$arrSyscache['syscache_name'],$arrData[$arrSyscache['syscache_name']]));
			if($bIsFilecache){
				Q::cache($arrSyscache['syscache_name'],$arrData[$arrSyscache['syscache_name']],array('cache_path'=>WINDSFORCE_PATH.'/~@~/data'.self::cacheSub($arrSyscache['syscache_name'])));
			}
		}

		foreach($CacheNames as $sCacheName){
			if(!isset($arrData[$sCacheName]) || $arrData[$sCacheName]===false){
				$arrData[$sCacheName]=false;
				$bAllowMem && (self::memory('set',$sCacheName,array()));
			}
		}

		return $arrData;
	}

	public static function saveSyscache($sCacheName,$Data){
		static $bIsFilecache=null,$bAllowMem=null;

		if(!isset($bIsFilecache)){
			$bIsFilecache=$GLOBALS['_commonConfig_']['RUNTIME_CACHE_BACKEND']=='FileCache';
			$bAllowMem=self::memory('check');
		}

		if(is_array($Data)){
			$nType=1;
			$Data=serialize($Data);
		}else{
			$nType=0;
		}

		$oSyscacheModel=new SyscacheModel();
		$oSyscacheModel->syscache_name=$sCacheName;
		$oSyscacheModel->syscache_type=$nType;
		$oSyscacheModel->syscache_data=$Data;
		$oSyscacheModel->save('replace');

		$bAllowMem && self::memory('delete',$sCacheName);
		if($bIsFilecache){
			$sCachefile=WINDSFORCE_PATH.'/~@~/data'.self::cacheSub($sCacheName).'/~@'.$sCacheName.'.php';
			is_file($sCachefile) && @unlink($sCachefile);
		}
	}

	static public function deleteCache($sCacheName){
		static $bIsFilecache=null,$bAllowMem=null;
		
		if(!isset($bIsFilecache)){
			$bIsFilecache=$GLOBALS['_commonConfig_']['RUNTIME_CACHE_BACKEND']=='FileCache';
			$bAllowMem=self::memory('check');
		}

		$bAllowMem && self::memory('delete',$sCacheName);
		if($bIsFilecache){
			$sCachefile=WINDSFORCE_PATH.'/~@~/data'.self::cacheSub($sCacheName).'/~@'.$sCacheName.'.php';
			is_file($sCachefile) && @unlink($sCachefile);
		}
	}

	public static function memory($sAction,$sKey='',$Value=''){
		$bMemEnable=$GLOBALS['_commonConfig_']['RUNTIME_CACHE_BACKEND']!='FileCache';

		if($sAction=='check'){
			return $bMemEnable?$GLOBALS['_commonConfig_']['RUNTIME_CACHE_BACKEND']:'';
		}elseif($bMemEnable && in_array($sAction,array('set','get','delete'))){
			switch($sAction){
				case 'set': return Q::cache($sKey,$Value); break;
				case 'get': return Q::cache($sKey); break;
				case 'delete': return Q::cache($sKey,null); break;
			}
		}

		return null;
	}
	
	static public function appLogo($sApp,$bHtml=false){
		$sLogo='';
		if(is_file(WINDSFORCE_PATH.'/System/app/'.$sApp.'/logo.png')){
			$sLogo=__ROOT__.'/System/app/'.$sApp.'/logo.png';
		}else{
			$sLogo=__ROOT__.'/System/app/logo.png';
		}
		
		if($bHtml===true){
			return "<img src=\"{$sLogo}\"";
		}else{
			return $sLogo;
		}
	}

	static public function includeFile($sFileName,$sApp=null,$sType='.class.php'){
		if(!empty($sApp)){
			$sIncludeFile='/System/app/'.$sApp.'/App/Class/'.($sType=='_.php'?'Extension/':'').$sFileName;
		}else{
			$sIncludeFile='/System/'.$sFileName;
		}
		return preg_match('/^[\w\d\/_]+$/i',$sIncludeFile)?realpath(WINDSFORCE_PATH.$sIncludeFile.$sType):false;
	}
	
	static function replaceSiteVar($sString,$arrReplaces=array()){
		$arrSiteVars=array(
			'{site_name}'=>$GLOBALS['_option_']['site_name'],
			'{site_description}'=>$GLOBALS['_option_']['site_description'],
			'{site_url}'=>self::getSiteurl(),
			'{time}'=>gmdate('Y-n-j H:i',CURRENT_TIMESTAMP),
			'{user_name}'=>$GLOBALS['___login___']?$GLOBALS['___login___']['user_name']:Q::L('游客','__COMMON_LANG__@Common'),
			'{user_nikename}'=>$GLOBALS['___login___']?($GLOBALS['___login___']['user_nikename']?$GLOBALS['___login___']['user_nikename']:$GLOBALS['___login___']['user_name']):
				Q::L('游客','__COMMON_LANG__@Common'),
			'{admin_email}'=>$GLOBALS['_option_']['admin_email']
		);
		
		$arrReplaces=array_merge($arrSiteVars,$arrReplaces);
		return str_replace(array_keys($arrReplaces),array_values($arrReplaces),$sString);
	}

	static public function getEvalValue($sValue){
		$arrMatches=array();
		if($sValue && preg_match("/{\s*(\S+?)\s*\}/ise",$sValue,$arrMatches)){
			@eval('$sValue='.$arrMatches[1].';');
			return $sValue;
		}else{
			return $sValue;
		}
	}

	static public function badword($sContent){
		if(empty($sContent)){
			return '';
		}

		if(!$GLOBALS['_option_']['badword_on']){
			return $sContent;
		}

		if(!isset($GLOBALS['_cache_']['badword'])){
			if(!Q::classExists('Cache_Extend')){
				require(Core_Extend::includeFile('function/Cache_Extend'));
			}
			self::loadCache('badword');
		}

		foreach($GLOBALS['_cache_']['badword'] as $arrBadword){
			$sContent=preg_replace($arrBadword['regex'],$arrBadword['value'],$sContent);
		}
		return $sContent;
	}

	static public function isPostInt($value){
		return !preg_match("/[^\d-.,]/",trim($value,'\''));
	}

	static public function isAlreadyFriend($nUserId){
		return FriendModel::isAlreadyFriend($nUserId,$GLOBALS['___login___']['user_id']);
	}

	static public function getBeginEndDay(){
		$nYear=date("Y");
		$nMonth=date("m");
		$nDay=date("d");

		$nDayBegin=mktime(0,0,0,$nMonth,$nDay,$nYear);//当天开始时间戳
		$nDayEnd=mktime(23,59,59,$nMonth,$nDay,$nYear);//当天结束时间戳

		return array($nDayBegin,$nDayEnd);
	}

	static public function segmentUsername($sUserName){
		if(empty($sUserName)){
			return '';
		}

		$sUserName=str_replace(',',';',$sUserName);
		$arrUsers=explode(';',$sUserName);
		return $arrUsers;
	}

	static public function getUploadSize($nSize=null){
		$nReturnSize=-1;
		$nUploadmaxfilesize=intval(ini_get('upload_max_filesize'));
		$nPostmaxsize=intval(ini_get('post_max_size'));
		
		$nPhpIni=($nUploadmaxfilesize<=$nPostmaxsize?$nUploadmaxfilesize:$nPostmaxsize)*1048576;
		if(is_null($nSize)){
			$nSize=$GLOBALS['_option_']['uploadfile_maxsize'];
		}

		if($nSize==-1){
			$nReturnSize=$nPhpIni;
		}else{
			if($nSize>=$nPhpIni){
				$nReturnSize=$nPhpIni;
			}else{
				$nReturnSize=$nSize;
			}
		}

		return $nReturnSize;
	}
	
	static public function aidencode($nId){
		static $sSidAuth='';
		$sSidAuth=$sSidAuth!=''?$sSidAuth:C::authcode(Q::cookie($GLOBALS['_commonConfig_']['RBAC_DATA_PREFIX'].'hash'),false);
		return rawurlencode(base64_encode($nId.'|'.substr(md5($nId.md5($GLOBALS['_commonConfig_']['QEEPHP_AUTH_KEY']).
			CURRENT_TIMESTAMP),0,8).'|'.CURRENT_TIMESTAMP.'|'.$sSidAuth));
	}

	static public function updateCreditByAction($sAction,$nUserId=0,$arrExtraSql=array(),$nCoef=1,$nUpdate=1){
		if($nUserId<1){
			return false;
		}
		
		if(!Q::classExists('Credit')){
			require_once(Core_Extend::includeFile('class/Credit'));
		}

		$oCredit=Q::instance('Credit');
		if($arrExtraSql){
			$oCredit->_arrExtraSql=$arrExtraSql;
		}

		return $oCredit->execRule($sAction,$nUserId,$nCoef,$nUpdate);
	}

	static public function timeFormat($nDate){
		if($GLOBALS['_option_']['date_convert']==1){
			return C::smartDate($nDate,$GLOBALS['_option_']['time_format']);
		}else{
			return date($GLOBALS['_option_']['time_format'],$nDate);
		}
	}

	static public function editorInclude(){
		$arrLangmap=array(
			'Zh-cn'=>'zh_CN',
			'Zh-tw'=>'zh_TW',
			'En-us'=>'en',
			'Ar'=>'ar',
		);

		$sLangname=LANG_NAME?LANG_NAME:'Zh-cn';
		$sPublic=__PUBLIC__;
		$sKindeditorLang=is_file(WINDSFORCE_PATH.'/Public/js/editor/kindeditor/lang/'.$arrLangmap[$sLangname].'.js')?$arrLangmap[$sLangname]:'zh_CN';

		return <<<WINDSFORCE
		<script type="text/javascript">var sEditorLang='{$sKindeditorLang}';</script>
		<script src="{$sPublic}/js/editor/kindeditor/kindeditor-min.js" type="text/javascript"></script>
WINDSFORCE;
	}

	static public function deleteAppconfig($sApp=null,$bCleanCookie=true){
		if(is_null($sApp)){
			$arrSaveDatas=array();
			$arrWhere['app_status']=1;
			$arrApps=AppModel::F()->where($arrWhere)->all()->query();
			if(is_array($arrApps)){
				foreach($arrApps as $oApp){
					$arrSaveDatas[]=$oApp['app_identifier'];
				}
			}
			$arrSaveDatas[]='admin';

			foreach($arrSaveDatas as $sTheApp){
				self::deleteAppconfig($sTheApp);
			}
		}else{
			$sApp=strtolower($sApp);
			$sAppConfigcachefile=WINDSFORCE_PATH.'/~@~/app/'.$sApp.'/Config.php';
			if(is_file($sAppConfigcachefile)){
				@unlink($sAppConfigcachefile);
			}
			if($bCleanCookie===true){
				Q::cookie('template',null,-1);
				Q::cookie('language',null,-1);
			}
		}

		return true;
	}

	static public function changeAppconfig($Data,$sValue=''){
		if(!is_array($Data)){
			$Data=array($Data=>$sValue);
		}

		$sAppGlobalconfigFile=WINDSFORCE_PATH.'/~@~/Config.inc.php';
		$arrAppconfig=(array)(include $sAppGlobalconfigFile);
		$arrAppconfig=array_merge($arrAppconfig,$Data);
		if(!file_put_contents($sAppGlobalconfigFile,
			"<?php\n /* QeePHP Config File,Do not to modify this file! */ \n return ".
			var_export($arrAppconfig,true).
			"\n?>")
		){
			Q::E(Q::L('全局配置文件 %s 不可写','__COMMON_LANG__@Common',null,$sAppGlobalconfigFile));
		}

		self::deleteAppconfig();
	}

	static public function template($sTemplate,$sApp=null,$sTheme=null){
		if(empty($sTheme)){
			$sTemplate=TEMPLATE_NAME.'/'.$sTemplate;
		}else{
			$sTemplate=$sTheme.'/'.$sTemplate;
		}

		if(!empty($sApp)){
			$sTemplatePath=WINDSFORCE_PATH.'/System/app/'.$sApp.'/Theme';
		}else{
			$sTemplatePath=WINDSFORCE_PATH.'/user/theme';
		}

		$sUrl=$sTemplatePath.'/'.$sTemplate.'.html';
		if(is_file($sUrl)){
			return $sUrl;
		}

		if(defined('QEEPHP_TEMPLATE_BASE') && empty($sTheme) && ucfirst(QEEPHP_TEMPLATE_BASE)!==TEMPLATE_NAME){// 依赖模板 兼容性分析
			$sUrlTry=str_replace('heme/'.TEMPLATE_NAME.'/','heme/'.ucfirst(QEEPHP_TEMPLATE_BASE).'/',$sUrl);
			if(is_file($sUrlTry)){
				return $sUrlTry;
			}
		}

		if(empty($sTheme) && 'Default'!==TEMPLATE_NAME){// Default模板 兼容性分析
			$sUrlTry=str_replace('heme/'.TEMPLATE_NAME.'/','heme/Default/',$sUrl);
			if(is_file($sUrlTry)){
				return $sUrlTry;
			}
		}

		Q::E(sprintf('Template File %s is not exist',$sUrl));
	}

	static public function getStylePreview($Style,$sType='',$bAdmin=false,$sTemplate=''){
		if(empty($sTemplate)){
			if(!is_object($Style)){
				$Style=StyleModel::F('style_id=?',$Style)->getOne();
			}
			
			if(empty($Style['style_id'])){
				return self::getNoneimg();
			}
	
			$oTheme=ThemeModel::F('theme_id=?',$Style['theme_id'])->getOne();
			if(empty($oTheme['theme_id'])){
				return self::getNoneimg();
			}
			
			$sTemplate=ucfirst($oTheme['theme_dirname']);
		}else{
			$sTemplate=ucfirst(strtolower($sTemplate));
		}

		if($bAdmin===false){
			$sPreviewPath='user/theme';
		}else{
			$sPreviewPath='System/admin/Theme';
		}

		if($sType=='large'){
			$sPreview='windsforce_preview_large';
		}elseif($sType=='mini'){
			$sPreview='windsforce_preview_mini';
		}else{
			$sPreview='windsforce_preview';
		}

		foreach(array('png','gif','jpg','jpeg') as $sExt){
			if(is_file(WINDSFORCE_PATH.'/'.$sPreviewPath.'/'.$sTemplate."/{$sPreview}.{$sExt}")){
				return __ROOT__.'/'.$sPreviewPath.'/'.$sTemplate."/{$sPreview}.{$sExt}";
				continue;
			}
		}

		return self::getNoneimg();
	}

	static public function getNoneimg(){
		return __PUBLIC__.'/images/common/none.gif';
	}

	static public function promotion(){
		$oPromotion=Q::instance('PromotionController');
		$oPromotion->index();
	}

	static public function initFront(){
		// 配置&菜单&登陆信息
		Core_Extend::loadCache('option');
		Core_Extend::loadCache('nav');
		Core_Extend::loginInformation();
		
		// CSS资源定义
		if(isset($GLOBALS['_commonConfig_']['_CURSCRIPT_'])){
			Core_Extend::defineCurscript($GLOBALS['_commonConfig_']['_CURSCRIPT_']);
		}
		
		// 读取当前的主题样式
		$sStyleCachepath=self::getCurstyleCachepath();
		$arrMustFile=array('style.css','common.css','style.php');

		foreach($arrMustFile as $sMustFile){
			if(!is_file($sStyleCachepath.'/'.$sMustFile)){
				if(!Q::classExists('Cache_Extend')){
					require_once(Core_Extend::includeFile('function/Cache_Extend'));
				}
				Cache_Extend::updateCache('style');
			}
		}

		if(!is_file($sStyleCachepath.'/style.php')){
			Q::cookie('style_id',null,-1);
			Q::E('Style file not found!');
		}

		$GLOBALS['_style_']=(array)(include $sStyleCachepath.'/style.php');
		define('QEEPHP_TEMPLATE_BASE',$GLOBALS['_style_']['qeephp_template_base']);
		
		if(defined('CURSCRIPT')){
			$sCurscript=$sStyleCachepath.'/scriptstyle_'.APP_NAME.'_'.str_replace('::','_',CURSCRIPT).'.css';
		}else{
			$sCurscript='';
		}

		if(defined('CURSCRIPT') && !is_file($sCurscript)){
			$sContent=$GLOBALS['_curscript_']='';
			$sContent=file_get_contents($sStyleCachepath.'/style.css');
			if(is_file($sStyleCachepath.'/'.APP_NAME.'_'.'style.css')){
				$sContent.=file_get_contents($sStyleCachepath.'/'.APP_NAME.'_'.'style.css');
			}
			$sContent=preg_replace("/([\n\r\t]*)\[CURSCRIPT\s*=\s*(.+?)\]([\n\r]*)(.*?)([\n\r]*)\[\/CURSCRIPT\]([\n\r\t]*)/ies","Core_Extend::cssVarTags('\\2','\\4')",$sContent);

			$sCssCurScripts=$GLOBALS['_curscript_'];
			$sCssCurScripts=preg_replace(array('/\s*([,;:\{\}])\s*/','/[\t\n\r]/','/\/\*.+?\*\//'),array('\\1','',''),$sCssCurScripts);
			$sCssCurScripts=trim(stripslashes($sCssCurScripts));
			if($sCssCurScripts==''){
				$sCssCurScripts=' ';
			}

			if(!is_dir(dirname($sCurscript))){
				$nStyleid=intval(Q::cookie('style_id'));
				if($GLOBALS['___login___']!==false && $GLOBALS['___login___']['user_extendstyle']==$nStyleid){
					$oUser=UserModel::F('user_id=?',$GLOBALS['___login___']['user_id'])->getOne();
					$oUser->user_extendstyle=0;
					$oUser->save('update');
					if($oUser->isError()){
						Q::E($oUser->getErrorMessage());
					}
				}
				
				Q::cookie('style_id',null,-1);
			}

			if(!file_put_contents($sCurscript,$sCssCurScripts)){
				Q::E(Q::L('无法写入缓存文件,请检查缓存目录 %s 的权限是否为0777','__COMMON_LANG__@Common',null,$sStyleCachepath));
			}
		}

		// 清除直接使用$_GET['t']带来的影响
		if(isset($_GET['t'])){
			Q::cookie('template',NULL,-1);
		}
		
		// 读取语言缓存
		Core_Extend::loadCache('lang');

		// 判断应用是否启用
		Core_Extend::loadCache('app');
		if($GLOBALS['_commonConfig_']['APP_DEBUG']===FALSE && !in_array(APP_NAME,$GLOBALS['_cache_']['app']) && APP_NAME!=='home'){
			Q::E(Q::L('应用 %s 尚未开启或者不存在','__COMMON_LANG__@Common',null,APP_NAME));
		}

		// 站点关闭
		if($GLOBALS['_option_']['close_site'] && !Core_Extend::isAdmin() && !Core_Extend::isCrawler()){
			Q::E('<h1 style="color:red;">The site is closed!</h1><br/>'.($GLOBALS['_option_']['close_site_reason']?$GLOBALS['_option_']['close_site_reason']:'No reason'));
		}

		// 必须要登陆才能够访问内容
		if($GLOBALS['_option_']['only_login_viewsite']==1 && $GLOBALS['___login___']===false && !Core_Extend::isCrawler()){
			if(!((APP_NAME==='home' && MODULE_NAME==='public' && !in_array(ACTION_NAME,array('index','mobile','sitemap','myrbac','role'))) || (APP_NAME==='home' && in_array(MODULE_NAME,array('getpassword','userappeal'))))){
				if(!in_array(APP_NAME.'@'.MODULE_NAME.'@'.ACTION_NAME,$GLOBALS['_commonConfig_']['LOGINVIEW_IGNORE'])){
					C::urlGo(Q::U('home://public/login',array('referer'=>__SELF__,'loginview'=>1),true));
				}
			}
		}

		// 计划任务
		if($GLOBALS['_commonConfig_']['CRON_ON']===true){
			Core_Extend::loadCache('cronnextrun',false,'db');
			if($GLOBALS['_cache_']['cronnextrun']<=CURRENT_TIMESTAMP){
				if(!Q::classExists('Windsforce_Cron')){
					require_once(Core_Extend::includeFile('class/windsforce/Windsforce_Cron'));
				}
				
				Windsforce_Cron::RUN();
			}
		}

		// 访问推广
		if(!empty($_GET['fromuid'])){
			Core_Extend::promotion();
		}
		
		// 在线统计
		if($GLOBALS['_option_']['online_on']==1){
			Online::RUN();
		}
	}

	static public function isCrawler(){
		$sAgent=strtolower($_SERVER['HTTP_USER_AGENT']);

		if(!empty($sAgent)){
			$arrSpiderSite=array(
				"TencentTraveler","Baiduspider+","BaiduGame","Googlebot","msnbot","Sosospider+",
				"Sogou web spider","ia_archiver","Yahoo! Slurp","YoudaoBot","Yahoo Slurp","MSNBot",
				"Java (Often spam bot)","BaiDuSpider","Voila","Yandex bot","BSpider","twiceler",
				"Sogou Spider","Speedy Spider","Google AdSense","Heritrix","Python-urllib","Alexa (IA Archiver)",
				"Ask","Exabot","Custo","OutfoxBot/YodaoBot","yacy","SurveyBot","legs","lwp-trivial",
				"Nutch","StackRambler","The web archive (IA Archiver)","Perl tool","MJ12bot","Netcraft",
				"MSIECrawler","WGet tools","larbin","Fish search",
			);

			foreach($arrSpiderSite as $sVal){
				$sStr=strtolower($sVal);
				if(strpos($sAgent,$sStr)!==false){
					return true;
				}
			}
		}else{
			return false;
		}
	}

	static public function loadLang(){
		$sHtml='';
		if(LANG_NAME!=='Zh-cn'){
			if(is_file(__COMMON_LANG__.'/'.LANG_NAME.'/Common.js')){
				$sHtml.="<script src=\"".__ROOT__."/user/language/".LANG_NAME."/Common.js\"></script>";
			}

			if(is_file(WINDSFORCE_PATH.'/System/app/'.APP_NAME.'/App/Lang/Admin/'.LANG_NAME.'/App.js')){
				$sHtml.="<script src=\"".__ROOT__."/System/app/".APP_NAME."/App/Lang/Admin/".LANG_NAME."/App.js\"></script>";
			}
		}
		return $sHtml;
	}

	static public function loadCss(){
		self::loadLang();
		$sStyleCachepath=self::getCurstyleCachepath();
		$sStyleCacheurl=self::getCurstyleCacheurl();
		
		$sScriptCss='';
		$sScriptCss='<link rel="stylesheet" type="text/css" href="'.$sStyleCacheurl.'/common.css?'.$GLOBALS['_style_']['verhash']."\" />";
		
		if(is_file($sStyleCachepath.'/'.APP_NAME.'_common.css')){
			$sScriptCss.='<link rel="stylesheet" type="text/css" href="'.$sStyleCacheurl.'/'.APP_NAME.'_common.css?'.$GLOBALS['_style_']['verhash']."\" />";
		}

		if(defined('CURSCRIPT')){
			$sScriptCss.='<link rel="stylesheet" type="text/css" href="'.$sStyleCacheurl.'/scriptstyle_'.APP_NAME.'_'.str_replace('::','_',CURSCRIPT).'.css?'.$GLOBALS['_style_']['verhash']."\" />";
		}
		
		$sScriptCss.='<link rel="stylesheet" type="text/css" href="'.$sStyleCacheurl.'/windsforce.css?'.$GLOBALS['_style_']['verhash']."\" />";
		
		// 如果启用了样式切换
		if(!empty($GLOBALS['_style_']['_style_extend_icons_'])){
			if($GLOBALS['_option_']['extendstyle_switch_on']==1){
				$sCurrentT=Q::cookie('extend_style_id');
				if($sCurrentT==''){
					if($GLOBALS['___login___']!==false){
						$sCurrentT=$GLOBALS['___login___']['user_extendstyle'];
					}else{
						$sCurrentT=$GLOBALS['_style_']['_current_style_'];
					}
				}
			}else{
				$sCurrentT=$GLOBALS['_style_']['_current_style_'];
			}
			
			if(!empty($sCurrentT) && is_file($sStyleCachepath.'/t_'.$sCurrentT.'.css')){
				$sScriptCss.='<link rel="stylesheet" id="extend_style" type="text/css" href="'.$sStyleCacheurl.'/t_'.$sCurrentT.'.css?'.$GLOBALS['_style_']['verhash']."\" />";
				$GLOBALS['_extend_style_']=$sCurrentT;

				// 取得扩展背景图片
				if(is_dir(WINDSFORCE_PATH.'/user/theme/'.$GLOBALS['_style_']['qeephp_template_base'].'/Public/Style/'.$sCurrentT.'/bgextend')){
					$arrBgimgPath=self::getJsbg('user/theme/'.$GLOBALS['_style_']['qeephp_template_base'].'/Public/Style/'.$sCurrentT.'/bgextend');
					$arrBgimgPath[]='"'.__ROOT__.'/user/theme/'.$GLOBALS['_style_']['qeephp_template_base'].'/Public/Style/'.$sCurrentT.'/bgimg.jpg'.'"';

					if($arrBgimgPath){
						$sScriptCss.="<script type=\"text/javascript\">";
						$sScriptCss.=
					"var globalImgbgs=[".implode(',',$arrBgimgPath)."];";
						$sScriptCss.="</script>";
					}
				}
			}else{
				$GLOBALS['_extend_style_']='0';
				$sScriptCss.='<link rel="stylesheet" id="extend_style" type="text/css" href="'.__PUBLIC__.'/images/common/none.css?'.$GLOBALS['_style_']['verhash']."\" />";

				// 取得背景图片
				if(is_dir(WINDSFORCE_PATH.'/user/theme/'.$GLOBALS['_style_']['qeephp_template_base'].'/Public/Images/bgextend')){
					$arrBgimgPath=self::getJsbg('user/theme/'.$GLOBALS['_style_']['qeephp_template_base'].'/Public/Images/bgextend');

					if($arrBgimgPath){
						$sScriptCss.="<script type=\"text/javascript\">";
						$sScriptCss.=
					"var globalImgbgs=[".implode(',',$arrBgimgPath)."];";
						$sScriptCss.="</script>";
					}
				}
			}
		}
		
		return $sScriptCss;
	}

	static public function getJsbg($sPathdir){
		$arrBgimgPath=array();
		
		// 取得背景图片
		if(is_dir(WINDSFORCE_PATH.'/'.$sPathdir)){
			$arrBgimgPath='';

			$arrFiles=C::listDir(WINDSFORCE_PATH.'/'.$sPathdir,false,true);
			sort($arrFiles);
			if(is_array($arrFiles)){
				foreach($arrFiles as &$sFile){
					$arrBgimgPath[]='"'.__ROOT__.'/'.$sPathdir.'/'.$sFile.'"';
				}
			}
		}

		return $arrBgimgPath;
	}
	
	static public function cssVarTags($sCurScript,$sContent){
		$arrCurScript=Q::normalize(explode(',',trim($sCurScript)));
		
		// 应用::模块::方法
		$bGetcontent=in_array(APP_NAME.'::'.CURSCRIPT,$arrCurScript);

		// 应用::模块
		if($bGetcontent===false){
			if(strpos(CURSCRIPT,'::')){
				$arrTemp=explode('::',CURSCRIPT);
				$bGetcontent=in_array(APP_NAME.'::'.$arrTemp[0],$arrCurScript);
			}
		}

		// 公用
		if($bGetcontent===false && defined('CURSCRIPT_COMMON')){
			$arrCommonscript=explode(',',CURSCRIPT_COMMON);
			if(is_array($arrCommonscript)){
				foreach($arrCommonscript as $sValue){
					if(in_array('@'.$sValue,$arrCurScript)){
						$bGetcontent=true;
						break;
					}
				}
			}
		}

		$GLOBALS['_curscript_'].=$bGetcontent?$sContent:'';
	}

	static public function getStyleId($nId=0){
		return Q::cookie('style_id')?Q::cookie('style_id'):$GLOBALS['_option_']['front_style_id'];
	}

	static public function getCurstyleCachepath(){
		return WINDSFORCE_PATH.'/~@~/style_/'.self::getStyleId();
	}

	static public function getCurstyleCacheurl(){
		return __ROOT__."/~@~/style_/".self::getStyleId();
	}

	static public function defineCurscript($arrModulecachelist){
		$arrResult=array();

		foreach($arrModulecachelist as $nKey=>$sCache){
			if(!is_int($nKey)){
				$temp=$sCache;
				$sCache=$nKey;
				$nKey=$temp.'*'.C::randString(6);
			}
			
			// 定义
			if(strpos($sCache,',')){
				foreach(explode(',',$sCache) as $nCacheKey=>$sValue){
					$arrResult[$nKey.C::randString(6)]=$sValue;
				}
			}else{
				$arrResult[$nKey]=$sCache;
			}
		}
		
		$arrResult=array_unique($arrResult);
	
		// 优先 @ucenter::index
		foreach(array(MODULE_NAME.'::'.ACTION_NAME,MODULE_NAME) as $sValue){
			$nKey=array_search($sValue,$arrResult);
			if($nKey!==false){
				if(strpos($nKey,'*')){
					$arrTemp=explode('*',$nKey);
					define('CURSCRIPT_COMMON',$arrTemp[0]);
				}

				define('CURSCRIPT',$sValue);
				break;
			}
		};
	}

	static public function thumb($sFilepath,$nWidth=0,$nHeight=0){
		if(!is_file($sFilepath)){
			$sFilepath=WINDSFORCE_PATH.'/Public/images/common/none.gif';
		}
		if(!$nWidth){
			$arrSize=@getimagesize($sFilepath);
			$nWidth=$arrSize[0];
			$nHeight=$arrSize[1];
		}
		Image::thumbGd($sFilepath,$nWidth,$nHeight);
	}

	static public function wapImage($nId,$nThumb=1,$nWidth=0,$nHeight=0){
		$arrTemp=explode('|',$GLOBALS['_option_']['wap_img_size']);

		if($nWidth==0){
			$nWidth=$arrTemp[0];
		}

		if($nHeight==0){
			$nHeight=$arrTemp[1];
		}

		return Q::U('home://misc/thumb?id='.$nId.'&w='.$nWidth.'&h='.$nHeight.'&thumb='.$nThumb);
	}

	static public function ubb($sContent,$bHomefreshmessage=true,$bUsersign=false,$nOuter=0){
		$oUbb2html=Q::instance('Ubb2html',array($sContent,$bHomefreshmessage,$nOuter));
		if($bUsersign===true){
			$sContent=$oUbb2html->convertUsersign();
		}else{
			$sContent=$oUbb2html->convert();
		}

		return $sContent;
	}

	static public function emotion(){
		$sLangname=LANG_NAME?LANG_NAME:'Zh-cn';
		$sPublic=__PUBLIC__;
		$sEmotionLang=is_file(WINDSFORCE_PATH.'/Public/js/emotions/language/'.$sLangname.'.js')?$sLangname:'Zh-cn';

		return <<<WINDSFORCE
		<link href="{$sPublic}/js/emotions/emoticon.css" rel="stylesheet" type="text/css" />
		<script type="text/javascript" src="{$sPublic}/js/emotions/language/{$sEmotionLang}.js"></script>
		<script type="text/javascript" src="{$sPublic}/js/emotions/jquery.emoticons.js"></script>
WINDSFORCE;
	}

	static public function calendar(){
		$sLangname=LANG_NAME?LANG_NAME:'Zh-cn';
		$sLangname=strtolower($sLangname);
		$sCalendarLang=is_file(WINDSFORCE_PATH.'/js/calendar/datepicker/lang/'.$sLangname.'.js')?$sLangname:'zh-cn';
		return $sCalendarLang;
	}

	static public function validate(){
		$sPublic=__PUBLIC__;
		$sValidateLang=is_file(WINDSFORCE_PATH.'/Public/js/jquery/validate/lang/'.LANG_NAME.'/lang.js')?LANG_NAME:'Zh-cn';

		return <<<WINDSFORCE
		<link rel="stylesheet" href="{$sPublic}/js/jquery/validate/images/validate.css">
		<script src="{$sPublic}/js/jquery/validate/jquery.validate.min.js"></script>
		<script src="{$sPublic}/js/jquery/validate/lang/{$sValidateLang}/lang.js"></script>
WINDSFORCE;
	}

	static public function removeDir($sDirName){
		if(!is_dir($sDirName)){
			@unlink($sDirName);
			return false;
		}

		$hHandle=@opendir($sDirName);
		while(($file=@readdir($hHandle))!==false){
			if($file!='.' && $file!='..'){
				$sDir=$sDirName.'/'.$file;
				if(is_dir($sDir)){
					self::removeDir($sDir);
				}else{
					@unlink($sDir);
				}
			}
		}

		closedir($hHandle);
		$bResult=rmdir($sDirName);
		return $bResult;
	}

	public static function isEmptydir($sDir){
		$hDir=@opendir($sDir);
		$nI=0;
		while($file=readdir($hDir)){
			$nI++;
		}
		closedir($hDir);

		if($nI>2){
			return false;
		}else{
			return true;
		}
	}

	public static function addFeed($sTemplate,$arrData,$nUserid=0,$sUsername=''){
		if(empty($nUserid)){
			$nUserid=$GLOBALS['___login___']['user_id'];
		}
		$nUserid=intval($nUserid);
		if(empty($sUsername)){
			$sUsername=$GLOBALS['___login___']['user_name'];
		}
		
		$oFeed=Q::instance('FeedModel');
		$oFeed->addFeed($sTemplate,$arrData,$nUserid,$sUsername);
		if($oFeed->isError()){
			Q::E($oFeed->getErrorMessage());
		}
	}

	public static function addNotice($sTemplate,$arrData,$nTouserid,$sType='system',$nFromid=0,$nUserid=0,$sUsername=''){
		if(empty($nUserid)){
			$nUserid=$GLOBALS['___login___']['user_id'];
		}
		$nUserid=intval($nUserid);
		
		if(empty($sUsername)){
			$sUsername=$GLOBALS['___login___']['user_name'];
		}

		$oNotice=Q::instance('NoticeModel');
		$oNotice->addNotice($sTemplate,$arrData,$nTouserid,$sType,$nFromid,$nUserid,$sUsername);
		if($oNotice->isError()){
			Q::E($oNotice->getErrorMessage());
		}
	}

	static public function checkSpam($arrData=array(),$bLogincheck=true){
		if(Core_Extend::isAdmin()){
			return true;
		}
		
		// 是否登录检查
		if($bLogincheck===TRUE && $GLOBALS['___login___']===FALSE){
			Q::E(Q::L('你没有登录，无法发布信息','__COMMON_LANG__@Common').'<br/><a href="'.Q::U('home://public/login').'">'.Q::L('前往登录','__COMMON_LANG__@Common').'</a>');
		}
		
		// 两次发表时间间隔
		$nFloodctrl=intval($GLOBALS['_option_']['flood_ctrl']);
		if($nFloodctrl>0 && isset($arrData['lasttime'])){
			$nLasttime=intval($arrData['lasttime']);
			if($nLasttime>0 && CURRENT_TIMESTAMP-$nLasttime<=$nFloodctrl){
				Q::E(Q::L('为防止灌水,发布信息时间间隔为 %d 秒','__COMMON_LANG__@Common',null,$nFloodctrl));
			}
		}

		// 强制用户激活邮箱
		if($GLOBALS['_option_']['need_email']==1 && $GLOBALS['___login___']['user_isverify']==0){
			Q::E(Q::L('你只有验证邮箱 %s 后才能够发布信息','__COMMON_LANG__@Common',null,$GLOBALS['___login___']['user_email']).'<br/><a href="'.Q::U('home://spaceadmin/verifyemail').'">'.Q::L('前往验证邮箱','__COMMON_LANG__@Common').'</a>');
		}

		// 强制用户上传头像
		if($GLOBALS['_option_']['need_avatar']){
			$arrAvatarInfo=Core_Extend::avatars($GLOBALS['___login___']['user_id']);
			if(!$arrAvatarInfo['exist']){
				Q::E(Q::L('你只有上传头像后才能够发布信息','__COMMON_LANG__@Common').'<br/><a href="'.Q::U('home://spaceadmin/avatar').'">'.Q::L('前往上传头像','__COMMON_LANG__@Common').'</a>');
			}
		}

		// 强制用户好友个数
		$nNeedfriendnum=intval($GLOBALS['_option_']['need_friendnum']);
		if($nNeedfriendnum>0){
			$oUsercount=UsercountModel::F('user_id=?',$GLOBALS['___login___']['user_id'])->getOne();
			if(!empty($oUsercount['user_id'])){
				$nHavefriendnum=intval($oUsercount['usercount_friends']);
				if($nHavefriendnum<$nNeedfriendnum){
					Q::E(Q::L('你只有至少添加 %d 个好友后才能够发布信息','__COMMON_LANG__@Common',null,$nNeedfriendnum).'<br/><a href="'.Q::U('home://friend/search').'">'.Q::L('前往添加好友','__COMMON_LANG__@Common').'</a>');
				}
			}else{
				Q::E(Q::L('用户统计数据不存在，请联系管理员修复','__COMMON_LANG__@Common').'<br/>'.Q::L('管理员邮箱地址','__COMMON_LANG__@Common').' '.$GLOBALS['_option_']['admin_email']);
			}
		}
	}

	static public function contentParsetag($sContent,$bUser=true,$bTag=true,$nTagmaxnum=5){
		// 初始化一些变量
		$arrReturn=array();
		$arrTags=$arrAtuserids=$arrContentsearch=$arrContentreplace=array();
		if($nTagmaxnum<1){
			$nTagmaxnum=1;
		}

		// @user_name 功能解析
		if(false!==strpos($sContent,'@')){
			if (preg_match_all('~\@([\w\d\_\-\x7f-\xff]+)(?:[\r\n\t\s ]+|[\xa1\xa1]+|[\xa3\xac]|[\xef\xbc\x8c]|[\,\.\;\[\#])~',$sContent,$arrMatch)){
				if(isset($arrMatch[1]) && is_array($arrMatch[1]) && count($arrMatch[1])){
					foreach($arrMatch[1] as $nKey=>$sValue){
						$sValue=trim($sValue);
						if('　'==substr($sValue,-2)){
							$sValue=substr($sValue,0,-2);
						}
						if($sValue && strlen($sValue)<50){
							$arrMatch[1][$nKey]=$sValue;
						}
					}

					$arrUsers=UserModel::F()->setColumns('user_id,user_name')->where(array('user_name'=>array('in',$arrMatch[1]),'user_status'=>1))->getAll();
					if(is_array($arrUsers)){
						foreach($arrUsers as $oUser){
							$sAtuser="@{$oUser['user_name']}";
							$arrContentsearch[$sAtuser]=$sAtuser;
							$arrContentreplace[$sAtuser]="[MESSAGE]@{$oUser['user_name']}[/MESSAGE] ";
							$arrAtuserids[$oUser['user_id']]=$oUser['user_id'];
						}
					}
				}
			}
		}

		// #你的话题# 功能解析
		if($bTag===true && false!==strpos($sContent,'#')){
			$arrMatch=array();
			if(preg_match_all('~\#([^\/\-\@\#\[\$\{\}\(\)\;\<\>\\\\]+?)\#~',$sContent,$arrMatch)){
				$nI=0;
				foreach($arrMatch[1] as $sValue){
					$sValue=trim($sValue);
					if (($nValuelen=strlen($sValue))<2 || $nValuelen>50){
						continue;
					}
					$arrTags[$sValue]=$sValue;
					$sTag="#{$sValue}#";
					$arrContentsearch[$sTag]=$sTag;
					$arrContentreplace[$sTag]="[TAG]#{$sValue}#[/TAG]";
					if(++$nI>=$nTagmaxnum){
						break;
					}
				}
			}
		}

		// 内容替换
		if($arrContentsearch && $arrContentreplace){
			uasort($arrContentsearch,create_function('$sA,$sB','return(strlen($sA)<strlen($sB));'));
			foreach($arrContentsearch as $sKey=>$sValue){
				if($sValue && isset($arrContentreplace[$sKey])){
					$sContent=str_replace($sValue,$arrContentreplace[$sKey],$sContent);
				}
			}
		}

		$sContent=trim($sContent);
		$arrReturn['content']=$sContent;
		$arrReturn['atuserids']=$arrAtuserids;
		$arrReturn['tags']=$arrTags;
		return $arrReturn;
	}

	static public function getLogo(){
		if($GLOBALS['_option_']['site_logo']){
			$sLogo=$GLOBALS['_option_']['site_logo'];
		}else{
			$sLogo=$GLOBALS['_style_']['logo'];
		}
		return $sLogo;
	}

	static public function getFavicon(){
		if($GLOBALS['_option_']['site_favicon']){
			$sFavicon=$GLOBALS['_option_']['site_favicon'];
		}else{
			$sFavicon=__ROOT__.'/user/theme/'.Q::cookie('template').'/favicon.png';
			if(!is_file(WINDSFORCE_PATH.'/user/theme/'.Q::cookie('template').'/favicon.png')){
				$sFavicon=__ROOT__.'/user/theme/Default/favicon.png';
			}
		}
		return $sFavicon;
	}

	static public function windsforceReferer($bRegister=false){
		return Q::U('home://public/'.($bRegister===true?'register':'login').'?referer='.urlencode(__SELF__),array(),true);
	}

	static public function getSiteurl(){
		return $GLOBALS['_option_']['site_url'];
	}

	static public function windsforceOuter($Params,$sEnter='index.php'){
		$sUrl=self::getSiteurl().'/'.$sEnter.'?';
		if(is_array($Params)){
			$sStr='';
			foreach($Params as $sVar=>$sVal){
				$sStr.=$sVar.'='.urlencode($sVal).'&';
			}
			$sStr=rtrim($sStr,'&');
		}else{
			$sStr=trim($Params);
		}

		return $sUrl.$sStr;
	}

	static public function newData($nCreatedateline,$bReturnImg=false,$nDate=86400){
		$bIsNew=false;
		if(CURRENT_TIMESTAMP-$nCreatedateline<=$nDate){
			$bIsNew=true;
		}

		if($bReturnImg===true){
			if($bIsNew===true){
				return ' <img class="new_data" src="'.__ROOT__.'/Public/images/common/new.gif" border="0" align="absmiddle" title="'.Q::L('新发表的','__COMMON_LANG__@Common').'"/>';
			}else{
				return '';
			}
		}else{
			return $bIsNew;
		}
	}

	static public function usersign($sUsersign){
		return Core_Extend::ubb(nl2br(htmlspecialchars($sUsersign)),true,true);
	}

	static public function getUsericon($nUserid,$bReturnImage=true,$bReturnImageHtml=true){
		$sReturn=$sTitle='';
		if($nUserid>0){
			$arrAdmins=explode(',',$GLOBALS['_commonConfig_']['ADMIN_USERID']);
			if(in_array($nUserid,$arrAdmins)){
				$sReturn=$bReturnImage===true?__ROOT__.'/Public/images/common/usericon/online_admin.gif':3;
				$sTitle=Q::L('管理员','__COMMON_LANG__@Common');
			}else{
				$sReturn=$bReturnImage===true?__ROOT__.'/Public/images/common/usericon/online_member.gif':2;
				$sTitle=Q::L('会员','__COMMON_LANG__@Common');
			}
		}else{
			$sReturn=$bReturnImage===true?__ROOT__.'/Public/images/common/usericon/online_guest.gif':-1;
			$sTitle=Q::L('游客','__COMMON_LANG__@Common');
		}

		if($bReturnImage===true && $bReturnImageHtml=true){
			return "<img class=\"usericon_data\" src=\"{$sReturn}\" title=\"{$sTitle}\" border=\"0\" align=\"absmiddle\" />";
		}else{
			return $sReturn;
		}
	}
	
	static public function getUseronlineicon($nUserid,$bReturnImage=true,$bReturnImageHtml=true,$bReally=false){
		static $arrOnlines=array();

		if($GLOBALS['_option_']['online_on']==0){
			return '';
		}

		if(is_array($nUserid)){
			if(!empty($nUserid['online_ip'])){
				$arrOnline=$nUserid;
				$arrOnlines[$nUserid['user_id']]=$arrOnline;
			}else{
				$arrOnline=array();
			}
		}else{
			if(!isset($arrOnline[$nUserid])){
				$arrOnline=Model::F_('online','user_id=?',$nUserid)->setColumns('user_id,online_isstealth,online_ip')->getOne();
				if(empty($arrOnline)){
					$arrOnline=array();
				}else{
					$arrOnlines[$nUserid]=$arrOnline;
				}
			}else{
				$arrOnline=$arrOnlines[$nUserid];
			}
		}

		$sTitle=Q::L('用户不在线','__COMMON_LANG__@Common');
		if(!empty($arrOnline['user_id'])){
			if($arrOnline['online_isstealth']==1 && $bReally===false){
				$bOnline=false;
			}else{
				$bOnline=true;
				$sTitle=Q::L('用户在线','__COMMON_LANG__@Common');
				if($GLOBALS['_option_']['online_commonshowip']==1){
					$sTitle.=' | '.$arrOnline['online_ip'].' '.self::convertIp($arrOnline['online_ip']);
				}
			}
		}else{
			$bOnline=false;
		}

		$sReturn=$bReturnImage===true?__ROOT__.'/Public/images/common/onlineicon/'.($bOnline===true?'ol.gif':'not_ol.gif'):$bOnline;
		if($bReturnImage===true && $bReturnImageHtml===true){
			return "<img class=\"onlineicon_data\" src=\"{$sReturn}\" title=\"{$sTitle}\" border=\"0\" align=\"absmiddle\" />";
		}else{
			return $sReturn;
		}
	}

	static public function api($arrDatas,$sType=''){
		if($sType=='json'){
			header("Content-Type:text/html; charset=utf-8");
			exit(json_encode($arrDatas));
		}elseif($sType=='xml'){
			header("Content-Type:text/xml; charset=utf-8");
			exit(C::xmlEncode($arrDatas));
		}

		return;
	}

	static public function checkRbac($Node,$bAnd=false){
		static $arrMyaccesslist=array();

		if(empty($Node)){
			return false;
		}
		
		if(Core_Extend::isAdmin()){
			return true;
		}

		if(empty($arrMyaccesslist)){
			if($GLOBALS['___login___']!==false){
				$nAuthid=$GLOBALS['___login___']['user_id'];
			}else{
				$nAuthid=-1;
			}

			$arrAccesslist=Rbac::getUserRbac($nAuthid);
			foreach($arrAccesslist as $arrTemp){
				foreach($arrTemp as $arrTempTwo){
					foreach($arrTempTwo as $sKey=>$nTemp){
						$arrMyaccesslist[]=$sKey;
					}
				}
			}
		}

		if(is_string($Node)){
			$Node=array($Node);
		}else{
			$Node=(array)$Node;
		}

		if($bAnd===false){
			$bCheck=false;
			foreach($Node as $sValue){
				if(in_array($sValue,$arrMyaccesslist)){
					$bCheck=true;
					break;
				}
			}
		}

		if($bAnd===true){
			$bCheck=true;
			foreach($Node as $sValue){
				if(!in_array($sValue,$arrMyaccesslist)){
					$bCheck=false;
					break;
				}
			}
		}

		return $bCheck;
	}

	/**
	 * 后台函数生成
	 *
	 * < 参数 >
	 * < $sFmain='app/config?id=3&controller=grouptopic';
	 *   $sHeader=4; 
	 *   $sMenu='fmenu=4&fmenucurid=1&fmenutitle=testtitle' || 4; >
	 */
	static public function adminUrl($sFmain,$nFheader='',$sFmenu=''){
		if($sFmain){
			$sController=$sAction=$sExtend='';

			if(strpos($sFmain,'?')!==false){
				$arrTemp=explode('?',$sFmain);
				if(isset($arrTemp[1])){
					$sExtend=$arrTemp[1];
				}

				$sFmain=$arrTemp[0];
			}

			if(strpos($sFmain,'/')!==false){
				$arrTemp=explode('/',$sFmain);
				if(isset($arrTemp[1])){
					$sAction=$arrTemp[1];
				}

				$sFmain=$arrTemp[0];
			}

			$sController=$sFmain;

			if(empty($sAction)){
				$sAction='index';
			}

			if(empty($nFheader)){
				$nFheader=4;
			}
			
			if(empty($sFmenu)){
				$sFmenu=4;
			}
			
			return Core_Extend::windsforceOuter("fmainc={$sController}&fmaina={$sAction}".($sExtend?'&'.$sExtend:'').(!empty($nFheader)?'&fheader='.$nFheader:'').'&'.(Core_Extend::isPostInt($sFmenu)?'fmenu='.$sFmenu:$sFmenu),'user/url/admin.php');
		}else{
			return Core_Extend::windsforceOuter('','user/url/admin.php');
		}
	}

	/**
	 * Flash批量上传相关
	 */
	static public function flashuploadInit(){
		// 读取发送过来的COOKIE
		$sAuth=trim(Q::G('__auth__'));
		$sPHPSESSID=trim(Q::G('__PHPSESSID__'));

		if(!empty($sAuth)){
			session_id($sPHPSESSID);
			Q::cookie($GLOBALS['_commonConfig_']['RBAC_DATA_PREFIX'].'auth',$sAuth);
		}
	}

	static public function flashuploadAuth(){
		// 解析auth数据，判断是否存在
		$sAuth=trim(Q::G('__auth__'));

		$bSessionExists=false;
		list($nUserId,$sIsAdmin,$sPassword)=$sAuth?explode("\t",C::authCode($sAuth,true,NULL)):array('','','');
		if($nUserId && $sPassword){
			$arrUser=Model::F_('user','user_id=? AND user_password=? AND user_status=1',$nUserId,$sPassword)->setColumns('user_id,user_name')->asArray()->getOne();
			if(!empty($arrUser['user_id'])){
				$bSessionExists=true;
			}
		}

		return $bSessionExists;
	}

	static public function path($sFile,$sType='images'){
		static $sApptemplate='';

		if($sApptemplate==''){
			$sApptemplate=Q::cookie('template');
		}

		$sType=ucfirst(strtolower($sType));
		if(is_file(WINDSFORCE_PATH.'/user/theme/'.$sApptemplate.'/Public/'.$sType.'/'.$sFile)){
			return __ROOT__.'/user/theme/'.$sApptemplate.'/Public/'.$sType.'/'.$sFile;
		}else{
			return __ROOT__.'/user/theme/Default/Public/'.$sType.'/'.$sFile;
		}
	}

	static public function highlight($sContent,$sKey){
		if($sKey){
			return preg_replace("/({$sKey})/i","<b style=\"color:red\">\\1</b>",$sContent);
		}else{
			return $sContent;
		}
	}

	static public function share($sType='btn-small'){
		if($GLOBALS['_option_']['share_on']==1 && $GLOBALS['_option_']['share_code']){
			return $GLOBALS['_option_']['share_code'];
		}
	}

	static public function subString($sContent,$nNum,$bUbb=false,$nPicture=0,$nUbbformat=true,$bClean=true){
		$arrAttachment=array();

		$sContent=preg_replace("/\[attachment\]\s*(\S+?)\s*\[\/attachment\]/ise","''==\$arrAttachment[]='\\1'",$sContent);
		if($nNum===0){
			$sContent='';
		}elseif($nNum!==-1){
			if($bClean===true){
				$sContent=strip_tags(Core_Extend::ubb($sContent));
			}
			$sContent=C::closeTags(C::subString(trim($sContent),0,$nNum));
		}
		
		if($arrAttachment && $nPicture>0){
			$arrAttachment=array_slice($arrAttachment,0,$nPicture);
			if($arrAttachment){
				foreach($arrAttachment as $nAttachment){
					$sContent.=' [attachment]'.$nAttachment.'[/attachment] ';
				}
			}
		}

		if($nUbbformat===true){
			return Core_Extend::ubb($sContent,$bUbb);
		}else{
			return $sContent;
		}
	}

	static public function getMenu(){
		if($GLOBALS['_commonConfig_']['APP_MENU']){
			return (array)$GLOBALS['_commonConfig_']['APP_MENU'];
		}else{
			return array();
		}
	}

	static public function getAppucenter(){
		$arrAppcenters=array();

		if(empty($GLOBALS['_cache_']['apps'])){
			Core_Extend::loadCache('apps');
		}
		foreach($GLOBALS['_cache_']['apps'] as $arrApp){
			if(!in_array($arrApp['app_identifier'],array('wap','home'))){
				$arrAppcenters[$arrApp['app_identifier']]=$arrApp;
			}
		}
		return $arrAppcenters;
	}

	static public function sortField($sName){
		if(Q::G('order_','G')==$sName && Q::G('sort_','G')=='asc'){
			echo "class=\"order_desc\"";
		}
		if(Q::G('order_','G')==$sName && Q::G('sort_','G')=='desc'){
			echo "class=\"order_asc\"";
		}
	}

	static public function getFriendById($nUserId){
		$arrUsers=Model::F_('friend','user_id=? AND friend_status=1',$nUserId)
			->setColumns('friend_friendid')
			->getAll();

		if(!empty($arrUsers)){
			$arrUserId=array();
			foreach($arrUsers as $oUser){
				$arrUserId[]=$oUser['friend_friendid'];
			}
			return $arrUserId;
		}else{
			return array();
		}
	}

	/**
	 * IP地址解析（Modify From Discuz!）
	 *
	 * < tinyipdata.dat 简单版 >
	 * < qqwry.dat 纯正IP数据库 >
	 */
	static public function convertIp($sIp){
		$sReturn='';

		if(preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/",$sIp)){
			$arrIp=explode('.',$sIp);

			if($arrIp[0]==10 || $arrIp[0]==127 || ($arrIp[0]==192 && $arrIp[1]==168) || ($arrIp[0]==172 && ($arrIp[1]>=16 && $arrIp[1]<=31))){
				$sReturn='- LAN';
			}elseif($arrIp[0]>255 || $arrIp[1]>255 || $arrIp[2]>255 || $arrIp[3]>255){
				$sReturn='- Invalid IP Address';
			}else{
				$sTinyipFile=WINDSFORCE_PATH.'/Public/misc/ipdata/tinyipdata.dat';
				$sFullipFile=WINDSFORCE_PATH.'/Public/misc/ipdata/qqwry.dat';

				if(is_file($sFullipFile)){
					$sReturn=self::convertipFull($sIp,$sFullipFile);
				}elseif(is_file($sTinyipFile)){
					$sReturn=self::convertipTiny($sIp,$sTinyipFile);
				}
			}
		}

		return $sReturn;
	}

	static public function convertipTiny($ip,$ipdatafile){
		static $fp=NULL,$offset=array(),$index=NULL;

		$ipdot=explode('.',$ip);
		$ip=pack('N',ip2long($ip));

		$ipdot[0]=(int)$ipdot[0];
		$ipdot[1]=(int)$ipdot[1];

		if($fp===NULL && $fp=@fopen($ipdatafile,'rb')){
			$offset=@unpack('Nlen',@fread($fp,4));
			$index=@fread($fp,$offset['len']-4);
		}elseif($fp==FALSE){
			return '- Invalid IP data file';
		}

		$length=$offset['len']-1028;
		$start=@unpack('Vlen',$index[$ipdot[0]*4].$index[$ipdot[0]*4+1].$index[$ipdot[0]*4+2].$index[$ipdot[0]*4+3]);

		for($start=$start['len']*8+1024;$start<$length;$start+=8){
			if($index{$start}.$index{$start+1}.$index{$start+2}.$index{$start+3}>=$ip){
				$index_offset=@unpack('Vlen',$index{$start+4}.$index{$start+5}.$index{$start+6}."\x0");
				$index_length=@unpack('Clen',$index{$start+7});
				break;
			}
		}

		@fseek($fp,$offset['len']+$index_offset['len']-1024);
		if($index_length['len']){
			return '- '.C::gbkToUtf8(@fread($fp,$index_length['len']),'GB2312');
		}else{
			return '- Unknown';
		}
	}

	static public function convertipFull($ip,$ipdatafile){
		if(!$fd=@fopen($ipdatafile,'rb')){
			return '- Invalid IP data file';
		}

		$ip=explode('.',$ip);
		$ipNum=$ip[0]*16777216+$ip[1]*65536+$ip[2]*256+$ip[3];

		if(!($DataBegin=fread($fd,4)) || !($DataEnd=fread($fd,4))){
			return;
		}

		@$ipbegin=implode('',unpack('L',$DataBegin));
		if($ipbegin<0){
			$ipbegin+=pow(2,32);
		}

		@$ipend=implode('',unpack('L',$DataEnd));
		if($ipend<0){
			$ipend+=pow(2,32);
		}

		$ipAllNum=($ipend-$ipbegin)/7+1;

		$BeginNum=$ip2num=$ip1num=0;
		$ipAddr1=$ipAddr2='';
		$EndNum=$ipAllNum;

		while($ip1num>$ipNum || $ip2num<$ipNum){
			$Middle=intval(($EndNum+$BeginNum)/2);

			fseek($fd,$ipbegin+7*$Middle);
			$ipData1=fread($fd,4);
			if(strlen($ipData1)<4){
				fclose($fd);
				return '- System Error';
			}

			$ip1num=implode('',unpack('L',$ipData1));
			if($ip1num<0){
				$ip1num+=pow(2,32);
			}

			if($ip1num>$ipNum){
				$EndNum=$Middle;
				continue;
			}

			$DataSeek=fread($fd,3);
			if(strlen($DataSeek)<3){
				fclose($fd);
				return '- System Error';
			}

			$DataSeek=implode('',unpack('L',$DataSeek.chr(0)));
			fseek($fd,$DataSeek);
			$ipData2=fread($fd,4);
			if(strlen($ipData2)<4){
				fclose($fd);
				return '- System Error';
			}

			$ip2num=implode('',unpack('L',$ipData2));
			if($ip2num<0){
				$ip2num+=pow(2,32);
			}

			if($ip2num<$ipNum){
				if($Middle==$BeginNum){
					fclose($fd);
					return '- Unknown';
				}
				$BeginNum=$Middle;
			}
		}

		$ipFlag=fread($fd,1);
		if($ipFlag==chr(1)){
			$ipSeek=fread($fd,3);
			if(strlen($ipSeek)<3){
				fclose($fd);
				return '- System Error';
			}

			$ipSeek=implode('',unpack('L',$ipSeek.chr(0)));
			fseek($fd,$ipSeek);
			$ipFlag=fread($fd,1);
		}

		if($ipFlag==chr(2)){
			$AddrSeek=fread($fd,3);
			if(strlen($AddrSeek)<3){
				fclose($fd);
				return '- System Error';
			}

			$ipFlag=fread($fd,1);
			if($ipFlag==chr(2)){
				$AddrSeek2=fread($fd,3);
				if(strlen($AddrSeek2)<3){
					fclose($fd);
					return '- System Error';
				}

				$AddrSeek2=implode('',unpack('L',$AddrSeek2.chr(0)));
				fseek($fd,$AddrSeek2);
			} else {
				fseek($fd,-1,SEEK_CUR);
			}

			while(($char=fread($fd,1))!=chr(0))
			$ipAddr2.=$char;

			$AddrSeek=implode('',unpack('L',$AddrSeek.chr(0)));
			fseek($fd,$AddrSeek);

			while(($char=fread($fd,1))!=chr(0))
			$ipAddr1.=$char;
		}else{
			fseek($fd,-1,SEEK_CUR);
			while(($char=fread($fd,1))!=chr(0))
			$ipAddr1.=$char;

			$ipFlag=fread($fd,1);
			if($ipFlag==chr(2)){
				$AddrSeek2=fread($fd,3);
				if(strlen($AddrSeek2)< 3){
					fclose($fd);
					return '- System Error';
				}

				$AddrSeek2=implode('',unpack('L',$AddrSeek2.chr(0)));
				fseek($fd,$AddrSeek2);
			} else {
				fseek($fd,-1,SEEK_CUR);
			}
			while(($char=fread($fd,1))!=chr(0))
			$ipAddr2.=$char;
		}
		fclose($fd);

		if(preg_match('/http/i',$ipAddr2)){
			$ipAddr2='';
		}

		$ipaddr="{$ipAddr1} {$ipAddr2}";
		$ipaddr=preg_replace('/CZ88\.NET/is','',$ipaddr);
		$ipaddr=preg_replace('/^\s*/is','',$ipaddr);
		$ipaddr=preg_replace('/\s*$/is','',$ipaddr);
		if(preg_match('/http/i',$ipaddr) || $ipaddr==''){
			$ipaddr='- Unknown';
		}

		return '- '.C::gbkToUtf8($ipaddr,'GB2312');
	}

	static public function hometag($nHometagid,$nUserid=null){
		if($nUserid===null){
			$nUserid=$GLOBALS['___login___']['user_id'];
		}
	
		$oHometagindex=HometagindexModel::F('hometag_id=? AND user_id=?',$nHometagid,$nUserid)->getOne();
		if(!empty($oHometagindex['user_id'])){
			return true;
		}else{
			return false;
		}
	}
	
	static public function search(){
		$arrSearchConfigs=$arrTemps=array();

		if(empty($GLOBALS['_cache_']['app'])){
			Core_Extend::loadCache('app');
		}
		foreach($GLOBALS['_cache_']['app'] as $sApp){
			$sConfigfile=WINDSFORCE_PATH.'/System/app/'.$sApp.'/App/Config/Search.php';
			if(is_file($sConfigfile)){
				$arrTemps=array_merge($arrTemps,(array)require($sConfigfile));
			}
		}

		if(is_array($arrTemps)){
			foreach($arrTemps as $sKey=>$arrTemp){
				$arrSearchConfigs[]=array(explode('@',$sKey),$arrTemp[0],explode('|',$arrTemp[1]));
			}
		}

		return $arrSearchConfigs;
	}

	static public function getBgjs($sPath){
		$sScriptCss='';
		$arrBgimgPath=Core_Extend::getJsbg($sPath);
		if($arrBgimgPath){
			$sScriptCss.="<script type=\"text/javascript\">";
			$sScriptCss.="var globalImgbgs=[".implode(',',$arrBgimgPath)."];";
			$sScriptCss.="</script>";
		}

		return $sScriptCss;
	}

	static public function getUserrating($nScore,$bOnlyname=true){
		Core_Extend::loadCache('rating');
		
		foreach($GLOBALS['_cache_']['rating'] as $nKey=>$arrRating){
			if($nScore>=$arrRating['rating_creditstart'] && $nScore<=$arrRating['rating_creditend']){
				if($bOnlyname===true){
					return $arrRating['rating_name'];
				}else{
					$arrRating['next_rating']=$GLOBALS['_cache_']['rating'][$nKey+1];
					$arrRating['next_needscore']=$arrRating['rating_creditend']-$nScore;
					$arrRating['next_progress']=number_format(($nScore-$arrRating['rating_creditstart'])/($arrRating['rating_creditend']-$arrRating['rating_creditstart']),2)*100;
					return $arrRating;
				}
			}
		}
	}

	static public function getCountById($nUserId,$sField='usercount_extendcredit1'){
		static $arrUser=array();

		if(!isset($arrUser[$nUserId])){
			$arrUser[$nUserId]=Model::F_('usercount','user_id=?',$nUserId)->getOne();
		}
		return $arrUser[$nUserId][$sField];
	}

	static public function getUsernameById($nUserId,$sField='user_name'){
		static $arrUser=array();

		if(!isset($arrUser[$nUserId])){
			$arrData=Model::F_('user','user_id=?',$nUserId)->setColumns('user_id,user_name,user_nikename,user_email,user_sign,user_extendstyle')->getOne();
			$arrUser[$arrData['user_id']]=$arrData;
		}
		return isset($arrUser[$nUserId][$sField])?$arrUser[$nUserId][$sField]:'';
	}

	static public function replaceAttachment($sContent){
		return preg_replace("/\<img[^>].*?class=\"attachment_auto_replace\".*?alt=\"([^\"]+)\"[^>]*\>/i","[attachment]$1[/attachment]",$sContent);
	}

	/**
	 * 获取省市县乡联动列表
	 */
	static public function showDistrict($arrOption=array()){
		// 默认配置
		$arrDefaultOption=array(
			'type'=>'province,city,district,community',// 显示的联动
			'isfirst'=>'0',// 是否有请选择的选项,1表示显示
			'class'=>'',// 样式名字
			'value'=>Q::G('province_id','G').','.Q::G('city_id','G').','.Q::G('district_id','G').','.Q::G('community_id','G'),// 默认值('0,0,0,0')
			'name'=>'province_id,city_id,district_id,community_id',// 标签名字
			'id'=>'province_id,city_id,district_id,community_id',// 标签ID
			'display'=>'1,1,1,1',// 是否显示
			'isname'=>0,// Option值显示地区ID（23）还是值（四川）
			'each'=>1,// 是否直接输出
		);

		$arrOption=array_merge($arrDefaultOption,$arrOption);
		
		$arrOption['type']=explode(',',$arrOption['type']);
		$arrOption['value']=explode(',',$arrOption['value']);
		$arrOption['name']=explode(',',$arrOption['name']);
		$arrOption['id']=explode(',',$arrOption['id']);
		$arrOption['display']=explode(',',$arrOption['display']);

		if($arrOption['isname']==1){
			$sValueField='district_name';
		}else{
			$sValueField='district_id';
		}

		// 返回的结果
		$sResult='';
		$arrData=array();
		$oDistrictModel=Q::instance('DistrictModel');
		
		// 省数据
		$arrProvinceList=$oDistrictModel->getDistrict('province',null,$arrOption['isname']);

		if(!empty($arrOption['value'][0])){
			$selectProvince=$arrOption['value'][0];
		}elseif(!empty($arrOption['isfirst'])){
			$selectProvince='';
		}else{
			$selectProvince=isset($arrProvinceList[0])?$arrProvinceList[0][$sValueField]:'';
		}

		$arrData['province']['list']=&$arrProvinceList;
		$arrData['province']['selectid']=&$selectProvince;
		$arrData['province']['id']=empty($arrOption['id'][0])?'':'id='.$arrOption['id'][0];
		$arrData['province']['name']=empty($arrOption['name'][0])?'':'name='.$arrOption['name'][0];
		$arrData['province']['display']=empty($arrOption['display'][0])?$arrOption['display'][0]:1;

		// 市数据
		if(isset($arrOption['type'][1]) && $arrOption['type'][1]=='city'){
			if(!empty($selectProvince)){
				$arrCityList=$oDistrictModel->getDistrict('city',$selectProvince,$arrOption['isname']);
				$selectCity=!empty($arrOption['value'][1])?$arrOption['value'][1]:
					(isset($arrCityList[0])?$arrCityList[0][$sValueField]:''); // 默认选中城市
				$arrData['city']['list']=&$arrCityList;
				$arrData['city']['selectid']=&$selectCity;
			}else{
				$arrData['city']['list']=array();
				$arrData['city']['selectid']='';
			}

			$arrData['city']['id']=empty($arrOption['id'][1])?'':'id='.$arrOption['id'][1];
			$arrData['city']['name']=empty($arrOption['name'][1])?'':'name='.$arrOption['name'][1];
			$arrData['city']['display']=empty($arrOption['display'][1])?$arrOption['display'][1]:1;
		}

		// 区数据
		if(isset($arrOption['type'][2]) && $arrOption['type'][2]=='district'){
			if(!empty($selectCity)){
				$arrDistrictList=$oDistrictModel->getDistrict('district',$selectCity,$arrOption['isname']);
				$selectDistrict=!empty($arrOption['value'][2])?$arrOption['value'][2]:
					(isset($arrDistrictList[0])?$arrDistrictList[0][$sValueField]:'');
				$arrData['district']['list']=&$arrDistrictList;
				$arrData['district']['selectid']=&$selectDistrict;
			} else {
				$arrData['district']['list']=array();
				$arrData['district']['selectid']='';
			}

			$arrData['district']['id']=empty($arrOption['id'][2])?'':'id='.$arrOption['id'][2];
			$arrData['district']['name']=empty($arrOption['name'][2])?'':'name='.$arrOption['name'][2];
			$arrData['district']['display']=empty($arrOption['display'][2])?$arrOption['display'][2]:1;
		}

		// 乡数据
		if(isset($arrOption['type'][3]) && $arrOption['type'][3]=='community'){
			if(!empty($selectDistrict)){
				$arrCommunityList=$oDistrictModel->getDistrict('community',$selectDistrict,$arrOption['isname']);
				$selectCommunity=!empty($arrOption['value'][3])?$arrOption['value'][3]:
					(isset($arrCommunityList[0])?$arrCommunityList[0][$sValueField]:'');
				$arrData['community']['list']=&$arrCommunityList;
				$arrData['community']['selectid']=&$selectCommunity;
			} else {
				$arrData['community']['list']=array();
				$arrData['community']['selectid']='';
			}

			$arrData['community']['id']=empty($arrOption['id'][3])?'':'id='.$arrOption['id'][3];
			$arrData['community']['name']=empty($arrOption['name'][3])?'':'name='.$arrOption['name'][3];
			$arrData['community']['display']=empty($arrOption['display'][3])?$arrOption['display'][3]:1;
		}

		$arrData['isfirst']=$arrOption['isfirst'];
		$arrData['class']=$arrOption['class'];
		$arrData['isname']=$arrOption['isname'];

		if($arrOption['each']==0){
			return $arrData;
		}

		// 渲染数据
		$sResult.="<span class=\"global_districtbox\">";

		$sResult.=self::templateDistrict_($arrData,$sValueField,'province','city');
		if(isset($arrData['city'])){
			$sResult.=self::templateDistrict_($arrData,$sValueField,'city','district');
		}
		if(isset($arrData['district'])){
			$sResult.=self::templateDistrict_($arrData,$sValueField,'district','community');
		}
		if(isset($arrData['community'])){
			$sResult.=self::templateDistrict_($arrData,$sValueField,'community','none');
		}
		
		$sResult.="</span>";
		
		return $sResult;
	}

	static private function templateDistrict_($arrData,$sValueField,$sType='province',$sNext='city'){
		$sResult="
<span class=\"global_{$sType}\" ".($arrData[$sType]['display']==0?'style="display:none;"':'').">
	<select ".(isset($arrData[$sNext])?"onchange=\"getDistrict('{$sNext}',this,{$arrData['isname']})\"":'')." class=\"{$arrData['class']}\" isfirst=\"{$arrData['isfirst']}\" {$arrData[$sType]['id']} {$arrData[$sType]['name']} ".(empty($arrData[$sType]['list'])?"style=\"display:none\"":'').">";

		if($arrData['isfirst']==1){
			$sResult.="<option value=\"\">- ".Q::L('请选择','__COMMON_LANG__@Common').self::getDistrictType($sType)." -</option>";
		}

		foreach($arrData[$sType]['list'] as $arrValue){
			$sResult.="<option value=\"{$arrValue[$sValueField]}\" ".($arrValue[$sValueField]==$arrData[$sType]['selectid']?'selected':'').">{$arrValue['district_name']}</option>";
		}
		
		$sResult.="
	</select>
</span>";

		return $sResult;
	}

	public static function getDistrictByUpid($Upid,$sSort='DESC',$arrWhere=array()){
		return Q::instance('DistrictModel')->getDistrictByUpid($Upid,$sSort,$arrWhere);
	}

	public static function getDistrictType($nLevel){
		$sStr='';

		switch($nLevel){
			case 1:
			case 'province':
				$sStr.=Q::L('省份','__COMMON_LANG__@Common');
				break;
			case 2:
			case 'city':
				$sStr.=Q::L('城市','__COMMON_LANG__@Common');
				break;
			case 3:
			case 'district':
				$sStr.=Q::L('州县','__COMMON_LANG__@Common');
				break;
			case 4:
			case 'community':
				$sStr.=Q::L('乡镇','__COMMON_LANG__@Common');
				break;
		}

		return $sStr;
	}

	public static function smartDate($nTime=NULL){
		$sText='';
		$nTime=$nTime===NULL || $nTime>CURRENT_TIMESTAMP?CURRENT_TIMESTAMP:intval($nTime);
		$nT=CURRENT_TIMESTAMP-$nTime; // 时间差（秒）

		if($nT==0){
			$sText='刚刚';
		}elseif($nT<60){
			$sText=$nT.'秒前'; // 一分钟内
		}elseif($nT<60*60){
			$sText=floor($nT/60).'分钟前'; // 一小时内
		}elseif($nT<60*60*24){
			$sText=floor($nT/(60*60)).'小时前'; // 一天内
		}elseif($nT<60*60*24*3){
			$sText=floor($nTime/(60*60*24))==1?'昨天':'前天'; // 昨天和前天
		}elseif($nT<60*60*24*30){
			$sText=ceil((CURRENT_TIMESTAMP-$nTime)/(60*60*24)).'天前';
		}elseif($nT<60*60*24*365){
			$sText=ceil((CURRENT_TIMESTAMP-$nTime)/(60*60*24*30)).'个月前';
		}else{
			$sText='很久了'; // 一年以前
		}

		return $sText;
	}

	public static function endDay($nTime){
		if(empty($nTime)){
			return '永不过期';
		}
		
		$sText='';
		$nTime=$nTime-CURRENT_TIMESTAMP;
		if($nTime<0){
			$nTime=0;
		}

		if($nTime==0){
			$sText='刚刚';
		}elseif($nTime<60){
			$sText=$nTime.'秒'; // 一分钟内
		}elseif($nTime<60*60){
			$sText=floor($nTime/60).'分钟'; // 一小时内
		}elseif($nTime<60*60*24){
			$sText=floor($nTime/(60*60)).'小时'; // 一天内
		}else{
			$sText=floor($nTime/(60*60*24)).'天';
		}

		return $sText;
	}

	public static function updateOption($arrValue,$sApp=''){
		$sTable=$sApp.'option';
		foreach($arrValue as $sKey=>$sValue){
			Model::M_($sTable)->updateWhere(array($sTable.'_value'=>trim($sValue)),$sTable.'_name=?',$sKey);
		}

		if(!Q::classExists('Cache_Extend')){
			require_once(Core_Extend::includeFile('function/Cache_Extend'));
		}
		Cache_Extend::updateCache(($sApp?$sApp.'_':'').'option');
	}

	public static function isCitysite(){
		return true;
	}

	public static function getSeo(&$oController,$arrSeo,$bTitle=true){
		$arrSeo=array_merge(array('title'=>'','keywords'=>'','description'=>''),$arrSeo);
		
		// 预处理
		if($arrSeo['title']){
			if(!$arrSeo['keywords']){
				$arrSeo['keywords']=$arrSeo['title'];
			}
			if(!$arrSeo['description']){
				$arrSeo['description']=$arrSeo['title'];
			}
		}
		
		if($bTitle===true){
			$sPage=Q::G('page','G')>1?' - '.Q::L('第','__COMMON_LANG__@Common').Q::G('page','G').Q::L('页','__COMMON_LANG__@Common'):'';
			
			foreach(array('title','keywords','description') as $sValue){
				if($sValue=='title'){
					$arrSeo[$sValue].=$sPage;
				}

				// 加标题
				if($bTitle===true && $sValue!='keywords'){
					if($sValue=='title'){
						$arrSeo[$sValue].=' - '.$GLOBALS['_option_']['site_name'];
					}else{
						$arrSeo[$sValue].=' '.$GLOBALS['_option_']['site_name'].'_'.$GLOBALS['_option_']['site_description'];
					}
				}
			}
		}

		$oController->_arrSeo=$arrSeo;
	}

	public static function getEditor(){
		return $GLOBALS['_commonConfig_']['EDITOR_HEIGHT'];
	}

}

/**
 * 公共模型
 */
class CommonModel extends Model{

	const STATUS_PENDING='0';// 待审
	const STATUS_OK='1';// 通过
	const STATUS_REJECT='2';// 拒绝
	const STATUS_RECYLE='9';// 回收站
	const STATUS_RESUBMIT='11';// 修改后提交
	const STATUS_ADMIN_CLOSED='3';// 管理员关闭
	const STATUS_CLOSED='12';// 结束
	const STATUS_USER_CLOSED='13';// 用户关闭

	static function F(){
		$arrArgs=func_get_args();
		return ModelMeta::instance(__CLASS__)->findByArgs($arrArgs);
	}

	static function M(){
		return ModelMeta::instance(__CLASS__);
	}

	/**
	 * 常用字段自动填充
	 */
	protected function getIp(){
		return C::getIp();
	}

	protected function userId(){
		return !empty($GLOBALS['___login___']['user_id'])?$GLOBALS['___login___']['user_id']:0;
	}
	
	protected function userName(){
		return !empty($GLOBALS['___login___']['user_name'])?$GLOBALS['___login___']['user_name']:'';
	}

	/**
	 * 通用字段验证
	 */
	protected function uniqueField_($sField,$sPk,$sValue){
		$nId=Q::G($sValue);
		$sModel=substr($sPk,0,strpos($sPk,'_'));

		$arrWhere[$sField]=trim(Q::G($sField));
		if($nId){
			$arrWhere[$sPk]=array('neq',$nId);
		}

		$oResult=Model::F_($sModel)->where($arrWhere)->setColumns($sPk)->getOne();
		if(!empty($oResult[$sPk])){
			return false;
		}

		return true;
	}

}

/**
 * 前台公共控制器
 */
class InitController extends PController{
	
	/**
	 * SEO信息
	 */
	public $_arrSeo=array('title'=>'','keywords'=>'','description'=>'');

	public function init__(){
		parent::init__();

		// 应用配置
		if(file_exists(APP_PATH.'/App/Class/Model/'.ucfirst(APP_NAME).'optionModel.class.php')){
			Core_Extend::loadCache(APP_NAME.'_option');
		}
		
		// 前端初始化
		Core_Extend::initFront();
		
		// RBAC
		if(!Rbac::checkRbac($GLOBALS['___login___'])){
			$this->E(Rbac::getErrorMessage());
		}

		// 404
		Core_Extend::page404($this);
	}
	
	public function is_login(){
		if($GLOBALS['___login___']===false){
			$this->assign('__JumpUrl__',Q::U('home://public/login'));
			$this->E(Q::L('你没有登录','__COMMON_LANG__@Common'));
		}
	}

	public function seccode(){
		Core_Extend::seccode();
	}
	
	public function check_seccode($bSubmit=false){
		$sSeccode=Q::G('seccode');
		
		$bResult=Core_Extend::checkSeccode($sSeccode);
		if(!$bResult){
			$this->E(Q::L('你输入的验证码错误','__COMMON_LANG__@Common'));
		}
		
		if($bSubmit===false){
			$this->S(Q::L('验证码正确','__COMMON_LANG__@Common'));
		}
	}
	
	public function page404(){
		header("HTTP/1.0 404 Not Found");
		$this->display('404');
		exit();
	}
	
	protected function E($sMessage='',$nDisplay=3,$bAjax=FALSE){
		if(Q::G('dialog')==1){
			$this->dialog_message($sMessage);
		}else{
			parent::E($sMessage,$nDisplay,$bAjax);
		}
	}
	
	protected function S($sMessage,$nDisplay=1,$bAjax=FALSE){
		if(Q::G('dialog')==1 && !C::isAjax()){
			$this->dialog_message($sMessage);
		}else{
			parent::S($sMessage,$nDisplay,$bAjax);
		}
	}
	
	public function dialog_message($sMessage){
		$this->assign('sMessage',$sMessage);
		$this->display('dialog_message');
		exit();
	}
	
}

/**
 * Wap公用控制器
 */
class WInitController extends Controller{

	public function init__(){

		exit('WAP应用不可用，准备重构');

		// 配置&登陆信息
		Core_Extend::loadCache('option');
		Core_Extend::loginInformation();
		
		// 应用配置
		if(Q::classExists(ucfirst(APP_NAME).'optionModel')){
			Core_Extend::loadCache(APP_NAME.'_option');
		}

		if($GLOBALS['_option_']['wap_computer_on']==1){
			if(preg_match('/(mozilla|m3gate|winwap|openwave)/i',$_SERVER['HTTP_USER_AGENT'])){
				C::urlGo(__ROOT__.'/index.php');
			}
		}

		if($GLOBALS['_option_']['wap_mobile_only']==1){
			header("Content-type: text/vnd.wap.wml; charset=utf-8");
		}

		// RBAC
		if(!Rbac::checkRbac($GLOBALS['___login___'])){
			$this->wap_mes(Rbac::getErrorMessage(),'',0);
		}
		

		// 404
		Core_Extend::page404($this);

		$this->init();
	}

	protected function init(){
		if($GLOBALS['_option_']['close_site']==1 && !Core_Extend::isAdmin()){
			$this->assign('__JumpUrl__',Q::U('wap://public/index'));
			$this->wap_mes($GLOBALS['_option_']['close_site_reason'],'',0);
		}

		if($GLOBALS['_option_']['wap_on']==0 && !Core_Extend::isAdmin()){
			$this->assign('__JumpUrl__',Q::U('wap://public/index'));
			$this->wap_mes($GLOBALS['_option_']['wap_close_reason'],'',0);
		}
		
		// 必须要登陆才能够访问内容
		if($GLOBALS['_option_']['only_login_viewsite']==1 && $GLOBALS['___login___']===false){
			if(!(APP_NAME==='wap' && MODULE_NAME==='public' && !in_array(ACTION_NAME,array('index')))){
				C::urlGo(Q::U('wap://public/login',array('referer'=>__SELF__,'loginview'=>1),true));
			}
		}
	}

	public function page404(){
		header("HTTP/1.0 404 Not Found");
		$this->wap_mes('404 未找到','',0);

		exit();
	}

	public function wap_mes($sMsg,$sLink='',$nStatus=1){
		if(empty($sLink)){
			$sLink=Q::U('wap://public/index');
		}
		
		$this->assign('__JumpUrl__',$sLink);
		$this->assign('__Message__',$sMsg);
		$this->assign('nStatus',$nStatus);

		$this->display(WINDSFORCE_PATH.'/System/app/wap/Theme/Default/message.html');

		exit();
	}

	public function is_login(){
		if($GLOBALS['___login___']===false){
			$this->assign('__JumpUrl__',Q::U('wap://public/login'));
			$this->wap_mes(Q::L('你没有登录','__COMMON_LANG__@Common'),'',0);
		}
	}

}

/**
 * 记录在线时间
 */
class Online{

	protected static $_arrData=array();
	protected static $_bNew=false;

	public static function RUN(){
		static $bUpdate=false;

		// 搜索引擎
		if(Core_Extend::isCrawler()){
			return;
		}

		if(!$bUpdate){
			$sHash=Q::cookie($GLOBALS['_commonConfig_']['RBAC_DATA_PREFIX'].'hash');
			self::create(!$sHash?true:false,$sHash);

			// 更新
			self::update();
			$bUpdate=true;
		}

		return $bUpdate;
	}

	public static function create($bNew=true,$sHash=''){
		if($bNew===true){
			self::$_bNew=true;
			self::$_arrData['online_hash']=C::randString(6);
		}else{
			self::$_arrData['online_hash']=$sHash;
		}

		self::$_arrData['online_ip']=C::getIp();
		self::$_arrData['online_activetime']=CURRENT_TIMESTAMP;
		if(MODULE_NAME!=='api' && APP_NAME.'@'.MODULE_NAME!='home@misc' && APP_NAME.'@'.MODULE_NAME.'@'.ACTION_NAME!='home@misc@ubb'){
			self::$_arrData['online_atpage']=APP_NAME.'@'.MODULE_NAME.'@'.ACTION_NAME;
		}

		if($GLOBALS['___login___']!==false){
			self::$_arrData['user_id']=$GLOBALS['___login___']['user_id'];
			self::$_arrData['online_username']=$GLOBALS['___login___']['user_name'];
		}else{
			self::$_arrData['user_id']=0;
			self::$_arrData['online_username']='';
		}

		return self::$_arrData; 
	}

	private static function update(){
		if(!empty(self::$_arrData['online_hash'])){
			if(self::$_bNew){
				// 最大在线人数检测
				$nOnlinenum=Model::F_('online')->all()->getCounts();
				if($GLOBALS['_option_']['online_mostnum']>0 && $nOnlinenum>$GLOBALS['_option_']['online_mostnum']){
					Q::E(Q::L('当前在线人数 %d 超过了网站最大负载量 %d','__COMMON_LANG__@Common',null,$nOnlinenum,$nOnlinemostnum));
				}
				
				self::delete();
				self::onlineDb(self::$_arrData,true);
			}else{
				self::onlineDb(self::$_arrData);
			}

			Q::cookie($GLOBALS['_commonConfig_']['RBAC_DATA_PREFIX'].'hash',self::$_arrData['online_hash'],86400);
		}
	}

	private static function onlineDb($arrData,$bInsert=false){
		if($bInsert===true){
			$oOnline=new OnlineModel();
			$oOnline->changeProp($arrData);
			$oOnline->save();
		}else{
			if(Model::F_('online','online_hash=?',$arrData['online_hash'])->getOne()){
				Model::M_('online')->updateWhere($arrData,'online_hash=?',$arrData['online_hash']);
			}else{
				self::onlineDb($arrData,true);
			}
		}
	}

	private static function delete(){
		// 清理过期在线用户数据 && 记录会员的在线时间
		$arrOnlines=Model::F_('online','user_id>0 AND online_activetime<?',(CURRENT_TIMESTAMP-$GLOBALS['_option_']['online_keeptime']*60))->setColumns('online_isstealth,online_activetime,create_dateline,user_id')->getAll();
		if(is_array($arrOnlines)){
			foreach($arrOnlines as $oValue){
				if($GLOBALS['_option_']['online_stealtholtime']==0 && $oValue['online_isstealth']==1){
					continue;
				}

				// 写入数据
				$nOnlinetime=$oValue['online_activetime']-$oValue['create_dateline'];
				$nOnlinetime=round($nOnlinetime/3600,1);
				$oUsercount=UsercountModel::F('user_id=?',$oValue['user_id'])->getOne();
				$oUsercount->usercount_oltime=$oUsercount->usercount_oltime+$nOnlinetime;
				$oUsercount->save('update');
			}
		}

		$oDb=Db::RUN();
		$oDb->query('DELETE FROM '.$GLOBALS['_commonConfig_']['DB_PREFIX'].'online WHERE online_activetime<'.(CURRENT_TIMESTAMP-$GLOBALS['_option_']['online_keeptime']*60));
	}

}

/**
 * 前台加载资源
 */
class Apptheme_Extend{
	
	static public function path($sFile,$sApp='home',$bAdmin=false){
		static $sApptemplate='';

		if($sApptemplate==''){
			$sApptemplate=Q::cookie('template');
		}

		if(is_file(WINDSFORCE_PATH.'/System/app/'.$sApp.'/Theme/'.$sApptemplate.'/Public/Images/'.$sFile)){
			if($bAdmin===false){
				return __TMPLPUB__.'/Images/'.$sFile;
			}else{
				return __ROOT__.'/System/app/'.$sApp.'/Theme/'.$sApptemplate.'/Public/Images/'.$sFile;
			}
		}else{
			if($bAdmin===false){
				return __TMPLPUB_DEFAULT__.'/Images/'.$sFile;
			}else{
				return __ROOT__.'/System/app/'.$sApp.'/Theme/Default/Public/Images/'.$sFile;
			}
		}
	}
	
}

/** 简化模板中的调用 */
class Appt extends Apptheme_Extend{}

/**
 * 附件相关函数
 */
class Attachment_Extend{

	static public function getAttachmenttype($arrAttachment){
		$arrAttachmentTypes=array(
			'img'=>array('jpg','jpeg','gif','png','bmp'),
			'swf'=>array('swf'),
			'wmp'=>array('wma','asf','wmv','avi','wav'),
			'mp3'=>array('mp3'),
			'qvod'=>array('rm','rmvb','ra','ram'),
			'flv'=>array('flv','mp4'),
			'url'=>array('html','htm','txt'),
			'download'=>array(),
		);
		
		if(is_string($arrAttachment)){
			$sAttachmentExtension=C::getExtName($arrAttachment,2);
		}else{
			$sAttachmentExtension=$arrAttachment['attachment_extension'];
		}
		
		foreach($arrAttachmentTypes as $sKey=>$arrAttachmentType){
			if(in_array($sAttachmentExtension,$arrAttachmentType)){
				return $sKey;
			}
		}
		
		return 'download';
	}

	static public function getAttachmenturl($arrAttachment,$bThumb=false,$bUrlpath=true){
		if($bUrlpath===false){
			return self::getPrefix(true).self::getAttachmenturl_($arrAttachment,$bThumb);
		}

		if(self::attachmentHidereallypath($arrAttachment)){
			return self::getSiteurl().'/index.php?app=home&c=attachmentread&a=index&id='.Core_Extend::aidencode($arrAttachment['attachment_id']).($arrAttachment['attachment_isthumb'] && $bThumb===true?'&thumb=1':'');
		}else{
			return self::getPrefix().self::getAttachmenturl_($arrAttachment,$bThumb);
		}
	}

	static public function getAttachmenturl_($arrAttachment,$bThumb=true){
		return ($arrAttachment['attachment_isthumb'] && $bThumb===true?
				$arrAttachment['attachment_thumbpath'].'/'.$arrAttachment['attachment_thumbprefix']:
				$arrAttachment['attachment_savepath'].'/').$arrAttachment['attachment_savename'];
	}

	static public function attachmentHidereallypath($arrAttachment){
		return ($arrAttachment['attachment_isimg']==1 && $GLOBALS['_option_']['upload_img_ishide_reallypath']) || 
			($arrAttachment['attachment_isimg']==0 && $GLOBALS['_option_']['upload_ishide_reallypath']);
	}

	static public function getAttachmentPreview($arrAttachment,$bUrlpath=true){
		if(empty($arrAttachment['attachment_id'])){
			$arrAttachment=Model::F_('attachment','attachment_id=?',$arrAttachment)->getOne();
		}
		
		if(empty($arrAttachment['attachment_id'])){
			if($bUrlpath===true){
				return __PUBLIC__.'/images/common/none.gif';
			}else{
				return WINDSFORCE_PATH.'/Public/images/common/none.gif';
			}
		}

		$sAttachmentPreview=self::getFileicon($arrAttachment['attachment_extension'],false,true,$bUrlpath);
		if($sAttachmentPreview===true){
			return self::getAttachmenturl($arrAttachment,true,$bUrlpath);
		}else{
			return $sAttachmentPreview;
		}
	}

	static public function getAttachmentcategoryPreview($arrAttachmentcategory,$bUrlpath=true){
		if(empty($arrAttachmentcategory['attachmentcategory_id'])){
			$arrAttachmentcategory=Model::F_('attachmentcategory','attachmentcategory_id=?',$arrAttachmentcategory)->getOne();
		}
		
		if(empty($arrAttachmentcategory['attachmentcategory_id']) || !$arrAttachmentcategory['attachmentcategory_cover']){
			if($bUrlpath===true){
				return __PUBLIC__.'/images/common/default_attachmentcategory.png';
			}else{
				return WINDSFORCE_PATH.'/Public/images/common/default_attachmentcategory.png';
			}
		}

		// 已设置封面
		if($arrAttachmentcategory['attachmentcategory_cover']){
			return self::getPrefix($bUrlpath?false:true).$arrAttachmentcategory['attachmentcategory_cover'];
		}
	}

	static public function getFileicon($sExtension,$bReturnImageIcon=false,$bReturnPath=true,$bUrlpath=true){
		$arrIcons=array(
			'image'=>array('gif','jpg','jpeg','bmp','png'),
			'archive'=>array('zip','z','gz','gtar','rar'),
			'audio'=>array('aif','aifc','aiff','au','kar','m3u','mid','midi',
						'mp2','mp3','mpga','ra','ram','rm','rpm','snd','wav',
						'wax','wma','aac'),
			'video'=>array('asf','asx','avi','mov','movie','mpeg','mpe','mpg',
						'mxu','qt','wm','wmv','wmx','wvx','rmvb','flv','mp4'),
			'document'=>array('doc','pdf','ppt'),
			'text'=>array('txt','ascii','mime'),
			'spreadsheet'=>array('xls','et'),
			'interactive'=>array('as','flash'),
			'code'=>array('h','c','h','cpp','dfm','pas','frm','vbs','asp','jsp','java','class','php'),
			'default'=>array(),
		);

		$sFileiconPath='';
		foreach($arrIcons as $sKey=>$arrIcon){
			if(in_array($sExtension,$arrIcon)){
				$sFileiconPath=$sKey;
				break;
			}
		}

		if(empty($sFileiconPath)){
			$sFileiconPath='default';
		}

		if($sFileiconPath=='image' && $bReturnImageIcon===false){
			return true;
		}

		if($bReturnPath===true){
			if($bUrlpath===true){
				return __PUBLIC__.'/images/crystal/'.$sFileiconPath.'.png';
			}else{
				return WINDSFORCE_PATH.'/Public/images/crystal/'.$sFileiconPath.'.png';
			}
		}else{
			return $sFileiconPath;
		}
	}

	static public function getAttachmentcategory($nUserid=null){
		if($nUserid===null){
			$nUserid=$GLOBALS['___login___']['user_id'];
		}
		$oAttachmentcategory=Q::instance('AttachmentcategoryModel');
		return $oAttachmentcategory->getAttachmentcategoryByUserid($nUserid);
	}

	static public function getAllowedtype(){
		return explode('|',$GLOBALS['_option_']['upload_allowed_type']);
	}

	static public function getPrefix($bFull=false){
		return ($bFull===false?self::getSiteurl('img').'/':WINDSFORCE_PATH.'/user/attachment/');
	}

	static public function getSiteurl($sPrefix='www'){
		return Core_Extend::getSiteurl($sPrefix);
	}

}

/**
 * Ubb代码解析
 */
class Ubb2html{
	
	public $_sContent='';
	public $_sLoginurl='';
	public $_sRegisterurl='';
	public $_nOuter=0;
	public $_bHomefreshmessage=true;

	public function __construct($arrData=array()){
		if(isset($arrData[0])){
			$this->_sContent=$arrData[0];
		}

		$this->_sLoginurl=Core_Extend::windsforceOuter('app=home&c=public&a=login&referer='.urlencode(__SELF__));
		$this->_sRegisterurl=Core_Extend::windsforceOuter('app=home&c=public&a=register&referer='.urlencode(__SELF__));

		if(isset($arrData[1])){
			$this->_bHomefreshmessage=$arrData[1];
		}

		if(isset($arrData[2])){
			$this->_nOuter=$arrData[2];
		}

		if(APP_NAME==='wap' || (defined('IN_WAP') && IN_WAP===true)){
			$this->_nOuter=1;
		}
	}

	public function convert($sContent=null){
		if($sContent===null){
			$sContent=$this->_sContent;
		}

		// 解析隐藏标签
		if($GLOBALS['___login___']===false){
			$sContent=preg_replace(
				"/\[hide\](.+?)\[\/hide\]/is",
				$this->needLogin(),
				$sContent
			);
		}else{
			$sContent=str_replace(array('[hide]','[/hide]'),'',$sContent);
		}

		// 解析特殊标签
		$sContent=str_replace(array('{','}'),array('&#123;','&#125;'),$sContent);
		
		// 换行和分割线
		$arrBasicUbbSearch=array('[hr]','<br>','[br]');
		$arrBasicUbbReplace=array('<hr/>','<br/>','<br/>');
		$sContent=str_replace($arrBasicUbbSearch,$arrBasicUbbReplace,$sContent);
		
		// URL和图像标签
		$sContent=preg_replace(
			"/\[url=([^\[]*)\]\[img(align=L| align=M| align=R)?(width=[0-9]+)?(height=[0-9]+)?\]\s*(\S+?)\s*\[\/img\]\[\/url\]/ise",
			"\$this->makeimgWithurl('\\1','\\2','\\3','\\4','\\5')",
			$sContent
		);
		
		$sContent=preg_replace(
			"/\[img(align=L| align=M| align=R)?(width=[0-9]+)?(height=[0-9]+)?\]\s*(\S+?)\s*\[\/img\]/ise",
			"\$this->makeImg('\\1','\\2','\\3','\\4')",
			$sContent
		);
		
		if($GLOBALS['_option_']['ubb_content_autoaddlink']==1){
			$sContent=preg_replace("/(?<=[^\]a-z0-9-=\"'\\/])((https?|ftp|gopher|news|telnet|rtsp|mms|callto|ed2k):\/\/|www\.)([a-z0-9\/\-_+=.~!%@?#%&;:$\\()|]+)/i","[autourl]\\1\\3[/autourl]",$sContent);
		}
		
		$arrRegUbbSearch=array(
			"/\[size=([^\[\<]+?)\](.+?)\[\/size\]/ie",
			"/\s*\[quote\][\n\r]*(.+?)[\n\r]*\[\/quote\]\s*/is",
			"/\s*\[quote=(.+?)\][\n\r]*(.+?)[\n\r]*\[\/quote\]\s*/is",
			"/\s*\[code\][\n\r]*(.+?)[\n\r]*\[\/code\]\s*/ie",
			"/\[autourl\]([^\[]*)\[\/autourl\]/ie",
			"/\[url\]([^\[]*)\[\/url\]/ie",
			"/\[url=([^\[]*)\](.+?)\[\/url\]/ie",
			"/\[email\]([^\[]*)\[\/email\]/is",
			"/\[acronym=([^\[]*)\](.+?)\[\/acronym\]/is",
			"/\[color=([a-zA-Z0-9#]+?)\](.+?)\[\/color\]/i",
			"/\[font=([^\[\<:;\(\)=&#\.\+\*\/]+?)\](.+?)\[\/font\]/i",
			"/\[p align=([^\[\<]+?)\](.+?)\[\/p\]/i",
			"/\[b\](.+?)\[\/b\]/i",
			"/\[i\](.+?)\[\/i\]/i",
			"/\[u\](.+?)\[\/u\]/i",
			"/\[blockquote\](.+?)\[\/blockquote\]/i",
			"/\[strong\](.+?)\[\/strong\]/i",
			"/\[strike\](.+?)\[\/strike\]/i",
			"/\[sup\](.+?)\[\/sup\]/i",
			"/\[sub\](.+?)\[\/sub\]/i",
			"/\s*\[php\][\n\r]*(.+?)[\n\r]*\[\/php\]\s*/ie",
			"/\[fly\](.+?)\[\/fly\]/i",
		);
		
		$arrRegUbbReplace=array(
			"\$this->makeFontsize('\\1','\\2')",
			$this->template(Q::L('引用','__COMMON_LANG__@Common'),"\\1"),
			$this->template(Q::L('引用自','__COMMON_LANG__@Common')." \\1","\\2"),
			"\$this->makeCode('\\1')",
			"\$this->makeUrl('\\1',1)",
			"\$this->makeUrl('\\1','0')",
			"\$this->makeUrl('\\1','0','\\2')",
			"<a href=\"mailto:\\1\">\\1</a>",
			"<acronym title=\"\\1\">\\2</acronym>",
			"<span style=\"color: \\1;\">\\2</span>",
			"<span style=\"font-family: \\1;\">\\2</span>",
			"<p align=\"\\1\">\\2</p>",
			"<strong>\\1</strong>",
			"<em>\\1</em>",
			"<u>\\1</u>",
			"<blockquote>\\1</blockquote>",
			"<strong>\\1</strong>",
			"<del>\\1</del>",
			"<sup>\\1</sup>",
			"<sub>\\1</sub>",
			"\$this->xhtmlHighlightString('\\1')",
			"<marquee scrollamount=\"3\" behavior=\"alternate\" width=\"90%\">\\1</marquee>",
		);
		
		$sContent=preg_replace($arrRegUbbSearch,$arrRegUbbReplace,$sContent);
		
		// 解析上传附件
		$sContent=preg_replace("/\[attachment\]\s*(\S+?)\s*\[\/attachment\]/ise","\$this->makeAttachment('\\1','{$this->_nOuter}')",$sContent);

		// 解析音乐和视频格式
		$sContent=preg_replace("/\[mp3\]\s*(\S+?)\s*\[\/mp3\]/ise","\$this->makeMp3('\\1')",$sContent);
		$sContent=preg_replace("/\[video\]\s*(\S+?)\s*\[\/video\]/ise","\$this->makeVideo('\\1')",$sContent);

		// 解析话题和@user_name
		$sContent=preg_replace("/\[TAG\]#\s*(\S+?)\s*#\[\/TAG\]/ise","\$this->makeTag('\\1')",$sContent);
		$sContent=preg_replace("/\[MESSAGE\]@\s*(\S+?)\s*\[\/MESSAGE\]/ise","\$this->makeMessage('\\1')",$sContent);

		return $sContent;
	}

	public function convertUsersign($sContent=null){
		if($sContent===null){
			$sContent=$this->_sContent;
		}

		// 解析特殊标签
		$sContent=str_replace(array('{','}'),array('&#123;','&#125;'),$sContent);

		// 换行和分割线
		$arrBasicUbbSearch=array('[hr]','[br]');
		$arrBasicUbbReplace=array('<hr/>','<br/>');
		$sContent=str_replace($arrBasicUbbSearch,$arrBasicUbbReplace,$sContent);

		// URL和图像标签
		$sContent=preg_replace(
			"/\[url=([^\[]*)\]\[img(align=L| align=M| align=R)?(width=[0-9]+)?(height=[0-9]+)?\]\s*(\S+?)\s*\[\/img\]\[\/url\]/ise",
			"\$this->makeimgWithurl('\\1','\\2','\\3','\\4','\\5')",
			$sContent
		);
		
		$sContent=preg_replace(
			"/\[img(align=L| align=M| align=R)?(width=[0-9]+)?(height=[0-9]+)?\]\s*(\S+?)\s*\[\/img\]/ise",
			"\$this->makeImg('\\1','\\2','\\3','\\4')",
			$sContent
		);

		$sContent=preg_replace("/(?<=[^\]a-z0-9-=\"'\\/])((https?|ftp|gopher|news|telnet|rtsp|mms|callto|ed2k):\/\/|www\.)([a-z0-9\/\-_+=.~!%@?#%&;:$\\()|]+)/i","[autourl]\\1\\3[/autourl]",$sContent);

		$arrRegUbbSearch=array(
			"/\[autourl\]([^\[]*)\[\/autourl\]/ie",
			"/\[url\]([^\[]*)\[\/url\]/ie",
			"/\[url=([^\[]*)\](.+?)\[\/url\]/ie",
			"/\[email\]([^\[]*)\[\/email\]/is",
			"/\[color=([a-zA-Z0-9#]+?)\](.+?)\[\/color\]/i",
			"/\[b\](.+?)\[\/b\]/i",
			"/\[i\](.+?)\[\/i\]/i",
			"/\[u\](.+?)\[\/u\]/i",
			"/\[strike\](.+?)\[\/strike\]/i",
			"/\[sup\](.+?)\[\/sup\]/i",
			"/\[sub\](.+?)\[\/sub\]/i",
		);
		
		$arrRegUbbReplace=array(
			"\$this->makeUrl('\\1',1)",
			"\$this->makeUrl('\\1','0')",
			"\$this->makeUrl('\\1','0','\\2')",
			"<a href=\"mailto:\\1\">\\1</a>",
			"<span style=\"color: \\1;\">\\2</span>",
			"<strong>\\1</strong>",
			"<em>\\1</em>",
			"<u>\\1</u>",
			"<del>\\1</del>",
			"<sup>\\1</sup>",
			"<sub>\\1</sub>",
		);
		
		$sContent=preg_replace($arrRegUbbSearch,$arrRegUbbReplace,$sContent);
		return $sContent;
	}


	public function makeimgWithurl($sUrl,$sAlignCode,$sWidthCode,$sHeightCode,$sSrc){
		return $this->makeImg($sAlignCode,$sWidthCode,$sHeightCode,$sSrc,$sUrl);
	}

	public function makeTag($sTag){
		$sUrl='home://ucenter/index';
		return '<a href="'.Q::U($sUrl,array('key'=>$sTag),true).'">#'.$sTag.'#</a>';
	}

	public function makeMessage($sMessage){
		if($this->_bHomefreshmessage===true){
			$sUrl=Q::U('home://ucenter/index',array('at'=>$sMessage),true);
		}else{
			$sUrl=Q::U('home://space/index?id='.urlencode($sMessage),array(),true);
		}
		return '<a href="'.$sUrl.'"'.($this->_bHomefreshmessage===true?'':' target="_blank"').'>@'.$sMessage.'</a>';
	}

	public function makeMp3($sSrc){
		$sExtName=C::getExtName($sSrc);
		if(!in_array($sExtName,array('mp3','wma','wav'))){
			return $sSrc;
		}
		if($sExtName!='mp3'){
			$sExtName='wmp';
		}

		return call_user_func(array($this,'music'.ucfirst($sExtName)),$sSrc);
	}

	public function musicMp3($sSrc){
		return $this->makeMedia($sSrc,'mp3.gif',Q::L('Mp3文件','__COMMON_LANG__@Common'),'mp3',0,'',240,20);
	}

	public function musicWmp($sSrc){
		return $this->makeMedia($sSrc,'wmp.gif',Q::L('Windows Media Player文件','__COMMON_LANG__@Common'),'wmp');
	}

	public function makeVideo($sSrc){
		$sExtName=C::getExtName($sSrc);

		if(!in_array($sExtName,array('swf','asf','wmv','avi','rm','rmvb','flv','mp4'))){
			return $sSrc;
		}
		if(in_array($sExtName,array('asf','wmv','avi'))){
			$sExtName='wmp';
		}
		if(in_array($sExtName,array('rm','rmvb'))){
			$sExtName='qvod';
		}
		if(in_array($sExtName,array('flv','mp4'))){
			$sExtName='flv';
		}

		return call_user_func(array($this,'video'.ucfirst($sExtName)),$sSrc);
	}

	public function videoSwf($sSrc){
		return $this->makeMedia($sSrc,'swf.gif',Q::L('Flash Player文件','__COMMON_LANG__@Common'),'swf');
	}

	public function videoWmp($sSrc){
		return $this->makeMedia($sSrc,'wmp.gif',Q::L('Windows Media Player文件','__COMMON_LANG__@Common'),'wmp');
	}

	public function videoQvod($sSrc,$sExtName){
		return $this->makeMedia($sSrc,'qvod.gif',Q::L('QVOD视频播放器','__COMMON_LANG__@Common'),'qvod');
	}

	public function videoFlv($sSrc,$sExtName){
		return $this->makeMedia($sSrc,'swf.gif',Q::L('Flash Video Player文件','__COMMON_LANG__@Common'),'flv');
	}

	public function makeImg($sAlignCode,$sWidthCode,$sHeightCode,$sSrc,$sUrl=''){
		if(empty($sUrl)){
			$sUrl=$sSrc;
		}
		
		// 对齐
		$sAlign=str_replace(' align=','',strtolower($sAlignCode));
		if($sAlign=='l'){
			$sShow=' align="left"';
		}elseif($sAlign=='r'){
			$sShow=' align="right"';
		}else{
			$sShow='';
		}
		
		// 宽度&高度
		$nWidth=str_replace(' width=','',strtolower($sWidthCode));
		if(!empty($nWidth)){
			$sShow.=' width="'.$nWidth.'"';
		}
		
		$nHeight=str_replace(' height=','',strtolower($sHeightCode));
		if(!empty($nHeight)){
			$sShow.=' height="'.$nHeight.'"';
		}
		
		return "<a href=\"{$sUrl}\" target=\"_blank\"><img src=\"{$sSrc}\" class=\"content-insert-image need_lazyload\" alt=\"".Q::L('在新窗口浏览此图片','__COMMON_LANG__@Common')."\" title=\"".Q::L('在新窗口浏览此图片','__COMMON_LANG__@Common')."\" border=\"0\" {$sShow}/></a>";
	}
	
	public function makeFontsize($nSize,$sWord){
		$nSizeItem=array(0,8,10,12,14,18,24,36);
		return "<span style=\"font-size:{$nSizeItem[$nSize]}px;\">{$sWord}</span>";
	}
	
	public function makeCode($sStr){
		$sStr=str_replace(array('[autourl]','[/autourl]'),array('',''),$sStr);
		return $this->template(Q::L('代码','__COMMON_LANG__@Common'),$sStr,'ubb_code');
	}
	
	public function makeUrl($sUrl,$nAutolink=0,$sLinkText=''){
		if($nAutolink==1){
			$sGoToRealLink=Core_Extend::windsforceOuter('app=home&c=public&a=url&go='.(substr(strtolower($sUrl),0,4)=='www.'?urlencode("http://{$sUrl}"):urlencode($sUrl)));
		}else{
			$sGoToRealLink=substr(strtolower($sUrl),0,4)=='www.'?"http://{$sUrl}":$sUrl;
		}
		
		$sUrlLink="<a href=\"{$sGoToRealLink}\" target=\"_blank\">";
		if(!empty($sLinkText)){
			$sUrl=$sLinkText;
		}else{
			if($GLOBALS['_option_']['ubb_content_shorturl'] && strlen($sUrl)>$GLOBALS['_option_']['ubb_content_urlmaxlen']){
				$nHalfMax=floor($GLOBALS['_option_']['ubb_content_urlmaxlen']/2);
				$sUrl=substr($sUrl,0,$nHalfMax).'...'.substr($sUrl,0-$nHalfMax);
			}
		}
		$sUrlLink.=$sUrl.'</a>';
		return $sUrlLink;
	}
	
	public function xhtmlHighlightString($sStr){
		$sHlt=@highlight_string($sStr,true);
		if(PHP_VERSION>'5'){
			return $this->template(Q::L('代码','__COMMON_LANG__@Common'),$sHlt,'ubb_code');
		}
		
		$sFon=str_replace(array('<font ','</font>'),array('<span ','</span>'),$sHlt);
		$sRet=preg_replace('#color="(.*?)"#','style="color: \\1"',$sFon);
		return $this->template(Q::L('代码','__COMMON_LANG__@Common'),$sHlt,'ubb_code');
	}

	public function makeAttachment($nId){
		if(!preg_match("/[^\d-.,]/",$nId)){
			$oAttachment=$this->getAttachment($nId);
			if($oAttachment===false){
				return '';
			}
			$sType=Attachment_Extend::getAttachmenttype($oAttachment);
			return call_user_func(array($this,'attachment'.ucfirst($sType)),$oAttachment,$this->_nOuter);
		}else{
			$sType=Attachment_Extend::getAttachmenttype($nId);
			if(strpos($nId,'http://')===FALSE && strpos($nId,'https://')===FALSE){
				$nId=Attachment_Extend::getPrefix().$nId;
			}
			return call_user_func(array($this,'attachment'.ucfirst($sType).'_'),$nId);
		}
	}

	public function attachmentImg($oAttachment,$nOuter=0){
		$sImg=Attachment_Extend::getAttachmenturl($oAttachment);
		if(APP_NAME==='wap' || (defined('IN_WAP') && IN_WAP===true)){
			$nOuter=0;
		}

		if($nOuter==0){
			if($GLOBALS['_option_']['upload_loginuser_view']==1 && $GLOBALS['___login___']===FALSE){
				return $this->needLogin();
			}else{
				if(APP_NAME==='wap' || (defined('IN_WAP') && IN_WAP===true)){
					$sImg=Core_Extend::wapImage($oAttachment['attachment_id']);
					return " <img src=\"{$sImg}\" class=\"content-insert-image\" alt=\"{$oAttachment['attachment_name']}\" title=\"".$oAttachment['attachment_name']."\" border=\"0\"> ";
				}

				$sTips=$this->commonTips($oAttachment,'viewimage.gif');
				$sContent="<a onclick=\"updateDownload('".$oAttachment['attachment_id']."');\" href=\"{$sImg}\" target=\"_blank\" title=\"{$oAttachment['attachment_name']}\"><img src=\"{$sImg}\" class=\"content-insert-image need_lazyload\" alt=\"{$oAttachment['attachment_name']}\" border=\"0\" tips='{$sTips}'/></a>";
				return $this->template($sContent);
			}
		}else{
			return "<a href=\"{$sImg}\" target=\"_blank\"><img src=\"{$sImg}\" class=\"content-insert-image need_lazyload\" alt=\"".Q::L('在新窗口浏览此图片','__COMMON_LANG__@Common')."\" title=\"".Q::L('在新窗口浏览此图片','__COMMON_LANG__@Common')."\" border=\"0\"/></a>";
		}
	}

	public function attachmentImg_($sUrl){
		$sTitle='<img src="'.__PUBLIC__.'/images/common/media/viewimage.gif"/> '.
			'<a href="'.$sUrl.'" target="_blank">'.basename($sUrl).'</a>';
		$sContent="<a href=\"{$sUrl}\" target=\"_blank\"><img src=\"{$sUrl}\" class=\"content-insert-image need_lazyload\" title=\"".Q::L('在新窗口浏览此图片','__COMMON_LANG__@Common')."\" border=\"0\" tips='<div>{$sTitle}</div>'/></a>";
		return $this->template($sContent);
	}
	
	public function attachmentSwf($oAttachment,$nOuter=0){
		if($nOuter==0){
			if($GLOBALS['_option_']['upload_loginuser_view']==1 && $GLOBALS['___login___']===FALSE){
				return $this->needLogin();
			}else{
				$sTips=$this->commonTips($oAttachment,'swf.gif',Q::L('Flash Player文件','__COMMON_LANG__@Common'));
				return $this->makeMedia(Attachment_Extend::getAttachmenturl($oAttachment),'swf.gif',$oAttachment['attachment_name'],'swf',$oAttachment['attachment_id'],$sTips);
			}
		}else{
			return "<a href=\"".$this->getAttachmentouterurl($oAttachment['attachment_id'])."\" target=\"_blank\">{$oAttachment['attachment_name']}</a>";
		}
	}

	public function attachmentSwf_($sUrl,$sExtension){
		return self::videoSwf($sUrl,$sExtension);
	}

	public function attachmentWmp($oAttachment,$nOuter=0){
		if($nOuter==0){
			if($GLOBALS['_option_']['upload_loginuser_view']==1 && $GLOBALS['___login___']===FALSE){
				return $this->needLogin();
			}else{
				$sTips=$this->commonTips($oAttachment,'wmp.gif',Q::L('Windows Media Player文件','__COMMON_LANG__@Common'));
				return $this->makeMedia(Attachment_Extend::getAttachmenturl($oAttachment),'wmp.gif',$oAttachment['attachment_name'],'wmp',$oAttachment['attachment_id'],$sTips);
			}
		}else{
			return "<a href=\"".$this->getAttachmentouterurl($oAttachment['attachment_id'])."\" target=\"_blank\">{$oAttachment['attachment_name']}</a>";
		}
	}

	public function attachmentWmp_($sUrl,$sExtension){
		return self::musicWmp($sUrl,$sExtension);
	}

	public function attachmentMp3($oAttachment,$nOuter=0){
		if($nOuter==0){
			if($GLOBALS['_option_']['upload_loginuser_view']==1 && $GLOBALS['___login___']===FALSE){
				return $this->needLogin();
			}else{
				$sTips=$this->commonTips($oAttachment,'mp3.gif',Q::L('Mp3文件','__COMMON_LANG__@Common'));
				return $this->makeMedia(Attachment_Extend::getAttachmenturl($oAttachment),'mp3.gif',$oAttachment['attachment_name'],'mp3',$oAttachment['attachment_id'],$sTips,240,20);
			}
		}else{
			return "<a href=\"".$this->getAttachmentouterurl($oAttachment['attachment_id'])."\" target=\"_blank\">{$oAttachment['attachment_name']}</a>";
		}
	}

	public function attachmentMp3_($sUrl,$sExtension){
		return self::musicMp3($sUrl,$sExtension);
	}

	public function attachmentQvod($oAttachment,$nOuter=0){
		if($nOuter==0){
			if($GLOBALS['_option_']['upload_loginuser_view']==1 && $GLOBALS['___login___']===FALSE){
				return $this->needLogin();
			}else{
				$sTips=$this->commonTips($oAttachment,'qvod.gif',Q::L('QVOD视频播放器','__COMMON_LANG__@Common'));
				return $this->makeMedia(Attachment_Extend::getAttachmenturl($oAttachment),'qvod.gif',$oAttachment['attachment_name'],'qvod',$oAttachment['attachment_id'],$sTips);
			}
		}else{
			return "<a href=\"".$this->getAttachmentouterurl($oAttachment['attachment_id'])."\" target=\"_blank\">{$oAttachment['attachment_name']}</a>";
		}
	}

	public function attachmentQvod_($sUrl,$sExtension){
		return self::videoQvod($sUrl,$sExtension);
	}

	public function attachmentFlv($oAttachment,$nOuter=0){
		if($nOuter==0){
			if($GLOBALS['_option_']['upload_loginuser_view']==1 && $GLOBALS['___login___']===FALSE){
				return $this->needLogin();
			}else{
				$sTips=$this->commonTips($oAttachment,'swf.gif',Q::L('Flash Video Player文件','__COMMON_LANG__@Common'));
				return $this->makeMedia(Attachment_Extend::getAttachmenturl($oAttachment),'swf.gif',$oAttachment['attachment_name'],'flv',$oAttachment['attachment_id'],$sTips);
			}
		}else{
			return "<a href=\"".$this->getAttachmentouterurl($oAttachment['attachment_id'])."\" target=\"_blank\">{$oAttachment['attachment_name']}</a>";
		}
	}

	public function attachmentFlv_($sUrl,$sExtension){
		return self::videoFlv($sUrl,$sExtension);
	}

	public function attachmentUrl($oAttachment,$nOuter=0){
		if($nOuter==0){
			if($GLOBALS['_option_']['upload_loginuser_view']==1 && $GLOBALS['___login___']===FALSE){
				return $this->needLogin();
			}else{
				$sTips=$this->commonTips($oAttachment,'url.gif',Q::L('文件','__COMMON_LANG__@Common'));
				$sContent="<a href=\"".Attachment_Extend::getAttachmenturl($oAttachment)."\" title=\"{$oAttachment['attachment_name']}\" target=\"_blank\" tips='{$sTips}'><img src='".__PUBLIC__."/images/common/media/url.gif'/> {$oAttachment['attachment_name']}</a>";
				return $this->template($sContent);
			}
		}else{
			return "<a href=\"".$this->getAttachmentouterurl($oAttachment['attachment_id'])."\" target=\"_blank\">{$oAttachment['attachment_name']}</a>";
		}
	}

	public function attachmentUrl_($sUrl){
		$sIcon='<img src="'.__PUBLIC__.'/images/common/media/url.gif"/> ';
		$sTitle=$sIcon.'<a href="'.$sUrl.'" target="_blank">'.$sExtension.Q::L('文件','__COMMON_LANG__@Common').'</a>';
		$sContent="<a href=\"".$sUrl."\" target=\"_blank\" tips='{$sTitle}'>{$sIcon}{$sUrl}</a>";
		return $this->template($sContent);
	}

	public function attachmentDownload($oAttachment,$nOuter=0){
		if($nOuter==0){
			if($GLOBALS['_option_']['upload_loginuser_view']==1 && $GLOBALS['___login___']===FALSE){
				return $this->needLogin();
			}else{
				$sTips=$this->commonTips($oAttachment,'download.gif',Q::L('下载文件','__COMMON_LANG__@Common'));
				$sContent="<a onclick=\"updateDownload('".$oAttachment['attachment_id']."');\" href=\"".Attachment_Extend::getAttachmenturl($oAttachment)."\" title=\"{$oAttachment['attachment_name']}\" target=\"_blank\" tips='{$sTips}'><img src='".__PUBLIC__."/images/common/media/download.gif'/> {$oAttachment['attachment_name']}</a>";
				return $this->template($sContent);
			}
		}else{
			return "<a href=\"".$this->getAttachmentouterurl($oAttachment['attachment_id'])."\" target=\"_blank\">{$oAttachment['attachment_name']}</a>";
		}
	}

	public function attachmentDownload_($sUrl){
		$sIcon='<img src="'.__PUBLIC__.'/images/common/media/download.gif"/> ';
		$sTitle=$sIcon.'<a href="'.$sUrl.'" target="_blank">'.Q::L('下载文件','__COMMON_LANG__@Common').'</a>';
		$sContent="<a href=\"".$sUrl."\" target=\"_blank\" tips='<div>{$sTitle}</div>'>{$sIcon}{$sUrl}</a>";
		return $this->template($sContent);
	}
	
	public function getAttachment($nId){
		$oAttachment=Model::F_('attachment','attachment_id=?',$nId)->query();
		if(empty($oAttachment['attachment_id'])){
			return false;
		}
		return $oAttachment;
	}

	protected function commonTips($oAttachment,$sIcon,$sTitle=''){
		return '<div><img src="'.__PUBLIC__.'/images/common/media/'.$sIcon.'"/> <a onclick="updateDownload('.$oAttachment['attachment_id'].');" href="'.Q::U('home://file@?id='.$oAttachment['attachment_id']).'" target="_blank" title="'.$oAttachment['attachment_name'].
			' '.Q::L('已下载','__COMMON_LANG__@Common').':'.$oAttachment['attachment_download'].'"><strong>'.($sTitle?$sTitle:Q::L('点击下载','__COMMON_LANG__@Common')).'</strong><span class="tip_size">('.C::changeFileSize($oAttachment['attachment_size']).')</span></a><span class="right">'.
			date('Y-m-d H:i',$oAttachment['create_dateline']).Q::L('上传','__COMMON_LANG__@Common').'</span></div>';
	}

	protected function makeMedia($sSrc,$sImg,$sTitle,$sType,$nDownload=0,$sTips='',$nWidth=600,$nHeight=405){
		$sIcon='<img src="'.__PUBLIC__.'/images/common/media/'.$sImg.'"/> ';
		$sId=C::randString(6);
		$sContent="<a href=\"javascript:playmedia('player_{$sId}','{$sType}','".$sSrc."','{$nWidth}','{$nHeight}','');".($nDownload?"updateDownload('{$nDownload}');":'')."\" tips='".($sTips?$sTips:"<div>{$sIcon}{$sTitle}</div>")."'>".$sIcon.basename($sSrc)."</a><div id=\"player_{$sId}\" style=\"display: none;\"></div>";
		return $this->template($sContent);
	}

	protected function needLogin(){
		return $this->template(
			Q::L('这部分内容只能在登入之后看到。请先','__COMMON_LANG__@Common').' <a onclick="ajaxLogin(\'\',\''.$this->_sRegisterurl.'\');" href="javascript:void(0);">'.Q::L('注册','__COMMON_LANG__@Common').'</a> '.Q::L('或者','__COMMON_LANG__@Common').' <a onclick="ajaxRegister(\'\',\''.$this->_sLoginurl.'\');" href="javascript:void(0);">'.Q::L('登录','__COMMON_LANG__@Common').'</a>',
			Q::L('隐藏内容','__COMMON_LANG__@Common'),
					'hide_ubb_box'
		);
	}

	protected function template($sContent,$sTitle='',$sId='common_ubb_box'){
		if(APP_NAME==='admin'){
			return <<<WINDSFORCE
				<div class="ubb_media_box {$sId}" style="overflow:hidden;width:100%;">
					<p>{$sContent}</p>
				</div>
WINDSFORCE;
		}

		if($sTitle){
			$sTitle=<<<WINDSFORCE
				<div class="ubbmediabox_title">
					{$sTitle}
				</div>
WINDSFORCE;
		}
		
		return <<<WINDSFORCE
		<div class="ubb_media_box {$sId}" style="overflow:hidden;word-wrap:break-word; word-break;break-all;">
			{$sTitle}
			<div class="ubbmediabox_content">
				{$sContent}
			</div>
		</div>
WINDSFORCE;
	}

	protected function getAttachmentouterurl($nId){
		return Q::U('home://attachment/show?id='.$nId,array(),true);
	}

}
