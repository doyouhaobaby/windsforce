<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   缓存文件($$)*/

!defined('Q_PATH') && exit;

class Cache_Extend{

	public static function updateCache($sCacheName='',$arrNotallowed=array()/** args */){
		$arrUpdateList=empty($sCacheName)?array():(is_array($sCacheName)?$sCacheName:array($sCacheName));

		if(!$arrUpdateList){
			$arrUpdatecache=array();

			$arrAllCachefile=C::listDir(WINDSFORCE_PATH.'/System/function/cache',false,true);
			foreach($arrAllCachefile as $sCachefile){
				$sCachefile=strtolower(subStr($sCachefile,11,-5));
				if(!in_array($sCachefile,$arrNotallowed)){
					$arrUpdatecache[]=$sCachefile;
				}
			}

			foreach($arrUpdatecache as $sUpdatecache){
				self::updateCache($sUpdatecache,$arrNotallowed);
			}
		}else{
			$arrArgs=func_get_args();
			array_shift($arrArgs);
			array_shift($arrArgs);
			
			foreach($arrUpdateList as $sCache){
				$sCache=ucfirst($sCache);
				if(strpos($sCache,'@')){
					$arrTemp=explode('@',$sCache);
					$sCache=$arrTemp[0];
				}
				$sCachefile=WINDSFORCE_PATH.'/System/function/cache/UpdateCache'.$sCache.'_.php';
				if(is_file($sCachefile)){
					$sCacheclass='UpdateCache'.ucfirst($sCache);
					if(!Q::classExists($sCacheclass)){
						require_once(Core_Extend::includeFile('function/cache/'.$sCacheclass,null,'_.php'));
					}

					$Callback=array($sCacheclass,'cache');
					if(is_callable($Callback)){
						call_user_func_array($Callback,$arrArgs);
					}else{
						Q::E('$Callback is not a callback');
					}
				}else{
					if(strpos($sCache,'_')!==false){
						$arrCaches=explode('_',$sCache);
						self::appUpdateCache(strtolower($arrCaches[1]),strtolower($arrCaches[0]),$arrNotallowed,$arrArgs);
					}else{
						Q::E('cache parameter is error');
					}
				}
			}
		}
	}

	public static function appUpdateCache($sCacheName='',$sApp='home',$arrNotallowed=array(),$arrArgs=array()){
		$arrUpdateList=empty($sCacheName)?array():(is_array($sCacheName)?$sCacheName:array($sCacheName));

		if(!$arrUpdateList){
			$arrUpdatecache=array();

			$arrAllCachefile=C::listDir(WINDSFORCE_PATH.'/System/app/'.$sApp.'/App/Class/Extension/cache',false,true);
			foreach($arrAllCachefile as $sCachefile){
				$sCachefile=strtolower(subStr($sCachefile,14,-5));
				if(!in_array($sCachefile,$arrNotallowed)){
					$arrUpdatecache[]=$sCachefile;
				}
			}

			foreach($arrUpdatecache as $sUpdatecache){
				self::appUpdateCache($sUpdatecache,$sApp,$arrNotallowed);
			}
		}else{
			foreach($arrUpdateList as $sCache){
				$sCachefile=WINDSFORCE_PATH.'/System/app/'.$sApp.'/App/Class/Extension/cache/AppUpdateCache'.ucfirst($sCache).'_.php';
				if(is_file($sCachefile)){
					$sCacheclass='AppUpdateCache'.ucfirst($sCache);
					if(!Q::classExists($sCacheclass)){
						require_once(Core_Extend::includeFile('cache/'.$sCacheclass,$sApp,'_.php'));
					}

					// 导入应用模型
					Q::import(WINDSFORCE_PATH.'/System/app/'.$sApp.'/App/Class/Model');

					$Callback=array($sCacheclass,'cache');
					if(is_callable($Callback)){
						call_user_func_array($Callback,$arrArgs);
					}else{
						Q::E('$Callback is not a callback');
					}
				}else{
					Q::E('$sCachefile %s is not exists');
				}
			}
		}
	}

}
