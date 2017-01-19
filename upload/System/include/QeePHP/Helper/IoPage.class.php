<?php
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   IoPage分页处理类 (用于文件夹等分页)($$)*/

!defined('Q_PATH') && exit;

class IoPage extends Page{

	public $_sIoPath='';
	public $_bRecursion=false;
	public $_bFullFilepath=false;
	const DIR_AND_FILE=0;
	const DIR_ONLY=1;
	const FILE_ONLY=2;
	public $_nDataRecordType=self::DIR_ONLY;
	public $_bOnlyImages=false;
	public $_arrData=array();
	public $_arrReturnData=array();
	public $_nTotalCount=0;
	public $_arrAllowedType=array();
	public $_arrDisAllowedType=array();
	public $_arrAllowedDir=array();
	public $_arrDisAllowedDir=array();
	public $_arrAllowedFilename=array();
	public $_arrDisAllowedFilename=array();

	protected function __construct($sIoPath='',$nSize=1,$sUrl='',$sParameter='',$nPage=null,$arrOption=array()){
		$this->_sIoPath=$sIoPath;

		if(isset($arrOption['fullfilepath'])){
			$this->_bFullFilepath=$arrOption['fullfilepath']?true:false;
		}

		if(isset($arrOption['type']) && in_array($arrOption['type'],array(0,1,2))){
			$this->_nDataRecordType=$arrOption['type'];
		}

		if(!empty($arrOption['image'])){
			$this->_bOnlyImages=true;
		}

		if(!empty($arrOption['recursion'])){
			$this->_bRecursion=true;
		}

		if(!empty($arrOption['allowedtype'])){
			$this->_arrAllowedType=$arrOption['allowedtype'];
		}

		if(!empty($arrOption['disallowedtype'])){
			$this->_arrDisAllowedType=$arrOption['disallowedtype'];
		}

		if(!empty($arrOption['alloweddir'])){
			$this->_arrAllowedDir=$arrOption['alloweddir'];
		}

		if(!empty($arrOption['disallowedtype'])){
			$this->_arrDisAllowedDir=$arrOption['disallowedtype'];
		}

		if(!empty($arrOption['allowedfilename'])){
			$this->_arrAllowedFilename=$arrOption['allowedfilename'];
		}

		if(!empty($arrOption['disallowedfilename'])){
			$this->_arrDisAllowedFilename=$arrOption['disallowedfilename'];
		}

		$this->getIoData();// 访问目录
		parent::__construct($this->_nTotalCount,$nSize,$sUrl='',$sParameter='',$nPage);// 调用父级构造函数
	}

	public static function RUN($sIoPath='',$nSize=1,$sUrl='',$sParameter='',$nPage=null,$arrOption=array(),$bDefaultIns=true){
		if($bDefaultIns and self::$_oDefaultDbIns){
			return self::$_oDefaultDbIns;
		}

		$oPage=new self($sIoPath,$nSize,$sUrl,$sParameter,$nPage,$arrOption);
		if($bDefaultIns){
			self::$_oDefaultDbIns=$oPage;
		}

		return $oPage;
	}

	public function getIoData($sIoPath=''){
		if(empty($sIoPath)){
			$sIoPath=$this->_sIoPath;
		}

		$hDir=dir($sIoPath);// 打开
		while(($file=$hDir->read())!==false){// 读取
			if((is_dir("{$sIoPath}/{$file}")) && $file!="." && $file!=".." && $file!='_svn'){
				$bAlllowed=true;// 判断是否允许

				if(!empty($this->_arrAllowedDir) && !in_array($file,$this->_arrAllowedDir)){
					$bAlllowed=false;
				}

				if(!empty($this->_arrDisAllowedDir) && in_array($file,$this->_arrDisllowedDir)){
					$bAlllowed=false;
				}

				if($this->_nDataRecordType!==self::FILE_ONLY){
					if($bAlllowed===true){
						$this->_arrData[]="{$sIoPath}/{$file}";
						$this->_nTotalCount++;
					}
				}

				if($this->_bRecursion===true){
					$this->getIoData("{$sIoPath}/{$file}");
				}
			}else{
				if($this->_nDataRecordType!==self::DIR_ONLY && $file!="." && $file!=".." && $file!='_svn'){
					$sExtendName=strtolower($this->getExtendName($file));
					$bAlllowed=true;// 判断是否允许

					if(!empty($this->_arrAllowedType) && !in_array($sExtendName,$this->_arrAllowedType)){
						$bAlllowed=false;
					}

					if(!empty($this->_arrDisAllowedType) && in_array($sExtendName,$this->_arrDisllowedType)){
						$bAlllowed=false;
					}

					if(!empty($this->_arrAllowedFilename) && !in_array($file,$this->_arrAllowedFilename)){
						$bAlllowed=false;
					}

					if(!empty($this->_arrDisAllowedFilename) && in_array($file,$this->_arrDisAllowedFilename)){
						$bAlllowed=false;
					}

					if($this->_bOnlyImages===true && in_array($sExtendName,array('jpg','jpeg','gif','png','bmp'))){
						if($bAlllowed===true){
							$this->_arrData[]=$this->_bFullFilepath?"{$sIoPath}/{$file}":$file;
							$this->_nTotalCount++;
						}
					}else{
						if($bAlllowed===true){
							$this->_arrData[]=$this->_bFullFilepath?"{$sIoPath}/{$file}":$file;;
							$this->_nTotalCount++;
						}
					}
				}
			}
		}

		$hDir->close();// 关闭
		asort($this->_arrData);// 排序
	}

	protected function getExtendName($sFileName){
		$arrExtend=explode(".",$sFileName);
		return end($arrExtend);
	}

	public function getCurrentData(){
		$arrReturnData=array();

		// 循环条件控制当前数据
		for($nJ=$this->_nPageStart;$nJ<($this->_nSize*($this->_nPage-1)+$this->_nSize) && $nJ<$this->_nCount;++$nJ){
			$arrReturnData[]=$this->_arrData[$nJ];
		}

		$this->_arrReturnData=$arrReturnData;
		return $arrReturnData;
	}

}
