<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   后台模板相关函数($$)*/

!defined('Q_PATH') && exit;

class Admintheme_Extend{
	
	static public function path($sFile,$sType='images',$bReturnurl=true){
		static $sAdmintemplate='';

		if($sAdmintemplate==''){
			if(APP_NAME==='myadmin'){
				$sAdmintemplate='myadmin';
			}else{
				$sAdmintemplate=Q::cookie('admin_template');
			}
		}

		$sType=ucfirst(strtolower($sType));
		$sAdmintemplate=ucfirst($sAdmintemplate);

		if(is_file(WINDSFORCE_PATH.'/System/admin/Theme/'.$sAdmintemplate.'/Public/'.$sType.'/'.$sFile)){
			if($bReturnurl===true){
				return __TMPLPUB__.'/'.$sType.'/'.$sFile;
			}else{
				return WINDSFORCE_PATH.'/System/admin/Theme/'.$sAdmintemplate.'/Public/'.$sType.'/'.$sFile;
			}
		}else{
			if($bReturnurl===true){
				return __TMPLPUB_DEFAULT__.'/'.$sType.'/'.$sFile;
			}else{
				return WINDSFORCE_PATH.'/System/admin/Theme/Default/Public/'.$sType.'/'.$sFile;
			}
		}
	}

	static public function pathContent($sFiles,$sType='css'){
		if(self::isCached()){
			return;
		}
		
		// 系统支持JS和CSS两种类型的文件
		$arrTypes=array();
		$arrTypes["css"]["ext"]=".css";
		$arrTypes["css"]["content_type"]="text/css;charset=UTF-8";
		$arrTypes["js"]["ext"]=".js";
		$arrTypes["js"]["content_type"]="text/javascript;charset=UTF-8";

		$sType=strtolower($sType);
		
		if(empty($sType) || empty($sFiles)){
			header("HTTP/1.0 404",true,404);
			return;
		}

		if($sType=='css'){
			$arrConfig=$arrTypes["css"];
		}elseif($sType=='js'){
			$arrConfig=$arrTypes["js"];
		}else{
			header("HTTP/1.0 404",true,404);
			return;
		}

		$nTenyears=date("D, j M Y H:i:s T",CURRENT_TIMESTAMP+315360000);//attachment
		header("Content-disposition:inline;filename=".$sFiles.$arrConfig["ext"]);
		header("Expires: {$nTenyears}",true);
		header("Last-Modified: ".date("D, j M Y H:i:s T"),true);
		header("Content-Type: ".$arrConfig["content_type"],true);
		header("Cache-Control: max-age=315360000",true);
		header("Age: 87000",true);

		$arrFiles=explode("\|",str_replace(array("'","/","\/","\"","."),array('','','','',''),$sFiles));
		foreach($arrFiles as $sValue){
			if($sType=='js'){
				echo(self::javascriptCompress(self::path($sValue.$arrConfig["ext"],$sType,false)));
			}elseif($sType=='css'){
				echo(self::cssCompress(self::path($sValue.$arrConfig["ext"],$sType,false),$sValue));
			}

			echo "\n";
		}
	}

	static public function cssCompress($sCssfile,$sResourcefile=''){
		if(!is_file($sCssfile)){
			return '';
		}

		$sCssData=file_get_contents($sCssfile);
		$sCssData=@preg_replace("/\{([0-9a-zA-Z\_\-\.\/]+)\}/e",'Admintheme_Extend::cssPath(\'\1\',\''.$sResourcefile.'\')',stripslashes($sCssData));
		$sCssData=preg_replace("/<\?.+?\?>\s*/",'',$sCssData);
		$sCssData=preg_replace(array('/\s*([,;:\{\}])\s*/','/[\t\n\r]/','/\/\*.+?\*\//'),array('\\1','',''),$sCssData);

		/* 兼容性处理 */
		$sAdmintemplate=Q::cookie('admin_template');
		if(strpos($sCssfile,'admin/Theme/'.$sAdmintemplate.'/Public/Css/')!==false){
			$sTheme=$sAdmintemplate;
		}else{
			$sTheme='Default';
		}

		$sCssData=str_replace('../Images/',__ROOT__.'/System/admin/Theme/'.$sTheme.'/Public/Images/',$sCssData);

		return $sCssData;
	}

	static public function cssPath($sFile,$sResourcefile=''){
		$sExtName=C::getExtName($sFile,2);

		if($sExtName=='css'){
			if(strpos($sFile,'/')===false){
				if(!empty($sResourcefile) && $sResourcefile.'.'.$sExtName==$sFile){
					$sFile='';
				}else{
					$sFile=Admintheme_Extend::path($sFile,'css',false);
				}
			}else{
				$sFile=WINDSFORCE_PATH.'/'.$sFile;
			}

			return self::cssCompress($sFile);
		}else{
			if(strpos($sFile,'/')){
				return __ROOT__.'/'.$sFile;
			}else{
				return self::path($sFile);
			}
		}
	}

	static public function javascriptCompress($sJsfile){
		if(!is_file($sJsfile)){
			return '';
		}
	
		$arrRemove=array(
			'/(^|\r|\n)\/\*.+?(\r|\n)\*\/(\r|\n)/is',
			'/\/\/note.+?(\r|\n)/i',
			'/\/\/debug.+?(\r|\n)/i',
			'/(^|\r|\n)(\s|\t)+/',
			'/(\r|\n)/',
			"/\/\*(.*?)\*\//ies",
		);

		$sJavascritpData=file_get_contents($sJsfile);
		$sJavascritpData=preg_replace($arrRemove,'',$sJavascritpData);

		return $sJavascritpData;
	}
	
	static protected function isCached(){
		$sTmp=self::getRequestHeader("HTTP_PRAGMA");
		if(strcasecmp($sTmp,"no-cache")==0){
			return false;
		}

		$sTmp=self::getRequestHeader("HTTP_CACHE_CONTROL");
		if(strcasecmp($sTmp,"no-cache")==0){
			return false;
		}

		$sTmp=self::getRequestHeader("HTTP_IF_MODIFIED_SINCE");
		if(!is_null($sTmp)){
			header ("HTTP/1.0 304 Not Modified",true,304);
			return true;
		}else{
			return false;
		}
	}

	static protected function getRequestHeader($sName){
		if(!array_key_exists($sName,$_SERVER)){
			return null;
		}

		return trim($_SERVER[$sName]);
	}
	
}

/** 简化模板中的调用 */
class At extends Admintheme_Extend{}
