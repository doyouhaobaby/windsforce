<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   头像处理相关函数($$)*/

!defined('Q_PATH') && exit;

class Avatar_Extend{

	static public function avatarTemp(){
		$nUploadfileMaxsize=Core_Extend::getUploadSize($GLOBALS['_option_']['avatar_uploadfile_maxsize']);
		
		$oUploadfile=new UploadFileForUploadify($nUploadfileMaxsize,array('gif','jpg','jpeg','png'),'',WINDSFORCE_PATH.'/user/avatar');

		$oUploadfile->_sSaveRule=array('Avatar_Extend','get_temp_avatar');
		$oUploadfile->setUploadifyDataName('image');
		$oUploadfile->setUploadReplace(TRUE);
		$oUploadfile->setAutoCreateStoreDir(TRUE);

		return $oUploadfile;
	}

	static public function get_temp_avatar($sData){
		return 'temp/temp_'. $GLOBALS['___login___']['user_id'].'.'.C::getExtName($sData,2);
	}

	static public function saveCrop(){
		$sSrc=Q::G('temp_image');
		$sSrc=WINDSFORCE_PATH.'/user/avatar/'.$sSrc;
		if(!is_file($sSrc)){
			Q::E(Q::L('无法获取裁剪图像','__COMMON_LANG__@Common'));
		}

		$arrPhotoInfo=@getimagesize($sSrc);

		if(function_exists('imagecreatetruecolor') && function_exists('imagecopyresampled')){
			$sImageCreateFromFunc='';
			switch($arrPhotoInfo['mime']){
				case 'image/jpeg':
					$sImageCreateFromFunc=function_exists('imagecreatefromjpeg')? 'imagecreatefromjpeg':'';
					break;
				case 'image/gif':
					$sImageCreateFromFunc=function_exists('imagecreatefromgif')? 'imagecreatefromgif':'';
					break;
				case 'image/png':
					$sImageCreateFromFunc=function_exists('imagecreatefrompng')? 'imagecreatefrompng':'';
					break;
			}

			$oImgR=$sImageCreateFromFunc($sSrc);
			$oDstR=ImageCreateTrueColor(200,200);
			imagecopyresampled($oDstR,$oImgR,0,0,intval(Q::G('x','P')),intval(Q::G('y','P')),200,200,intval(Q::G('w','P')),intval(Q::G('h','P')));
			$sOutfile=WINDSFORCE_PATH.'/user/avatar/'.C::getAvatar($GLOBALS['___login___']['user_id'],'big');
			self::createFileDir($sOutfile);
			imagejpeg($oDstR,$sOutfile,100);
			imagedestroy($oDstR);

			self::createThumbs($sOutfile,$sImageCreateFromFunc);// 创建缩略图
			self::deleteHigh($sSrc);// 删除原图
			
			return true;
		}else{
			return false;
		}
	}

	static public function createFileDir($sDir){
		if(!is_dir(dirname($sDir)) && !C::makeDir(dirname($sDir))){
			Q::E(Q::L('上传目录%s不可写','__COMMON_LANG__@Common',null,dirname($sDir)));
		}
	}

	static public function createThumbs($sOutfile){
		$arrThumbs=array(
			array($sOutfile,WINDSFORCE_PATH.'/user/avatar/'.C::getAvatar($GLOBALS['___login___']['user_id'],'middle'),array(120,120)),
			array($sOutfile,WINDSFORCE_PATH.'/user/avatar/'.C::getAvatar($GLOBALS['___login___']['user_id'],'small'),array(48,48)),
		);

		foreach($arrThumbs as $arrThumb){
			self::createThumb($arrThumb[0],$arrThumb[1],$arrThumb[2]);
		}
	}

	static public function createThumb($sFilename,$sThumbPath,$arrSize=array()){
		self::createFileDir($sThumbPath);
		Image::thumb($sFilename,$sThumbPath,'',$arrSize[0],$arrSize[1],true);
	}

	static public function deleteHigh($sSrc){
		if(is_file($sSrc)){
			@unlink($sSrc);
		}
	}

	static public function deleteAvatar($nUserId=null){
		if($nUserId===null){
			$nUserId=$GLOBALS['___login___']['user_id'];
		}

		foreach(array('big','middle','small') as $sValue){
			$sAvatarfile=WINDSFORCE_PATH.'/user/avatar/'.C::getAvatar($nUserId,$sValue);
			if(is_file($sAvatarfile)){
				@unlink($sAvatarfile);
			}
		}

		$oUser=UserModel::F('user_id=?',$nUserId)->getOne();
		if(!empty($oUser['user_id'])){
			$oUser->user_avatar='0';
			$oUser->setAutofill(false);
			$oUser->save('update');
			if($oUser->isError()){
				Q::E($oUser->getErrorMessage());
			}
		}
	}

}
