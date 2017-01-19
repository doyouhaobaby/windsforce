<?php
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   使用MemCache来缓存数据($$)*/

!defined('Q_PATH') && exit;

class MemcacheCache{

	protected $_arrDefaultServer=array(
		'host'=>'127.0.0.1',// 缓存服务器地址或主机名
		'port'=>'11211',// 缓存服务器端口
	);
	protected $_arrOptions=array(
		'servers'=>array(),
		'compressed'=>false,//是否压缩缓存数据
		'persistent'=>true,//是否使用持久连接
		'cache_time'=>86400,
		'cache_prefix'=>'~@',
	);
	protected $_hHandel;

	public function __construct(array $arrOptions=null){
		if(!extension_loaded('memcache')){
			Q::E('memcache extension must be loaded before use.');
		}

		$this->_arrOptions['compressed']=$GLOBALS['_commonConfig_']['RUNTIME_MEMCACHE_COMPRESSED'];
		$this->_arrOptions['persistent']=$GLOBALS['_commonConfig_']['RUNTIME_MEMCACHE_PERSISTENT'];

		if(is_array($arrOptions)){
			$this->_arrOptions=array_merge($this->_arrOptions,$arrOptions);
		}

		if(empty($this->_arrOptions['servers'])){
			if(!empty($GLOBALS['_commonConfig_']['RUNTIME_MEMCACHE_SERVERS'])){
				$this->_arrOptions['servers']=$GLOBALS['_commonConfig_']['RUNTIME_MEMCACHE_SERVERS'];
			}else{
				$this->_arrOptions['servers'][]=
					array(
						'host'=>$GLOBALS['_commonConfig_']['RUNTIME_MEMCACHE_HOST'],
						'port'=>$GLOBALS['_commonConfig_']['RUNTIME_MEMCACHE_PORT'],
					);
			}
		}

		$this->_hHandel=new Memcache();
		foreach($this->_arrOptions['servers'] as $arrServer){
			$bResult=$this->_hHandel->addServer($arrServer['host'],$arrServer['port'],$this->_arrOptions['persistent']);
			if(!$bResult){
				Q::E(sprintf('Unable to connect the memcached server [%s:%s] failed.',$arrServer['host'],$arrServer['port']));
			}
		}
	}

	public function getCache($sCacheName){
		return $this->_hHandel->get($this->_arrOptions['cache_prefix'].$sCacheName);
	}

	public function setCache($sCacheName,$Data,array $arrOptions=null){
		$bCompressed=isset($arrOptions['compressed'])?$arrOptions['compressed']:$this->_arrOptions['compressed'];
		$nCacheTime=isset($arrOptions['cache_time'])?$arrOptions['cache_time']:$this->_arrOptions['cache_time'];
		$this->_hHandel->set($this->_arrOptions['cache_prefix'].$sCacheName,$Data,$bCompressed?MEMCACHE_COMPRESSED:0,$nCacheTime);
	}

	public function deleleCache($sCacheName){
		return $this->_hHandel->delete($this->_arrOptions['cache_prefix'].$sCacheName);
	}

}
