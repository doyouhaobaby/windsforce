<?php
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   语言包管理类（Learn JC!）($$)*/

!defined('Q_PATH') && exit;

class LangPackage{

	private $_sPackageName='';
	private $_sLangName='';
	private $_sPackagePath='';
	public $LANGS=array();
	private $_bNeedUpdated=false;
	private $_nUpdateTime=0;
	static private $LANG_PACKAGES=array();
	static private $_sHistoryPath=null;

	static public function getPackage($sLangName,$sPackageName){
		if(isset(self::$LANG_PACKAGES[$sLangName][$sPackageName])){// 直接返回已经被创建的享员对象
			return self::$LANG_PACKAGES[$sLangName][$sPackageName];
		}else{// 创建对象
			$sPackageNameOld=$sPackageName;
			
			// 分析语言包路径
			if(strpos($sPackageName,'@')!==false){
				$arrValues=explode('@',$sPackageName);
				if($arrValues[0]==='__QEEPHP__'){
					$sDir=Q_PATH.'/Resource_/Lang';
				}elseif($arrValues[0]==='__APP__'){
					$sDir=APP_LANG_PATH;
				}else{
					$bDefined=false;
					eval('$bDefined=defined(\''.$arrValues[0].'\');');
					if($bDefined){
						eval('$sDir='.$arrValues[0].';');
					}else{
						$sDir=$arrValues[0];
					}
				}

				$sPackageName=$arrValues[1];
			}else{
				$sDir=APP_LANG_PATH;
			}

			$sThePackagePath=self::findPackage($sDir,$sLangName,$sPackageName);

			if(!($oThePackage=self::hasPackage($sLangName,$sPackageNameOld))){
				$oThePackage=self::createPackage($sLangName,$sPackageNameOld,$sThePackagePath);
			}

			if($sThePackagePath){
				$oThePackage->loadPackageFile($sThePackagePath);
			}else{
				E('We can not find a lang file:<br/>'.self::$_sHistoryPath);
				exit();
			}

			return $oThePackage;
		}
	}

	static private function findPackage($sDir,$sLangName,$sPackageName=null){
		$sDir=ucfirst($sDir);
		$sLangName=ucfirst($sLangName);
		$sPackageName=ucfirst($sPackageName);

		$sPath="{$sDir}/{$sLangName}/{$sPackageName}.lang.php";
		if(is_file($sPath)){
			return $sPath;
		}elseif(is_file("{$sDir}/Zh-cn/{$sPackageName}.lang.php")){// 尝试从默认语言环境中加载
			return "{$sDir}/Zh-cn/{$sPackageName}.lang.php";
		}
		self::$_sHistoryPath=$sPath;

		return null;
	}

	public function loadPackageFile($sPackagePath){
		if(!is_file($sPackagePath)){
			E(sprintf('PackagePath [ %s ] is not exists',self::$_sHistoryPath));
		}

		$this->LANGS=array_merge($this->LANGS,(array)(include $sPackagePath));

		if($this->_nUpdateTime<filemtime($sPackagePath)){// 更新语言包的时间
			$this->_nUpdateTime=filemtime($sPackagePath);
		}
	}

	static private function createPackage($sLangName,$sPackageName,$sPackagePath){
		if(isset(self::$LANG_PACKAGES[$sLangName][$sPackageName])){
			return self::$LANG_PACKAGES[$sLangName][$sPackageName];
		}

		return self::$LANG_PACKAGES[$sLangName][$sPackageName]=new self($sLangName,$sPackageName,$sPackagePath);
	}

	static private function hasPackage($sLangName,$sPackageName){
		if(isset(self::$LANG_PACKAGES[$sLangName][$sPackageName])){
			return self::$LANG_PACKAGES[$sLangName][$sPackageName];
		}else{
			return false;
		}
	}

	private function __construct($sLangName,$sPackageName,$sPackagePath){
		$this->_sPackagePath=$sPackagePath;
		$this->_sPackageName=$sPackageName;
		$this->_sLangName=$sLangName;
		$this->load();
	}

	public function __destruct(){
		if($this->isUpdated()){
			$this->save();
		}
	}

	public function load(){
		$this->LANGS=array();
		$this->loadPackageFile($this->_sPackagePath);
	}

	public function save(){
		$sOut="<?php\r\n";
		$sOut.="/** QeePHP Lang File, Do not to modify it! */\r\n";
		$sOut.="return array(\r\n";

		foreach($this->LANGS as $sKey=>$sValue){
			$sKey=strtolower($sKey);
			$sValue=$this->filterOptionValue($sValue);
			$sOut.="'{$sKey}'=>$sValue,\r\n";
		}

		$sOut.=")\r\n";
		$sOut.="\r\n?>";

		if(!file_put_contents($this->_sPackagePath,$sOut)){
			E('Configuration write failed system language,if your sever is Linux hosts ,set permissions to 0777 ,the path is'.$this->_sPackagePath);
		}
	}

	private function filterOptionValue($sValue){
		if($sValue===false){
			return 'FALSE';
		}

		if($sValue===true){
			return 'TRUE';
		}

		if($sValue==''){
			return '""';
		}
		$sValue=str_replace('"','\\"',$sValue);
		$sValue=str_replace("\n","\\n",$sValue);
		$sValue=str_replace("\r","\\r",$sValue);
		$sValue=str_replace('$','\\$',$sValue);

		return '"'.$sValue.'"';
	}

	public function getName(){
		return $this->_sPackageName;
	}

	public function getLangName(){
		return $this->_sLanguageName;
	}

	public function set($sKey,$sValue){
		if($this->get($sKey)==$sValue){
			return;
		}

		$this->LANGS[$sKey]=$sValue;
		$this->_bNeedUpdated=true;
	}

	public function get($sKey){
		if($this->has($sKey)){
			return $this->LANGS[$sKey];
		}else{
			return null;
		}
	}

	public function has($sKey){
		return isset($this->LANGS[$sKey]);
	}

	public function isUpdated(){
		return $this->_bNeedUpdated;
	}

	public function getUpdateTime(){
		return $this->_nUpdateTime;
	}

}
