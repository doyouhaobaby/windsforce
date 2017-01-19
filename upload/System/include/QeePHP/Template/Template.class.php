<?php
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   模板处理类（Learn JC!）($$)*/

!defined('Q_PATH') && exit;

class Template{

	protected $TEMPLATE_OBJS=array();
	static public $_arrParses=array();
	protected $_sCompiledFilePath;
	protected $_sThemeName='';
	protected $_bIsChildTemplate=FALSE;
	static protected $_bWithInTheSystem=FALSE;
	static private $_sTemplateDir;
	private $_arrVariable=array();

	public function loadParses(){
		$sClassName=get_class($this);// 具体的类
		call_user_func(array($sClassName,'loadDefaultParses'));// 载入默认的分析器
	}

	public function putInTemplateObj_(TemplateObj $oTemplateObj){
		$this->TEMPLATE_OBJS[]=$oTemplateObj;
	}

	public function clearTemplateObj(){
		$nCount=count($this->TEMPLATE_OBJS);
		$this->TEMPLATE_OBJS=array();

		return $nCount;
	}

	public function compile($sTemplatePath,$sCompiledPath='',$bReturnCompiled=false){
		if(!is_file($sTemplatePath)){
			Q::E('$sTemplatePath is not a file');
		}

		if($sCompiledPath==''){
			$sCompiledPath=$this->getCompiledPath($sTemplatePath);
		}

		$sCompiled=file_get_contents($sTemplatePath);
		foreach(self::$_arrParses as $sParserName){
			$oParser=Q::instance($sParserName);
			$this->bParseTemplate_($sCompiled);
			$oParser->parse($this,$sTemplatePath,$sCompiled);// 分析
			$sCompiled=$this->compileTemplateObj();// 编译
		}

		if(defined('TMPL_STRIP_SPACE')){
			// HTML
			$arrFind=array("~>\s+<~","~>(\s+\n|\r)~");
			$arrReplace=array("><",">");
			$sCompiled=preg_replace($arrFind,$arrReplace,$sCompiled);

			// Javascript
			$sCompiled=preg_replace(array('/(^|\r|\n)\/\*.+?(\r|\n)\*\/(\r|\n)/is','/\/\/note.+?(\r|\n)/i','/\/\/debug.+?(\r|\n)/i','/(^|\r|\n)(\s|\t)+/','/(\r|\n)/',"/\/\*(.*?)\*\//ies"),'',$sCompiled);
		}

		$sStr="<?php !defined('Q_PATH') && exit; /* QeePHP ".(Q::L('模板缓存文件生成时间：','__QEEPHP__@Q')).date('Y-m-d H:i:s',CURRENT_TIMESTAMP)."  */ ?>\r\n";

		$sCompiled=$sStr.$sCompiled;
		$sCompiled=str_replace(array("\r","\n"),'__qeephp_framework_pk_with_you__',$sCompiled);
		$sCompiled=preg_replace("/(__qeephp_framework_pk_with_you__)+/i",'__qeephp_framework_pk_with_you__',$sCompiled);
		$sCompiled=str_replace('__qeephp_framework_pk_with_you__',(IS_WIN?"\r\n":"\n"),$sCompiled);// 解决不同操作系统源代码换行混乱

		if($bReturnCompiled===false){
			$this->makeCompiledFile($sTemplatePath,$sCompiledPath,$sCompiled);// 生成编译文件
			return $sCompiledPath;
		}else{
			return $sCompiled;
		}
	}

	protected function compileTemplateObj(){
		$sCompiled='';// 逐个编译TemplateObj
		foreach($this->TEMPLATE_OBJS as $oTemplateObj){
			$oTemplateObj->compile();
			$sCompiled.=$oTemplateObj->getCompiled();
		}

		return $sCompiled;
	}

	public function getCompiledPath($sTemplatePath){
		$sTemplatePath=str_replace('\\','/',$sTemplatePath); 
		
		$arrValue=explode('/',str_replace(array(str_replace('\\','/',TEMPLATE_PATH.'/'),str_replace('\\','/',Q_PATH.'/'),str_replace('\\','/',getcwd().'/')),array(''),$sTemplatePath));

		if($GLOBALS['_commonConfig_']['TMPL_MODULE_ACTION_DEPR']=='/' && count($arrValue)>1){
			array_shift($arrValue);
		}
		
		if(self::$_bWithInTheSystem===true){// 如果保存在系统内部
			$this->_sCompiledFilePath=dirname($sTemplatePath).'/~Com/'.basename($sTemplatePath,$GLOBALS['_commonConfig_']['TEMPLATE_SUFFIX']).'.php';
			return $this->_sCompiledFilePath;
		}

		$sFileName=implode('/',$arrValue);

		$this->_sCompiledFilePath=APP_RUNTIME_PATH.'/Cache/'.($this->_sThemeName?ucfirst($this->_sThemeName).'/':'').
			($GLOBALS['_commonConfig_']['TMPL_MODULE_ACTION_DEPR']=='/'?ucfirst(MODULE_NAME).'/':'').
			basename($sFileName,$GLOBALS['_commonConfig_']['TEMPLATE_SUFFIX']).(dirname($sFileName)!='.'?'~@'.md5(dirname($sFileName)):'').'.php';

		return $this->_sCompiledFilePath;
	}

	static public function in($bWithInTheSystem=false){
		$bOldValue=self::$_bWithInTheSystem;
		self::$_bWithInTheSystem=$bWithInTheSystem;

		return $bOldValue;
	}

	public function returnCompiledPath(){
		return $this->_sCompiledFilePath;
	}

	protected function isCompiledFileExpired($sTemplatePath,$sCompiledPath){
		if(!is_file($sCompiledPath)){
			return true;
		}

		if($GLOBALS['_commonConfig_']['CACHE_LIFE_TIME']==-1){// 编译过期时间为-1表示永不过期
			return false;
		}

		if(filemtime($sCompiledPath)+$GLOBALS['_commonConfig_']['CACHE_LIFE_TIME']<CURRENT_TIMESTAMP){
			return true;
		}

		if(filemtime($sTemplatePath)>=filemtime($sCompiledPath)){
			return true;
		}

		return false;
	}

	protected function makeCompiledFile($sTemplatePath,$sCompiledPath,&$sCompiled){
		!is_file($sCompiledPath) && !is_dir(dirname($sCompiledPath)) && C::makeDir(dirname($sCompiledPath));
		file_put_contents($sCompiledPath,$sCompiled);
	}

	static public function loadDefaultParses(){
		include_once(Q_PATH.'/Template/TemplateParsers_.php');

		TemplateGlobalParser::regToParser();// 全局
		TemplatePhpParser::regToParser();// PHP
		TemplateCodeParser::regToParser();// 代码
		TemplateNodeParser::regToParser();// 节点
		TemplateRevertParser::regToParser(); // 反向
		TemplateGlobalRevertParser::regToParser(); // 全局反向
	}

	static public function setTemplateDir($sDir){
		if(!is_dir($sDir)){
			Q::E('$sDir is not a dir');
		}

		return self::$_sTemplateDir=$sDir;
	}

	static public function findTemplate($arrTemplateFile){
		$sTemplateFile=isset($arrTemplateFile['theme'])?$arrTemplateFile['theme'].'/':'';

		$sTemplateFile.=(isset($arrTemplateFile['file'])?$arrTemplateFile['file']:'');
		if(is_file(self::$_sTemplateDir.'/'.$sTemplateFile)){
			return self::$_sTemplateDir.'/'.$sTemplateFile;
		}

		if(defined('QEEPHP_TEMPLATE_BASE') && !isset($arrTemplateFile['theme']) && ucfirst(QEEPHP_TEMPLATE_BASE)!==TEMPLATE_NAME){// 依赖模板 兼容性分析
			$sTemplateDir=str_replace('/Theme/'.TEMPLATE_NAME.'/','/Theme/'.ucfirst(QEEPHP_TEMPLATE_BASE).'/',self::$_sTemplateDir.'/');
			if(is_file($sTemplateDir.'/'.$sTemplateFile)){
				return $sTemplateDir.'/'.$sTemplateFile;
			}
		}

		if(!isset($arrTemplateFile['theme']) && 'Default'!==TEMPLATE_NAME){// Default模板 兼容性分析
			$sTemplateDir=str_replace('/Theme/'.TEMPLATE_NAME.'/','/Theme/Default/',self::$_sTemplateDir.'/');
			if(is_file($sTemplateDir.'/'.$sTemplateFile)){
				return $sTemplateDir.$sTemplateFile;
			}
		}

		return null;
	}

	public function putInTemplateObj(TemplateObj $oTemplateObj){
		$oTopTemplateObj=$this->TEMPLATE_OBJS[0];
		$oTopTemplateObj->addTemplateObj($oTemplateObj);// 插入
	}

	protected function bParseTemplate_(&$sCompiled){
		$oTopTemplateObj=new TemplateObj($sCompiled);// 创建顶级TemplateObj
		$oTopTemplateObj->locate($sCompiled,0);
		$this->clearTemplateObj();
		Template::putInTemplateObj_($oTopTemplateObj);
	}

	public function includeChildTemplate($sTemplateFile,$sCurrentFile='',$sSourceFile=''){
		if(!is_file($sTemplateFile)){
			$bExistsFile=false;// 默认主题自动导向
			if(defined('QEEPHP_TEMPLATE_BASE') && ucfirst(QEEPHP_TEMPLATE_BASE)!==TEMPLATE_NAME){// 依赖主题自动导向
				$sReplacePath='/Theme/'.TEMPLATE_NAME.'/';
				$sTargetPath='/Theme/'.ucfirst(QEEPHP_TEMPLATE_BASE).'/';
				$sTemplateFile2=str_replace($sReplacePath,$sTargetPath,$sTemplateFile);
				if(is_file($sTemplateFile2)){
					$sTemplateFile=&$sTemplateFile2;
					$bExistsFile=true;
				}else{
					unset($sTemplateFile2);
				}
			}

			if($bExistsFile===false && 'Default'!==TEMPLATE_NAME){// 默认主题自动导向
				$sReplacePath='/Theme/'.TEMPLATE_NAME.'/';
				$sTargetPath='/Theme/Default/';
				$sTemplateFile=str_replace($sReplacePath,$sTargetPath,$sTemplateFile);
				if(is_file($sTemplateFile)){
					$bExistsFile=true;
				}
			}

			if($bExistsFile===false){
				E(Q::L('警告：对不起子模板 %s 不存在','__QEEPHP__@Q',null,$sTemplateFile));
				return;
			}
		}

		$this->display($sTemplateFile,true,true,$sCurrentFile,$sSourceFile);
	}

	public function display($TemplateFile,$bDisplayAtOnce=true,$bChild=false,$sCurrentFile='',$sSourceFile=''){
		$TemplateFileOld=$TemplateFile;

		if(is_string($TemplateFile) && is_file($TemplateFile)){
			$this->_sThemeName=TEMPLATE_NAME;
		}else{
			if(is_array($TemplateFile) && !empty($TemplateFile['theme'])){
				$this->_sThemeName=$TemplateFile['theme'];
			}else{
				$this->_sThemeName=TEMPLATE_NAME;
			}
			$TemplateFile=self::findTemplate($TemplateFile);
		}

		if(!is_file($TemplateFile)){
			$TemplateFile=$TemplateFile?$TemplateFile:$TemplateFileOld;
			Q::E(Q::L('无法找到模板文件<br>%s','__QEEPHP__@Q',null,is_array($TemplateFile)?implode(' ',$TemplateFile):$TemplateFile));
		}

		$arrVars=&$this->_arrVariable;
		if(is_array($arrVars) and count($arrVars)){
			extract($arrVars,EXTR_PREFIX_SAME,'tpl_');
		}

		$sCompiledPath=$this->getCompiledPath($TemplateFile);// 编译文件路径
		if($this->isCompiledFileExpired($TemplateFile,$sCompiledPath)){// 重新编译
			$this->loadParses();
			$this->compile($TemplateFile,$sCompiledPath);
		}

		// 逐步将子模板缓存写入父模板至到最后
		if($bChild===true){
			if($GLOBALS['_commonConfig_']['CACHE_REPLACE_CHILDREN'] && is_file($sCurrentFile) && is_file($sCompiledPath)){
				$sTheNote="/<!--<\#\#\#\#incl\*".md5($sSourceFile)."\*ude\#\#\#\#>-->(.*?)<!--<\/\#\#\#\#incl\*".md5($sSourceFile)."\*ude####\/>-->/s";
				$sCurrentCache=file_get_contents($sCurrentFile);
				preg_match_all($sTheNote,$sCurrentCache,$arrResult);

				// 替换标签
				if(!empty($arrResult[1][0])){
					$sCurrentCache=str_replace($arrResult[1][0],file_get_contents($sCompiledPath),$sCurrentCache);
					file_put_contents($sCurrentFile,$sCurrentCache);
				}
			}
		}

		$sReturn=null;
		if($bDisplayAtOnce===false){// 需要返回
			ob_start();
			include $sCompiledPath;
			$sReturn=ob_get_contents();
			ob_end_clean();
			return $sReturn;
		}else{// 不需要返回
			include $sCompiledPath;
		}

		return $sReturn;
	}

	public function setVar($Name,$Value=null){
		if(is_string($Name)){
			$sOldValue=isset($this->_arrVariable[$Name])?$this->_arrVariable[$Name]:null;
			$this->_arrVariable[$Name]=&$Value;
			return $sOldValue;
		}elseif(is_array($Name)){
			foreach($Name as $sName=>&$EachValue){
				$this->setVar($sName,$EachValue);
			}
		}
	}

	public function getVar($sName){
		return isset($this->_arrVariable[$sName])?$this->_arrVariable[$sName]:null;
	}

}
