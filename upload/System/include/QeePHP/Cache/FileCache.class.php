<?php
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   使用硬盘文件进行缓存($$)*/

!defined('Q_PATH') && exit;

class FileCache{

	protected $_arrOptions=array(
		'serialize'=>true,
		'cache_time'=>86400,
		'cache_path'=>'',
		'cache_prefix'=>'~@',
	);
	static protected $_sStaticHead='<?php die(); ?>';
	static protected $_nStaticHeadLen=15;

	public function __construct(array $arrOptions=null){
		if(!is_null($arrOptions)){
			$this->_arrOptions=array_merge($this->_arrOptions,$arrOptions);
		}
		if(empty($this->_arrOptions['cache_path'])){
			if(!empty($GLOBALS['_commonConfig_']['RUNTIME_FILECACHE_PATH'])){
				$this->_arrOptions['cache_path']=$GLOBALS['_commonConfig_']['RUNTIME_FILECACHE_PATH'];
			}else{
				$this->_arrOptions['cache_path']=APP_RUNTIME_PATH.'/Data';
			}
		}
	}

	public function checkCache($sCacheName,$arrOptions,$nTime=-1){
		$sFilePath=$this->getCacheFilePath($sCacheName,$arrOptions);
		if(!is_file($sFilePath)){
			return true;
		}
 		return ($nTime!==-1 && filemtime($sFilePath)+$nTime<CURRENT_TIMESTAMP);
	}

	public function getCache($sCacheName,array $arrOptions=null){
		$arrOptions=$this->option($arrOptions);
		$sCacheFilePath=$this->getCacheFilePath($sCacheName,$arrOptions);

		clearstatcache();
		if(!is_file($sCacheFilePath)){ 
			return false; 
		}

		$hFp=fopen($sCacheFilePath,'rb');
		if(!$hFp){
			return false;
		}
		flock($hFp,LOCK_SH);

		$nLen=filesize($sCacheFilePath);
		$bMqr=get_magic_quotes_runtime();
		if(version_compare(PHP_VERSION,'5.3.0','<')){
			@set_magic_quotes_runtime(0);
		}
		
		// 头部的16个字节存储了安全代码
		$sHead=fread($hFp,self::$_nStaticHeadLen);
		$nLen-=self::$_nStaticHeadLen;
		
		do{
			// 检查缓存是否已经过期
			if($this->checkCache($sCacheName,$arrOptions,$arrOptions['cache_time'])){
				$EncryptTest=null;
				$Data=false;
				break;
			}

			if($nLen>0){
				$Data=fread($hFp,$nLen);
			}else{
				$Data=false;
			}

			if(version_compare(PHP_VERSION,'5.3.0','<')){
				@set_magic_quotes_runtime($bMqr);
			}

		}while(false);

		flock($hFp,LOCK_UN);
		fclose($hFp);

		if($Data===false){
			return false;
		}

		// 解码
		if($arrOptions['serialize']){
			$Data=unserialize($Data);
		}

		return $Data;
	}

	public function setCache($sCacheName,$Data,array $arrOptions=null){
		$arrOptions=$this->option($arrOptions);
		if($arrOptions['serialize']){
			$Data=serialize($Data);
		}
		$Data=self::$_sStaticHead.$Data;

		$sCacheFilePath=$this->getCacheFilePath($sCacheName,$arrOptions);
		$this->writeData($sCacheFilePath,$Data);
	}

	public function deleleCache($sCacheName,array $arrOptions=null){
		$arrOptions=$this->option($arrOptions);
		$sCacheFilePath=$this->getCacheFilePath($sCacheName,$arrOptions);
		if($this->existCache($sCacheName,$arrOptions)){
			unlink($sCacheFilePath);
		}
	}

	public function existCache($sCacheName,$arrOptions){
		$sCacheFilePath=$this->getCacheFilePath($sCacheName,$arrOptions);
		return is_file($sCacheFilePath);
	}

	protected function getCacheFilePath($sCacheName,$arrOptions){
		if(!is_dir($arrOptions['cache_path'])){
			C::makeDir($arrOptions['cache_path']);
		}
		return $arrOptions['cache_path'].'/'.$arrOptions['cache_prefix'].$sCacheName.'.php';
	}

	public function writeData($sFileName,$sData){
		!is_dir(dirname($sFileName)) && C::makeDir(dirname($sFileName));
		return file_put_contents($sFileName,$sData,LOCK_EX);
	}

	protected function option(array $arrOptions=null){
		return !is_null($arrOptions)?array_merge($this->_arrOptions,$arrOptions):$this->_arrOptions;
	}

}
