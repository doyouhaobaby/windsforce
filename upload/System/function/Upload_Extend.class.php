<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   文件上传相关函数($$)*/

!defined('Q_PATH') && exit;

class Upload_Extend{

	public static function getUploadDir(){
		$sUploadDir='';
		
		$sUploadStoreType=$GLOBALS['_option_']['upload_store_type'];
		if($sUploadStoreType=='month'){
			$sUploadDir='/month_'.date('Ym',CURRENT_TIMESTAMP);
		}elseif($sUploadStoreType=='day'){
			$sUploadDir='/day_'.date('Ymd',CURRENT_TIMESTAMP);
		}else{
			$sUploadDir='';
		}
		
		return $sUploadDir;
	}
	
	public static function getIconName($sType,$nId,$sExt='icon'){
		return $sType.'_'.$nId.'_'.$sExt;
	}

	public static function uploadIcon($sType,$arrUploadoption=array()){
		if(empty($_FILES)){
			Q::E(Q::L('你没有选择任何文件','__COMMON_LANG__@Common'));
		}

		if(empty($GLOBALS['_cache_'][$sType.'_option'])){
			Core_Extend::loadCache($sType.'_option');
		}

		$arrDefaultoption=array(
			'uploadfile_maxsize'=>$GLOBALS['_cache_'][$sType.'_option'][$sType.'_icon_uploadfile_maxsize'],
			'width'=>120,
			'height'=>120,
			'upload_path'=>WINDSFORCE_PATH.'/user/attachment/app/'.strtolower($sType).'/icon',
			'upload_saverule'=>'',
		);

		$arrUploadoption=array_merge($arrDefaultoption,$arrUploadoption);

		$sUploadDir=Upload_Extend::getUploadDir();
		$nUploadfileMaxsize=Core_Extend::getUploadSize($arrUploadoption['uploadfile_maxsize']);
		$oUploadfile=new UploadFile($nUploadfileMaxsize,array('gif','jpeg','jpg','png'),'',$arrUploadoption['upload_path'].$sUploadDir);
		
		// 缩略图设置
		$oUploadfile->_sThumbPrefix='';
		$oUploadfile->_bThumb=true;
		$oUploadfile->_nThumbMaxHeight=$arrUploadoption['height'];
		$oUploadfile->_nThumbMaxWidth=$arrUploadoption['width'];
		$oUploadfile->_sThumbPath=$arrUploadoption['upload_path'].$sUploadDir;
		$oUploadfile->_bThumbRemoveOrigin=FALSE;
		$oUploadfile->_bThumbFixed=true;
		$oUploadfile->_bUploadReplace=true;
			
		// 文件上传规则
		if(empty($arrUploadoption['upload_saverule'])){
			$oUploadfile->_sSaveRule=array(ucfirst($sType).'_Extend','getIconName');
		}else{
			$oUploadfile->_sSaveRule=$arrUploadoption['upload_saverule'];
		}
			
		// 自动创建附件储存目录
		$oUploadfile->setAutoCreateStoreDir(TRUE);
			
		$sPhotoDir='';
		$arrUploadInfo=array();
		if(!$oUploadfile->upload()){
			Q::E($oUploadfile->getErrorMessage());
		}else{
			$arrPhotoInfo=$oUploadfile->getUploadFileInfo();
			$sPhotoDir=str_replace(C::tidyPath($arrUploadoption['upload_path']).'/','',C::tidyPath($arrPhotoInfo[0]['thumbpath'])).'/'.$arrPhotoInfo[0]['savename'];
		}
		
		return $sPhotoDir;
	}
	
	public static function deleteicon($sType,$sUrl,$arrUploadoption=array()){
		$arrDefaultoption=array(
			'upload_path'=>WINDSFORCE_PATH.'/user/attachment/app/'.strtolower($sType).'/icon',
		);

		$arrUploadoption=array_merge($arrDefaultoption,$arrUploadoption);
		
		$sFile=$arrUploadoption['upload_path'].'/'.$sUrl;
		$sDir=dirname($sFile);
		if(is_file($sFile)){
			@unlink($sFile);
		}

		$arrFiles=C::listDir($sDir,false,true);
		if(count($arrFiles)==1 && $arrFiles[0]=='index.html'){
			if(is_file($sDir.'/index.html')){
				@unlink($sDir.'/index.html');
			}

			if(is_dir($sDir)){
				@rmdir($sDir);
			}
		}
	}

	public static function getUploadSavename($sFilename){
		return md5(md5($sFilename).gmdate('YmdHis').C::randString(6)).'.'.C::getExtName($sFilename,2);
	}

	public static function uploadFlash($bUploadFlash=true,$bReturnUploadinfo=false,$bDatabase=true,$arrUploadoption=array()){
		if(empty($_FILES)){
			Q::E(Q::L('你没有选择任何文件','__COMMON_LANG__@Common'));
			return;
		}

		$arrDefaultoption=array(
			'upload_allowed_type'=>$GLOBALS['_option_']['upload_allowed_type'],
			'uploadfile_maxsize'=>$GLOBALS['_option_']['uploadfile_maxsize'],
			'upload_create_thumb'=>$GLOBALS['_option_']['upload_create_thumb'],
			'upload_thumb_size'=>$GLOBALS['_option_']['upload_thumb_size'],
			'upload_is_watermark'=>$GLOBALS['_option_']['upload_is_watermark'],
			'upload_images_watertype'=>$GLOBALS['_option_']['upload_images_watertype'],
			'upload_watermark_imgurl'=>$GLOBALS['_option_']['upload_watermark_imgurl'],
			'upload_imageswater_offset'=>$GLOBALS['_option_']['upload_imageswater_offset'],
			'upload_imageswater_text'=>$GLOBALS['_option_']['upload_imageswater_text'],
			'upload_imageswater_textcolor'=>$GLOBALS['_option_']['upload_imageswater_textcolor'],
			'upload_imageswater_textfontsize'=>$GLOBALS['_option_']['upload_imageswater_textfontsize'],
			'upload_imageswater_textfontpath'=>$GLOBALS['_option_']['upload_imageswater_textfontpath'],
			'upload_imageswater_textfonttype'=>$GLOBALS['_option_']['upload_imageswater_textfonttype'],
			'upload_imageswater_offset'=>$GLOBALS['_option_']['upload_imageswater_offset'],
			'upload_imageswater_position'=>$GLOBALS['_option_']['upload_imageswater_position'],
			'upload_path'=>WINDSFORCE_PATH.'/user/attachment',
			'upload_thumb'=>'thumb_',
			'flash_inputname'=>'Filedata',
		);

		$arrUploadoption=array_merge($arrDefaultoption,$arrUploadoption);
		$sUploadDir=self::getUploadDir();
		$arrAllAllowType=explode('|',$arrUploadoption['upload_allowed_type']);
		$nUploadfileMaxsize=Core_Extend::getUploadSize($arrUploadoption['uploadfile_maxsize']);

		if($bUploadFlash===true){
			$oUploadfile=new UploadFileForUploadify($nUploadfileMaxsize,$arrAllAllowType,'',$arrUploadoption['upload_path'].$sUploadDir);
			$oUploadfile->setUploadifyDataName($arrUploadoption['flash_inputname']);
		}else{
			$oUploadfile=new UploadFile($nUploadfileMaxsize,$arrAllAllowType,'',$arrUploadoption['upload_path'].$sUploadDir);
		}

		if($arrUploadoption['upload_create_thumb']==1){
			$oUploadfile->_bThumb=true;
			$arrThumbMax=explode('|',$arrUploadoption['upload_thumb_size']);
			$oUploadfile->_nThumbMaxHeight=$arrThumbMax[0];
			$oUploadfile->_nThumbMaxWidth=$arrThumbMax[1];
			$oUploadfile->_sThumbPath=$arrUploadoption['upload_path'].$sUploadDir.'/thumb';// 缩略图文件保存路径
			$oUploadfile->_sThumbPrefix=$arrUploadoption['upload_thumb'];
		}

		$oUploadfile->_sSaveRule=array('Upload_Extend','getUploadSavename');// 设置上传文件规则
		
		if($arrUploadoption['upload_is_watermark']==1){
			$oUploadfile->_bIsImagesWaterMark=true;
			$oUploadfile->_sImagesWaterMarkType=$arrUploadoption['upload_images_watertype'];
			$oUploadfile->_arrImagesWaterMarkImg=array(
				'path'=>$arrUploadoption['upload_watermark_imgurl'],
				'offset'=>$arrUploadoption['upload_imageswater_offset']
			);
			$oUploadfile->_arrImagesWaterMarkText=array(
				'content'=>$arrUploadoption['upload_imageswater_text'],
				'textColor'=>$arrUploadoption['upload_imageswater_textcolor'],
				'textFont'=>$arrUploadoption['upload_imageswater_textfontsize'],
				'textPath'=>$arrUploadoption['upload_imageswater_textfontpath'],
				'textFile'=>$arrUploadoption['upload_imageswater_textfonttype'],
				'offset'=>$arrUploadoption['upload_imageswater_offset']
			);
			$oUploadfile->_nWaterPos=$arrUploadoption['upload_imageswater_position'];
		}

		$oUploadfile->setAutoCreateStoreDir(TRUE);
		if(!$oUploadfile->upload()){
			Q::E($oUploadfile->getErrorMessage());
		}else{
			$arrPhotoInfo=$oUploadfile->getUploadFileInfo();
		}

		if($bDatabase===true){
			$oAttachment=Q::instance('AttachmentModel');
			$arrUploadids=$oAttachment->upload($arrPhotoInfo);
			if($oAttachment->isError()){
				Q::E($oAttachment->getErrorMessage());
				return false;
			}
			
			return $arrUploadids;
		}

		if($bReturnUploadinfo===true){
			return $arrPhotoInfo;
		}else{
			return true;
		}
	}

	public static function uploadNormal($bReturnUploadinfo=false,$bDatabase=true,$arrUploadoption=array()){
		return self::uploadFlash(false,$bReturnUploadinfo,$bDatabase,$arrUploadoption);
	}
	
}
