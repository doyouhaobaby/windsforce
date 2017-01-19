<?php
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   对 PHP 原生Cookie 函数库的封装($$)*/

!defined('Q_PATH') && exit;

class Cookie{

	public static function setCookie($sName,$sValue='',$nLife=0,$sCookieDomain=null,$bPrefix=true,$bHttponly=false){
		$sName=($bPrefix?$GLOBALS['_commonConfig_']['COOKIE_PREFIX']:'').$sName;

		if($sValue===null || $nLife<0){
			$nLife=-1;
			if(isset($_COOKIE[$sName])){
				unset($_COOKIE[$sName]);
			}
		}else{
			$_COOKIE[$sName]=$sValue;
			if($nLife!==NULL && $nLife==0){
				$nLife=$GLOBALS['_commonConfig_']['COOKIE_EXPIRE'];
			}
		}

		$nLife=$nLife>0?CURRENT_TIMESTAMP+$nLife:($nLife<0?CURRENT_TIMESTAMP-31536000:null);
		$sPath=$bHttponly && PHP_VERSION<'5.2.0'?$GLOBALS['_commonConfig_']['COOKIE_PATH'].';HttpOnly':$GLOBALS['_commonConfig_']['COOKIE_PATH'];
		$sCookieDomain=$sCookieDomain!==null?$sCookieDomain:$GLOBALS['_commonConfig_']['COOKIE_DOMAIN'];

		$nSecure=$_SERVER['SERVER_PORT']==443?1:0;
		if(PHP_VERSION<'5.2.0'){
			setcookie($sName,$sValue,$nLife,$sPath,$sCookieDomain,$nSecure);
		}else{
			setcookie($sName,$sValue,$nLife,$sPath,$sCookieDomain,$nSecure,$bHttponly);
		}
	}

	public static function getCookie($sName,$bPrefix=true){
		$sName=($bPrefix?$GLOBALS['_commonConfig_']['COOKIE_PREFIX']:'').$sName;
		return isset($_COOKIE[$sName])?$_COOKIE[$sName]:'';
	}

	public static function deleteCookie($sName,$sCookieDomain=null,$bPrefix=true){
		self::setCookie($sName,null,-1,$sCookieDomain,$bPrefix);
	}

	public static function clearCookie($bOnlyDeletePrefix=true,$sCookieDomain=null){
		$nCookie=count($_COOKIE);
		foreach($_COOKIE as $sKey=>$Val){
			if($bOnlyDeletePrefix===true && $GLOBALS['_commonConfig_']['COOKIE_PREFIX']){
				if(strpos($sKey,$GLOBALS['_commonConfig_']['COOKIE_PREFIX'])===0){
					self::deleteCookie($sKey,$sCookieDomain,false);
				}
			}else{
				self::deleteCookie($sKey,$sCookieDomain,false);
			}
		}

		return $nCookie;
	}

}
