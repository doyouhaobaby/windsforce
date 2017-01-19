<?php
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   文件上传 for jquery uploadify flash 文件上传($$)*/

!defined('Q_PATH') && exit;

class UploadFileForUploadify extends UploadFile{

	protected $_sUploadifyDataName='Filedata';

	public function upload($sSavePath=''){
		// 如果不指定保存文件名，则由系统默认
		if(empty($sSavePath)){
			$sSavePath=$this->_sSavePath;
		}

		// 检查上传目录
		if(!is_dir($sSavePath)){
			if(is_dir(base64_decode($sSavePath))){
				$sSavePath=base64_decode($sSavePath);
			}else{
				if(!$this->_bAutoCreateStoreDir){
					$this->_sError(Q::L("存储目录不存在：“%s”",'__QEEPHP__@Q',null,$sSavePath));
					return false;
				}else if(!C::makeDir($sSavePath)){
					$this->_sError=Q::L('上传目录%s不可写','__QEEPHP__@Q',null,$sSavePath);
					return false;
				}
			}
		}else{
			if(!is_writeable($sSavePath)){
				$this->_sError=Q::L('上传目录%s不可写','__QEEPHP__@Q',null,$sSavePath);
				return false;
			}
		}

		$arrFileInfo=array();
		$bIsUpload=false;

		if(!isset($_FILES[$this->_sUploadifyDataName])){
			Q::E(sprintf('File input name %s not exists',$this->_sUploadifyDataName));
		}
		$arrFile=$_FILES[$this->_sUploadifyDataName];
		if(!empty($arrFile['name'])){
			$this->_sLastInput=$arrFile['name'];
			$arrFile['key']=0;// 登记上传文件的扩展信息
			$arrFile['extension']=strtolower($this->getExt($arrFile['name']));
			$arrFile['savepath']=$sSavePath;
			$arrFile['savename']=$this->getSaveName($arrFile);
			$arrFile['isthumb']=$this->_bThumb?1:0;
			$arrFile['thumbprefix']=$this->_sThumbPrefix;
			$arrFile['thumbpath']=$this->_sThumbPath;
			$arrFile['module']=MODULE_NAME;

			if($this->_bAutoCheck){// 自动检查附件
				if(!$this->check($arrFile)){
					return false;
				}
			}

			if(!$this->save($arrFile)){
				return false;
			}

			if(function_exists($this->_sHashType)){
				$sFun=$this->_sHashType;
				$arrFile['hash']=$sFun(C::gbkToUtf8($arrFile['savepath'].'/'.$arrFile['savename'],'utf-8','gb2312'));
			}

			unset($arrFile['tmp_name'],$arrFile['error']);// 上传成功后保存文件信息，供其他地方调用

			$this->_arrLastFileinfo=$arrFile;
			$arrFileInfo[]=$arrFile;
			$bIsUpload=true;
		}

		if($bIsUpload){
			$this->_arrUploadFileInfo=$arrFileInfo;
			return true;
		}else{
			$this->error(self::UPLOAD_ERR_PARTIAL);
			return false;
		}
	}

	public function setUploadifyDataName($sUploadifyDataName='Filedata'){
		$sOldValue=$this->_sUploadifyDataName;
		$this->_sUploadifyDataName=$sUploadifyDataName;
		return $sOldValue;
	}

	public function getUploadifyDataName(){
		return $this->_sUploadifyDataName;
	}

}
