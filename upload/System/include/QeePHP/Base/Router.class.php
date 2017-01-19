<?php
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   系统路由解析类($$)*/

!defined('Q_PATH') && exit;

class Router{

	protected $_sLastRouterName=null;
	protected $_arrLastRouteInfo=array();
	protected $_arrRouters=array();
	protected $_oUrlParseObj=null;

	public function __construct($oUrlParseObj=null){
		if(!$GLOBALS['_commonConfig_']['START_ROUTER']){
			return false;
		}
		
		if(is_null($oUrlParseObj)){
			$this->_oUrlParseObj=new Url();
		}else{
			$this->_oUrlParseObj=$oUrlParseObj;
		}
	}

	public function G($sRouterName=null){
		$sRouterName=$sRouterName?$sRouterName:$this->getRouterName();

		$arrRouteInfo=array();
		if(!empty($this->_arrRouters)){
			if(isset($this->_arrRouters[$sRouterName])){
				if(!strpos($sRouterName,'@')){
					$arrRouteInfo=$this->getNormalRoute($sRouterName,$this->_arrRouters[$sRouterName]);
				}else{
					$arrRouteInfo=$this->getFlowRoute($sRouterName,$this->_arrRouters[$sRouterName]);
				}
			}else{
				$sRegx=trim($_SERVER['PATH_INFO'],'/');
				foreach($this->_arrRouters as $sKey=>$sRouter){
					if(0===strpos($sKey,'/') && preg_match($sKey,$sRegx,$arrMatches)){
						$arrRouteInfo=$this->getRegexRoute($arrMatches,$sRouter,$sRegx);
						break;
					}
				}
			}
		}

		$this->_arrRouteInfo=$arrRouteInfo;
		return $this->_arrRouteInfo;
	}

	public function import(array $arrRouters=null){
		if(!$GLOBALS['_commonConfig_']['START_ROUTER']){
			return false;
		}

		if(is_null($arrRouters)){
			$arrRouters=$GLOBALS['_commonConfig_']['_ROUTER_'];
		}

		$this->_arrRouters=array_merge($this->_arrRouters,$arrRouters);
		return $this;
	}

	public function add($sRouteName,array $arrRule){
		$this->_arrRouters[$sRouteName]=$arrRule;
		return $this;
	}

	public function remove($sRouteName){
		unset($this->_arrRouters[$sRouteName]);
		return $this;
	}

	public function get($sRouteName){
		return $this->_arrRouters[$sRouteName];
	}

	public function getLastRouterName(){
		return $this->_sLastRouterName;
	}

	public function getLastRouterInfo(){
		 return $this->_arrLastRouteInfo;
	}

	private function parseUrl($Route){
		if(is_string($Route)){
			$arrArray=array_filter(explode('/',$Route));
		}else{
			$arrArray=$Route;
		}

		if(count($arrArray)!==2){
			Q::E('$Route parameter format error,claiming the $arrArray the number of elements equal 2.');
		}

		$arrVar=array();
		$arrVar['a']=array_pop($arrArray);
		$arrVar['c']=array_pop($arrArray);

		return $arrVar;
	}

	private function getRouterName(){
		if(isset($_GET['r'])){
			$sRouteName=$_GET['r'];
			unset($_GET['r']);
		}else{
			$sPathInfo=&$_SERVER['PATH_INFO'];
			$arrPaths=explode($GLOBALS['_commonConfig_']['URL_PATHINFO_DEPR'],trim($sPathInfo,'/'));
			
			if(isset($arrPaths[0]) && $arrPaths[0]=='app'){
				array_shift($arrPaths);
				$_GET['app']=array_shift($arrPaths);
			}

			$sRouteName=array_shift($arrPaths);
		}

		$sRouteName=strtolower($sRouteName);
		if(isset($this->_arrRouters[$sRouteName.'@'])){
			$sRouteName=$sRouteName.'@';
		}
		$this->_sLastRouterName=$sRouteName;
		return $this->_sLastRouterName;
	}

	private function getNormalRoute($sRouteName,array $arrRule){
		if(isset($arrRule['regex'])){
			return $this->getRegexRoute_($sRouteName,$arrRule);
		}else{
			return $this->getSimpleRoute_($sRouteName,$arrRule);
		}
	}

	private function getFlowRoute($sRouteName,array $arrRule){
		foreach($arrRule as $arrRule){
			$arrVar=$this->getNormalRoute($sRouteName,$arrRule);
			if(!empty($arrVar)){
				return $arrVar;
			}
		}

		return array();
	}

	private function getSimpleRoute_($sRouteName,$arrRule){
		if(count($arrRule)<2 || count($arrRule)>5){
			Q::E('$arrRule parameter must be greater than or equal 2,less than or equal 5.');
		}

		$arrVar=$this->parseUrl($arrRule[0]);
		if($GLOBALS['_commonConfig_']['URL_MODEL']===URL_COMMON){
			return $arrVar;
		}

		$sPathInfo=&$_SERVER['PATH_INFO'];
		$sDepr=$GLOBALS['_commonConfig_']['URL_PATHINFO_DEPR'];
		$sRegx=str_replace('/',$sDepr,rtrim($sPathInfo,'/'));
		$arrPaths=array_filter(explode($sDepr,trim(str_ireplace($sDepr.strtolower($sRouteName).$sDepr,$sDepr,$sRegx),$sDepr)));
		if(isset($arrPaths[0]) && $arrPaths[0]=='app'){
			array_shift($arrPaths);
			$arrVar['app']=array_shift($arrPaths);
		}
		
		if(!empty($arrRule[1]) && in_array($arrRule[1],$arrPaths)){
			foreach($arrPaths as $nKey=>$sValue){
				if($sValue==$arrRule[1]){
					unset($arrPaths[$nKey]);
				}
			}
		}

		$arrVars=explode(',',$arrRule[1]);
		for($nI=0;$nI<count($arrVars);$nI++){
			$arrVar[$arrVars[$nI]]=array_shift($arrPaths);
		}
		$bResult=preg_replace('@(\w+)\/([^,\/]+)@e','$arrVar[\'\\1\']="\\2";',implode('/',$arrPaths));
		
		$arrParams=array();
		if(isset($arrRule[2])){
			parse_str($arrRule[2],$arrParams);
			$arrVar=array_merge($arrVar,$arrParams);
		}

		return $arrVar;
	}

	private function getRegexRoute_($sRouteName,$arrRule){
		if(count($arrRule)<3 || count($arrRule)>6){
			Q::E('$arrRule parameter must be greater than or equal 3, less than or equal 6.');
		}

		$sPathInfo=&$_SERVER['PATH_INFO'];
		$sDepr=$GLOBALS['_commonConfig_']['URL_PATHINFO_DEPR'];
		$sRegx=trim($sPathInfo,'/');
		$sRegx=explode($sDepr,$sRegx);
		if($sRegx[0]=='app'){
			array_shift($sRegx);
			$_GET['app']=array_shift($sRegx);
		}
		$sRegx=implode($sDepr,$sRegx);
		$sRegx=ltrim($sRegx,strtolower(rtrim($sRouteName,'@')));
		$sTheRegex=array_shift($arrRule);
		$arrMatches=array();
		if(preg_match($sTheRegex,$sRegx,$arrMatches)){
			$arrVar=$this->parseUrl($arrRule[0]);
			if($GLOBALS['_commonConfig_']['URL_MODEL']===URL_COMMON){
				return $arrVar;
			}

			$arrVars=explode(',',$arrRule[1]);
			for($nI=0;$nI<count($arrVars);$nI++){
				$arrVar[$arrVars[$nI]]=$arrMatches[$nI+1];
			}

			$bResult=preg_replace('@(\w+)\/([^,\/]+)@e','$arrVar[\'\\1\']="\\2";',trim(str_replace($arrMatches[0],'',$sRegx),'\/'));
			
			$arrParams=array();
			if(isset($arrRule[2])){
				parse_str($arrRule[2],$arrParams);
				$arrVar=array_merge($arrVar,$arrParams);
			}
			return $arrVar;
		}

		return array();
	}

	/**
	 * 全局解析规则 TP
	 */
	private function getRegexRoute($arrMatches,$sRouter,$sRegx){
		$sUrl=is_array($sRouter)?$sRouter[0]:$sRouter;
		$sUrl=preg_replace('/:(\d+)/e','$arrMatches[\\1]',$sUrl);
		if(0===strpos($sUrl,'/') || 0===strpos($sUrl,'http')){
			header("Location:{$sUrl}",true,is_array($sRouter) && !empty($sRouter[1])?$sRouter[1]:301);
			exit;
		}else{
			$arrVar=array();
			if(false!==strpos($sUrl,'?')){
				$arrInfo=parse_url($sUrl);
				$arrPath=explode('/',$arrInfo['path']);
				parse_str($arrInfo['query'],$arrVar);
			}elseif(strpos($sUrl,'/')){
				$arrPath=explode('/',$sUrl);
			}else{
				parse_str($sUrl,$arrVar);
			}

			if(isset($arrPath)) {
				$arrVar['a']=array_pop($arrPath);
				if(!empty($arrPath)){
					$arrVar['c']=array_pop($arrPath);
				}
				if(!empty($arrPath)){
					$var['app']=array_pop($arrPath);
				}
			}

			$sRegx=substr_replace($sRegx,'',0,strlen($arrMatches[0]));
			if($sRegx){
				preg_replace('@(\w+)\/([^,\/]+)@e','$arrVar[strtolower(\'\\1\')]=strip_tags(\'\\2\');',$sRegx);
			}

			if(is_array($sRouter) && !empty($sRouter[1])){
				parse_str($sRouter[1],$arrParams);
				$arrVar=array_merge($arrVar,$arrParams);
			}
		}

		return $arrVar;
	}

}
