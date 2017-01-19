<?php
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   图像处理($$)*/

!defined('Q_PATH') && exit;

class Image{

	static public function getImageInfo($sImagesPath){
		$arrImageInfo=getimagesize($sImagesPath);

		if($arrImageInfo!==false){
			$sImageType=strtolower(substr(image_type_to_extension($arrImageInfo[2]),1));
			$nImageSize=filesize($sImagesPath);
			return array(
				'width'=>$arrImageInfo[0],
				'height'=>$arrImageInfo[1],
				'type'=>$sImageType,
				'size'=>$nImageSize,
				'mime'=>$arrImageInfo['mime']
			);
		}else{
			return false;
		}
	}

	static public function thumb($sImage,$sThumbName,$sType='',$nMaxWidth=200,$nMaxHeight=50,$bInterlace=true,$bFixed=false,$nQuality=100){
		// 获取原图信息
		$arrInfo=self::getImageInfo($sImage);

		if($arrInfo!==false){
			$nSrcWidth=$arrInfo['width'];
			$nSrcHeight=$arrInfo['height'];
			$sType=empty($sType)?$arrInfo['type']:$sType;
			$sType=strtolower($sType);
			$bInterlace=$bInterlace? 1:0;
			unset($arrInfo);
			$nScale=min($nMaxWidth/$nSrcWidth,$nMaxHeight/$nSrcHeight);// 计算缩放比例

			if($bFixed===true){
				$nWidth=$nMaxWidth;
				$nHeight=$nMaxHeight;
			}else{
				// 超过原图大小不再缩略
				if($nScale>=1){
					$nWidth=$nSrcWidth;
					$nHeight=$nSrcHeight;
				}else{// 缩略图尺寸
					$nWidth=(int)($nSrcWidth*$nScale);
					$nHeight=(int)($nSrcHeight*$nScale);
				}
			}

			$sCreateFun='ImageCreateFrom'.($sType=='jpg'?'jpeg':$sType);// 载入原图
			$oSrcImg=$sCreateFun($sImage);

			// 创建缩略图
			if($sType!='gif' && function_exists('imagecreatetruecolor')){
				$oThumbImg=imagecreatetruecolor($nWidth,$nHeight);
			}else{
				$oThumbImg=imagecreate($nWidth,$nHeight);
			}

			// 复制图片
			if(function_exists("ImageCopyResampled")){
				imagecopyresampled($oThumbImg,$oSrcImg,0,0,0,0,$nWidth,$nHeight,$nSrcWidth,$nSrcHeight);
			}else{
				imagecopyresized($oThumbImg,$oSrcImg,0,0,0,0,$nWidth,$nHeight,$nSrcWidth,$nSrcHeight);
			}

			if('gif'==$sType || 'png'==$sType){
				imagealphablending($oThumbImg,false); // 取消默认的混色模式
				$oBackgroundColor=imagecolorallocate($oThumbImg,0,255,0);  // 指派一个绿色
				imagecolortransparent($oThumbImg,$oBackgroundColor);  // 设置为透明色，若注释掉该行则输出绿色的图
			}

			// 对jpeg图形设置隔行扫描
			if('jpg'==$sType || 'jpeg'==$sType){
				imageinterlace($oThumbImg,$bInterlace);
			}

			if($sType=='png'){
				$nQuality=ceil($nQuality/10)-1;
				if($nQuality<0){
					$nQuality=0;
				}
			}

			$sImageFun='image'.($sType=='jpg'?'jpeg':$sType);// 生成图片
			$sImageFun($oThumbImg,$sThumbName,$nQuality);
			imagedestroy($oThumbImg);
			imagedestroy($oSrcImg);

			return $sThumbName;
		}

		return false;
	}

	static public function thumbGd($sTargetFile,$nThumbWidth,$nThumbHeight){
		$arrAttachInfo=@getimagesize($sTargetFile);

		list($nImgW,$nImgH)=$arrAttachInfo;
		header('Content-type:'.$arrAttachInfo['mime']);

		if($nImgW>=$nThumbWidth || $nImgH>=$nThumbHeight){
			if(function_exists('imagecreatetruecolor') && function_exists('imagecopyresampled') && function_exists('imagejpeg')){
				switch($arrAttachInfo['mime']){
					case 'image/jpeg':
						$sImageCreateFromFunc=function_exists('imagecreatefromjpeg')?'imagecreatefromjpeg':'';
						$sImageFunc=function_exists('imagejpeg')?'imagejpeg':'';
						break;
					case 'image/gif':
						$sImageCreateFromFunc=function_exists('imagecreatefromgif')?'imagecreatefromgif':'';
						$sImageFunc=function_exists('imagegif')?'imagegif':'';
						break;
					case 'image/png':
						$sImageCreateFromFunc=function_exists('imagecreatefrompng')?'imagecreatefrompng':'';
						$sImageFunc=function_exists('imagepng')?'imagepng':'';
						break;
				}

				$oAttachPhoto=$sImageCreateFromFunc($sTargetFile);

				$nXRatio=$nThumbWidth/$nImgW;
				$nYRatio=$nThumbHeight/$nImgH;

				if(($nXRatio*$nImgH)<$nThumbHeight){
					$arrThumb['height']=ceil($nXRatio*$nImgH);
					$arrThumb['width']=$nThumbWidth;
				}else{
					$arrThumb['width']=ceil($nYRatio*$nImgW);
					$arrThumb['height']=$nThumbHeight;
				}

				$oThumbPhoto=@imagecreatetruecolor($arrThumb['width'],$arrThumb['height']);
				if($arrAttachInfo['mime']!='image/jpeg'){
					$oAlpha=imagecolorallocatealpha($oThumbPhoto,0,0,0,127);
					imagefill($oThumbPhoto,0,0,$oAlpha);
				}

				@imageCopyreSampled($oThumbPhoto,$oAttachPhoto,0,0,0,0,$arrThumb['width'],$arrThumb['height'],$nImgW,$nImgH);
				if($arrAttachInfo['mime']!='image/jpeg'){
					imagesavealpha($oThumbPhoto,true);
				}
				clearstatcache();

				if($arrAttachInfo['mime']=='image/jpeg'){
					$sImageFunc($oThumbPhoto,null,90);
				}else{
					$sImageFunc($oThumbPhoto);
				}
			}
		}else{
			readfile($sTargetFile);
			exit;
		}
	}

	static public function outerImage($sUrl,$sFilename=''){
		if($sUrl==''){
			return false;
		}

		if($sFilename==''){
			$sExt=strtolower(strrchr($sUrl,'.'));
			if(!in_array($sExt,array('.gif','.png','.jpg','.jpeg','.bmp'))){
				return false;
			}
			$sFilename=date("YmdHis").$sExt;
		}

		ob_start();
		readfile($sUrl);
		$sImg=ob_get_contents();
		ob_end_clean();
		$nSize=strlen($sImg);
		$oFp=@fopen($sFilename,'a');
		fwrite($oFp,$sImg);
		fclose($oFp);
		
		return $sFilename;
	}

	static public function gdVersion(){
		$nVersion=-1;

		if($nVersion>=0){
			return $nVersion;
		}

		if(!extension_loaded('gd')){
			$nVersion=0;
		}else{
			if(PHP_VERSION >='4.3'){// 尝试使用gd_info函数
				if(function_exists('gd_info')){
					$arrVerInfo=gd_info();
					preg_match('/\d/',$arrVerInfo['GD Version'],$arrMatch);
					$nVersion=$arrMatch[0];
				}else{
					if(function_exists('imagecreatetruecolor')){
						$nVersion=2;
					}elseif(function_exists('imagecreate')){
						$nVersion=1;
					}
				}
			}else{
				if(preg_match('/phpinfo/',ini_get('disable_functions'))){
					$nVersion=1;// 如果phpinfo被禁用，无法确定gd版本
				}else{
					ob_start();// 使用phpinfo函数
					phpinfo(8);
					$sInfo=ob_get_contents();
					ob_end_clean();

					$sInfo=stristr($sInfo,'gd version');
					preg_match('/\d/',$sInfo,$arrMatch);
					$nVersion=$arrMatch[0];
				}
			}
		}

		return $nVersion;
	}

	static public function imageWaterMark($sBackgroundPath,$nWaterPos=0,$arrWaterArgs,$bDeleteBackgroupPath=true){
		$bIsWaterImage=FALSE;

		if(!empty($sBackgroundPath) && is_file($sBackgroundPath)){// 读取背景图片
			$arrBackgroundInfo=@getimagesize($sBackgroundPath);
			$nGroundWidth=$arrBackgroundInfo[0]; // 取得背景图片的宽
			$nGroundHeight=$arrBackgroundInfo[1]; // 取得背景图片的高
			switch($arrBackgroundInfo[2]){// 取得背景图片的格式
				case 1:
					$oBackgroundIm=@imagecreatefromgif($sBackgroundPath);break;
				case 2:
					$oBackgroundIm=@imagecreatefromjpeg($sBackgroundPath);break;
				case 3:
					$oBackgroundIm=@imagecreatefrompng($sBackgroundPath);break;
				default:
					return Q::L("错误的图像格式！",'__QEEPHP__@Q');
			}
		}else{
			return Q::L("图像%d为空或者不存在！",'__QEEPHP__@Q',null,$sBackgroundPath);
		}

		@imagealphablending($oBackgroundIm,true);// 设定图像的混色模式
		if(!empty($sBackgroundPath) && is_file($sBackgroundPath)){
			if($arrWaterArgs['type']=='img' && !empty($arrWaterArgs['path'])){
				$bIsWaterImage=TRUE;
				$nSet=0;

				$nOffset=!empty($arrWaterArgs['offset'])?$arrWaterArgs['offset']:0;
				if(strpos($arrWaterArgs,'http://localhost/')==0 || strpos($arrWaterArgs,'https://localhost/')==0){// localhost 转127.0.0.1,否则将会错误
					$arrWaterArgs['path']=str_replace('localhost','127.0.0.1',$arrWaterArgs['path']);
				}

				$arrWaterInfo=@getimagesize($arrWaterArgs['path']);
				$nWaterWidth=$arrWaterInfo[0]; // 取得水印图片的宽
				$nWaterHeight=$arrWaterInfo[1]; // 取得水印图片的高
				switch($arrWaterInfo[2]){// 取得水印图片的格式
					case 1:
						$oWaterIm=@imagecreatefromgif($arrWaterArgs['path']);
						break;
					case 2:
						$oWaterIm=@imagecreatefromjpeg($arrWaterArgs['path']);
						break;
					case 3:
						$oWaterIm=@imagecreatefrompng($arrWaterArgs['path']);
						break;
					default:
						return Q::L("错误的图像格式！",'__QEEPHP__@Q');
				}
			}elseif($arrWaterArgs['type']==='text' && $arrWaterArgs['content']!=''){
				$bFrameworkFont=false;
				if(isset($arrWaterArgs['textPath']) && strtolower($arrWaterArgs['textPath'])=='framework-font'){// 使用框架内部提供的字体
					$arrWaterArgs['textPath']=Q_PATH.'/Resource_/Images/Seccode/Fonts';
					$bFrameworkFont=true;
				}

				$sFontfileTemp=$sFontfile=isset($arrWaterArgs['textFile']) && !empty($arrWaterArgs['textFile'])?$arrWaterArgs['textFile']:'FetteSteinschrift.ttf';
				$sFontfile=(!empty($arrWaterArgs['textPath'])?C::tidyPath($arrWaterArgs['textPath']).($bFrameworkFont===true?'/En-us':''):'C:\WINDOWS\Fonts').'/'. $sFontfile ;
				if($bFrameworkFont===true && !is_file($sFontfile)){// 这里如果为框架内部字体的话，在En-us中找不到，那么到Zh-cn中去找字体
					$sFontfile=C::tidyPath($arrWaterArgs['textPath']).'/Zh-cn/'.$sFontfileTemp;
				}

				if(!is_file($sFontfile)){
					return Q::L("字体文件%s无法找到！",'__QEEPHP__@Q',null,$sFontfile);
				}

				$sWaterText=$arrWaterArgs['content'];
				$nSet=1;
				$nOffset=!empty($arrWaterArgs['offset'])?$arrWaterArgs['offset']:5;
				$sTextColor=empty($arrWaterArgs['textColor'])?'#FF0000': $arrWaterArgs['textColor'];
				$nTextFont=!isset($arrWaterArgs['textFont']) || empty($arrWaterArgs['textFont'])?20:$arrWaterArgs['textFont'];
				$arrTemp=@imagettfbbox(ceil($nTextFont),0,$sFontfile,$sWaterText);//取得使用 TrueType 字体的文本的范围
				$nWaterWidth=$arrTemp[2]-$arrTemp[6];
				$nWaterHeight=$arrTemp[3]-$arrTemp[7];
				unset($arrTemp);
			}else{
				return Q::L("水印参数type不为img 和 text！",'__QEEPHP__@Q');
			}
		}else{
			return Q::L("水印参数不为数组！",'__QEEPHP__@Q');
		}

		if(($nGroundWidth<($nWaterWidth*2)) || ($nGroundHeight<($nWaterHeight*2))){// 如果水印占了原图一半就不搞水印了.影响浏览.抵制影响正常浏览的广告
			return true;
		}

		switch($nWaterPos){
			case 1:// 1为顶端居左
				$nPosX=$nOffset*$nSet;
				$nPosY=($nWaterHeight+$nOffset)*$nSet;
				break;
			case 2:// 2为顶端居中
				$nPosX=($nGroundWidth-$nWaterWidth)/2;
				$nPosY=($nWaterHeight+$nOffset)*$nSet;
				break;
			case 3:// 3为顶端居右
				$nPosX=$nGroundWidth-$nWaterWidth-$nOffset*$nSet;
				$nPosY=($nWaterHeight+$nOffset)*$nSet;
				break;
			case 4:// 4为中部居左
				$nPosX=$nOffset*$nSet;
				$nPosY=($nGroundHeight-$nWaterHeight)/2;
				break;
			case 5:// 5为中部居中
				$nPosX=($nGroundWidth-$nWaterWidth)/2;
				$nPosY=($nGroundHeight-$nWaterHeight)/2;
				break;
			case 6:// 6为中部居右
				$nPosX=$nGroundWidth-$nWaterWidth-$nOffset*$nSet;
				$nPosY=($nGroundHeight-$nWaterHeight)/2;
				break;
			case 7:// 7为底端居左
				$nPosX=$nOffset*$nSet;
				$nPosY=$nGroundHeight-$nWaterHeight;
				break;
			case 8:// 8为底端居中
				$nPosX=($nGroundWidth-$nWaterWidth)/2;
				$nPosY=$nGroundHeight-$nWaterHeight;
				break;
			case 9:// 9为底端居右
				$nPosX=$nGroundWidth-$nWaterWidth-$nOffset*$nSet;
				$nPosY=$nGroundHeight -$nWaterHeight;
				break;
			default:// 随机
				$nPosX=rand(0,($nGroundWidth-$nWaterWidth));
				$nPosY=rand(0,($nGroundHeight-$nWaterHeight));
				break;
		}

		if($bIsWaterImage===TRUE){//图片水印
			@imagealphablending($oWaterIm,true);
			@imagealphablending($oBackgroundIm,true);
			@imagecopy($oBackgroundIm,$oWaterIm,$nPosX,$nPosY,0,0,$nWaterWidth,$nWaterHeight); // 拷贝水印到目标文件
		}else{ //文字水印
			if(!empty($sTextColor) &&(strlen($sTextColor)==7)){
				$R=hexdec(substr($sTextColor,1,2));
				$G=hexdec(substr($sTextColor,3,2));
				$B=hexdec(substr($sTextColor,5));
			}else{
				return Q::L("水印文字颜色错误！",'__QEEPHP__@Q');
			}
			@imagettftext($oBackgroundIm,$nTextFont,0,$nPosX,$nPosY,@imagecolorallocate($oBackgroundIm,$R,$G,$B),$sFontfile ,$sWaterText);
		}

		if($bDeleteBackgroupPath===true){// 生成水印后的图片
			@unlink($sBackgroundPath);
		}

		switch($arrBackgroundInfo[2]){// 取得背景图片的格式
			case 1:
				@imagegif($oBackgroundIm,$sBackgroundPath);
				break;
			case 2:
				@imagejpeg($oBackgroundIm,$sBackgroundPath);
				break;
			case 3:
				@imagepng($oBackgroundIm,$sBackgroundPath);
				break;
			default:
				return Q::L("错误的图像格式！",'__QEEPHP__@Q');
		}

		if(isset($oWaterIm)){
			@imagedestroy($oWaterIm);
		}
		@imagedestroy($oBackgroundIm);

		return true;
	}

	static function changeImgSize($sImgPath,$nMaxWidth,$nMaxHeight){
		$arrSize=@getimagesize($sImgPath);

		$nW=$arrSize[0];
		$nH=$arrSize[1];

		@$nWRatio=$nMaxWidth/$nW;// 计算缩放比例
		@$nHRatio=$nMaxHeight/$nH;

		if(($nW<=$nMaxWidth) && ($nH<=$nMaxHeight)){// 决定处理后的图片宽和高
			$arrTn['w']=$nW;
			$arrTn['h']=$nH;
		}else if(($nWRatio*$nH)<$nMaxHeight){
			$arrTn['h']=ceil($nWRatio*$nH);
			$arrTn['w']=$nMaxWidth;
		}else{
			$arrTn['w']=ceil($nHRatio*$nW);
			$arrTn['h']=$nMaxHeight;
		}

		$arrTn['rc_w']=$nW;
		$arrTn['rc_h']=$nH;

		return $arrTn;
	}

	static public function output($oImage,$sType='png',$sFilename=''){
		header("Content-type: image/".$sType);

		$sImageFun='image'.$sType;
		if(empty($sFilename)){
			$sImageFun($oImage);
		}else{
			$sImageFun($oImage,$sFilename);
		}

		@imagedestroy($oImage);
	}

	static public function resizeImage($sImageFile,$sTargetFile='',$nWidth=80,$nHeight=80,$bCut=true,$sCorner='',$nCornerRadius=6,$nAngle=0){
		$sType=substr(strrchr($sImageFile,"."),1);// 图片的类型

		$oIm=null;// 初始化图象
		if($sType=="jpg"){
			$oIm=imagecreatefromjpeg($sImageFile);
		}

		if($sType=="gif"){
			$oIm=imagecreatefromgif($sImageFile);
		}

		if($sType=="png"){
			$oIm=imagecreatefrompng($sImageFile);
		}

		if($oIm===null){
			return Q::L("错误的图像格式！",'__QEEPHP__@Q');
		}

		// 图象目标地址
		if($sTargetFile===''){
			$nFullLength=strlen($sImageFile);
			$nTypeLength=strlen($sType);
			$nNameLength=$nFullLength-$nTypeLength;
			$sName=substr($sImageFile,0,$nNameLength-1);
			$sTargetFile=$sName."_small.png";
		}

		if($sTargetFile==$sImageFile){
			return Q::L("裁剪的图像和原图像地址一样！",'__QEEPHP__@Q');
		}

		if(is_file($sTargetFile)){
			return Q::L("保存的图像已经存在！",'__QEEPHP__@Q');
		}

		$nSourceWidth=imagesx($oIm);// 取得图像地址
		$nSourceHeight=imagesy($oIm);
		$nResizeRatio=($nWidth)/($nHeight);// 改变后的图象的比例

		// 初始化圆角信息
		if($sCorner==''){
			$sCorner=Q_PATH.'/Resource_/Images/rounded_corner.png';
		}

		$nRatio=($nSourceWidth)/($nSourceHeight);// 实际图象的比例
		if($bCut===TRUE){// 裁图
			if($nRatio>=$nResizeRatio){// 高度优先
				$oNewImg=imagecreatetruecolor($nWidth,$nHeight);
				imagecopyresampled($oNewImg,$oIm,0,0,0,0,$nWidth,$nHeight,(($nSourceHeight)*$nResizeRatio),$nSourceHeight);
				$oTmp=self::roundedCorner($oNewImg,$nWidth,$sCorner,$nCornerRadius,$nAngle);
				imagepng($oTmp,$sTargetFile);
			}

			if($nRatio<$nResizeRatio){// 宽度优先
				$oNewImg=imagecreatetruecolor($nWidth,$nHeight);
				imagecopyresampled($oNewImg,$oIm,0,0,0,0,$nWidth,$nHeight,$nSourceWidth,(($nSourceWidth)/$nResizeRatio));
				$oTmp=self::roundedCorner($oNewImg,0,$sCorner,$nCornerRadius,$nAngle);
				imagepng($oTmp,$sTargetFile);
			}
		}else{ //不裁图
			if($nRatio>=$nResizeRatio){
				$oNewImg=imagecreatetruecolor($nWidth,($nWidth)/$nRatio);
				imagecopyresampled($oNewImg,$oIm,0,0,0,0,$nWidth,($nWidth)/$nRatio,$nSourceWidth,$nSourceHeight);
				ImageJpeg($oNewImg,$sTargetFile);
			}

			if($nRatio<$nResizeRatio){
				$oNewImg=imagecreatetruecolor(($nHeight)*$nRatio,$nHeight);
				imagecopyresampled($oNewImg,$oIm,0,0,0,0,($nHeight)*$nRatio,$nHeight,$nSourceWidth,$nSourceHeight);
				ImageJpeg($oNewImg,$sTargetFile);
			}
		}

		return true;
	}

	static public function roundedCorner($oImage,$nSize=0,$sCorner='',$nCornerRadius=6,$nAngle=0){
		$bTopLeft=true;
		$bBottomLeft=true;
		$bBottomRight=true;
		$bTopRight=true;

		$oCornerSource=imagecreatefrompng($sCorner);
		$nCornerWidth=imagesx($oCornerSource);
		$nCornerHeight=imagesy($oCornerSource);
		$oCornerResized=ImageCreateTrueColor($nCornerRadius,$nCornerRadius);
		ImageCopyResampled($oCornerResized,$oCornerSource,0,0,0,0,$nCornerRadius,$nCornerRadius,$nCornerWidth,$nCornerHeight);
		$nCornerWidth=imagesx($oCornerResized);
		$nCornerHeight=imagesy($oCornerResized);
		$oWhite=ImageColorAllocate($oImage,255,255,255);
		$oBlack=ImageColorAllocate($oImage,0,0,0);

		if($bTopLeft==true){// 顶部左圆角
			$nDestX=0;
			$nDestY=0;
			imagecolortransparent($oCornerResized,$oBlack);
			imagecopymerge($oImage,$oCornerResized,$nDestX,$nDestY,0,0,$nCornerWidth,$nCornerHeight,100);
		}

		if($bBottomLeft==true){// 下部左圆角
			$nDestX=0;
			$nDestY=$nSize-$nCornerHeight;
			$oRotated=imagerotate($oCornerResized,90,0);
			imagecolortransparent($oRotated,$oBlack);
			imagecopymerge($oImage,$oRotated,$nDestX,$nDestY,0,0,$nCornerWidth,$nCornerHeight,100);
		}

		if($bBottomRight==true){// 下部右圆角
			$nDestX=$nSize-$nCornerWidth;
			$nDestY=$nSize-$nCornerHeight;
			$oRotated=imagerotate($oCornerResized,180,0);
			imagecolortransparent($oRotated,$oBlack);
			imagecopymerge($oImage,$oRotated,$nDestX,$nDestY,0,0,$nCornerWidth,$nCornerHeight,100);
		}

		if($bTopRight==true){// 顶部右圆角
			$nDestX=$nSize-$nCornerWidth;
			$nDestY=0;
			$oRotated=imagerotate($oCornerResized,270,0);
			imagecolortransparent($oRotated,$oBlack);
			imagecopymerge($oImage,$oRotated,$nDestX,$nDestY,0,0,$nCornerWidth,$nCornerHeight,100);
		}

		$oImage=imagerotate($oImage,$nAngle,$oWhite);

		return $oImage;
	}

}
