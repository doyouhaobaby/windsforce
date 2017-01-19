<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   主题缓存($$)*/

!defined('Q_PATH') && exit;

class UpdateCacheStyle{

	public static function cache(){
		$arrData=array();

		$arrStylevars=$arrStyledata=array();
		$nDefaultStyleid=$GLOBALS['_option_']['front_style_id'];
		$arrStylevarOjbects=Model::F_('stylevar')->getAll();
		if(is_array($arrStylevarOjbects)){
			foreach($arrStylevarOjbects as $oStylevar){
				$arrStylevars[$oStylevar['style_id']][$oStylevar['stylevar_variable']]=$oStylevar['stylevar_substitute'];
			}
		}

		$arrCacheStyledir=$arrTheStyles=array();
		$arrCacheStyledir=C::listDir(WINDSFORCE_PATH.'/~@~/style_');

		$arrStyleObjects=Model::F_('style','style_status=?',1)->getAll();
		if(is_array($arrStyleObjects)){
			foreach($arrStyleObjects as $arrStyle){
				$arrDataNew=array();
				$oTheme=Model::F_('theme','theme_id=?',$arrStyle['theme_id'])->getOne();
				$arrTheStyles[]=$arrStyle['style_id'];
				$arrStyle['qeephp_template_base']=$oTheme['theme_dirname'];
				$arrStyle=array_merge($arrStyle,$arrStylevars[$arrStyle['style_id']]);
				$arrStyle['img_dir']=$arrStyle['img_dir']?$arrStyle['img_dir']:'theme/Default/Public/Images';
				$arrStyle['style_img_dir']=$arrStyle['style_img_dir']?$arrStyle['style_img_dir']:$arrStyle['img_dir'];
				$arrStyle['img_dir']=__ROOT__.'/user/'.$arrStyle['img_dir'];
				$arrStyle['style_img_dir']=__ROOT__.'/user/'.$arrStyle['style_img_dir'];
				foreach($arrStyle as $sKey=>$sStyle){
					if($sKey!='menu_hover_bg_color' && substr($sKey,-8,8)=='bg_color'){
						$sNewKey=substr($sKey,0,-8).'bg_code';
						$arrDataNew[$sNewKey]=self::setCssBackground($arrStyle,substr($sKey,0,-6));
					}
				}

				$arrStyle=array_merge($arrStyle,$arrDataNew);
				$arrStyleIcons[$arrStyle['style_id']]=$arrStyle['menu_hover_bg_color'];
				if(strstr($arrStyle['logo'],',')){
					$arrFlash=explode(",",$arrStyle['logo']);
					$arrFlash[0]=trim($arrFlash[0]);
					$arrFlash[0]=preg_match('/^http:\/\//i',$arrFlash[0])?$arrFlash[0]:$arrStyle['style_img_dir'].'/'.$arrFlash[0];
					$arrStyle['logo_str']="<embed src=\"".$arrFlash[0]."\" width=\"".trim($arrFlash[1])."\" height=\"".trim($arrFlash[2])."\" type=\"application/x-shockwave-flash\" wmode=\"transparent\"></embed>";
				}else{
					$arrStyle['logo']=preg_match('/^http:\/\//i',$arrStyle['logo'])?$arrStyle['logo']:$arrStyle['style_img_dir'].'/'.$arrStyle['logo'];
					$arrStyle['logo_str']="<img src=\"".$arrStyle['logo']."\" alt=\"".$GLOBALS['_option_']['site_name']."\" border=\"0\" />";
				}

				$sStyleExtendDir=WINDSFORCE_PATH.'/user/theme/'.$arrStyle['qeephp_template_base'].'/Public/Style';
				if(is_dir($sStyleExtendDir)){
					$arrExtendstyleData=array();
					$arrStyleDirs=C::listDir($sStyleExtendDir);
					$arrExtendstyleData[]=array('',Q::L('默认','__COMMON_LANG__@Common'),$arrStyle['menu_hover_bg_color']);
					foreach($arrStyleDirs as $sStyleDir){
						$sExtendStylefile=$sStyleExtendDir.'/'.$sStyleDir.'/style.css';
						if(is_file($sExtendStylefile)){
							$sContent=file_get_contents($sExtendStylefile);
							if(preg_match('/\[name\](.+?)\[\/name\]/i',$sContent,$arrResult1) &&
								preg_match('/\[iconbgcolor](.+?)\[\/iconbgcolor]/i',$sContent,$arrResult2))
							{
								$arrExtendstyleData[$sStyleDir]=array($sStyleDir,$arrResult1[1],$arrResult2[1]);
							}
						}
					}

					$arrStyleExtendValue=explode('|',$arrStyle['style_extend']);
					$arrStyle['_current_style_']=isset($arrStyleExtendValue[1])?$arrStyleExtendValue[1]:'';

					$arrStyle['_style_extend_icons_']=array();
					$arrStyleExtendValue=explode("\t",$arrStyleExtendValue[0]);
					$arrStyleExtendValue[]=0;
					foreach($arrStyleExtendValue as $sStyleExtendValue){
						if(array_key_exists($sStyleExtendValue,$arrExtendstyleData)){
							$arrStyle['_style_extend_icons_'][$sStyleExtendValue]=$arrExtendstyleData[$sStyleExtendValue];
						}
					}
				}else{
					$arrStyle['_current_style_']='';
					$arrStyle['_style_extend_icons_']=array();
				}
	
				$nContentWidthInt=intval($arrStyle['content_width']);
				$nContentWidthInt=$nContentWidthInt?$nContentWidthInt:600;
				$nImageMaxWidth=$GLOBALS['_option_']['image_max_width'];
				if(substr(trim($nContentWidthInt),-1,1)!='%'){
					if(substr(trim($nImageMaxWidth),-1,1)!='%'){
						$arrStyle['image_max_width']=$nImageMaxWidth>$nContentWidthInt?$nContentWidthInt:$nImageMaxWidth;
					}else{
						$arrStyle['image_max_width']=intval($nContentWidthInt*$nImageMaxWidth/100);
					}
				}else{
					if(substr(trim($nImageMaxWidth),-1,1)!='%'){
						$arrStyle['image_max_width']='%'.$nImageMaxWidth;
					}else{
						$arrStyle['image_max_width']=($nImageMaxWidth>$nContentWidthInt?$nContentWidthInt:$nImageMaxWidth).'%';
					}
				}

				$arrStyle['verhash']=C::randString(6,null,true);
				$arrStyle['__root__']=__ROOT__;
				$arrStyles[intval($arrStyle['style_id'])]=$arrStyle;
			}
		}

		if(is_array($arrCacheStyledir)){
			foreach($arrCacheStyledir as $nKey=>$sValue){
				if(!in_array($sValue,$arrTheStyles)){
					$sCurDeletedstyleDir=WINDSFORCE_PATH.'/~@~/style_/'.$sValue;
					$arrCurDeletedstyleFiles=C::listDir($sCurDeletedstyleDir,true,true);
					foreach($arrCurDeletedstyleFiles as $sCurDeletedstyleFile){
						@unlink($sCurDeletedstyleFile);
					}
					@rmdir($sCurDeletedstyleDir);
				}
			}
		}

		if(is_array($arrStyles)){
			foreach($arrStyles as $arrStyle){
				$arrStyle['_style_icons_']=$arrStyleIcons;
				$sStyleIdPath=WINDSFORCE_PATH.'/~@~/style_/'.intval($arrStyle['style_id']);
				if(!is_dir($sStyleIdPath)&& !C::makeDir($sStyleIdPath)){
					Q::E(Q::L('无法写入缓存文件,请检查缓存目录 %s 的权限是否为0777','__COMMON_LANG__@Common',null,$sStyleIdPath));
				}
				self::writeToCache($sStyleIdPath.'/style.php',$arrStyle);
				self::writetoCssCache($arrStyle,$sStyleIdPath);
			}
		}
	}

	private static function writeToCache($sStylePath,$arrStyle){
		if(!file_put_contents($sStylePath,
			"<?php\n /* WindsForce Style File,Do not to modify this file! */ \n return ".
				var_export($arrStyle,true).
			"\n?>")
		){
			Q::E(Q::L('无法写入缓存文件,请检查缓存目录 %s 的权限是否为0777','__COMMON_LANG__@Common',null,$sStylePath));
		}
	}

	private static function writetoCssCache($arrData=array(),$sStyleIdPath){
		$arrTypes=array();
		$arrTypes[]='@';
		$arrApps=Model::F_('app','app_status=?',1)
			->setColumns('app_identifier')
			->getAll();
		if(is_array($arrApps)){
			foreach($arrApps as $oApp){
				$arrTypes[]=$oApp['app_identifier'];
			}
		}

		$arrCssfiles=array(
			'style'=>array('style','style_append'),
			'common'=>array('common','common_append'),
			'windsforce'=>array('windsforce','windsforce_append'),
		);
		$arrStyleExtendValue=explode('|',$arrData['style_extend']);
		$arrStyleExtendValue=explode("\t",$arrStyleExtendValue[0]);
		foreach($arrStyleExtendValue as $nStyleExtendValue=>$sStyleExtendValue){
			$arrCssfiles['t_'.$sStyleExtendValue]=array($sStyleExtendValue);
		}

		foreach($arrTypes as $sType){
			foreach($arrCssfiles as $sExtra=>$arrCssData){
				$sCssData='';
				foreach($arrCssData as $sCss){
					$bAppcss=false;
					if($sType=='@'){
						$sCssfile=WINDSFORCE_PATH.'/user/theme/'.ucfirst($arrData['qeephp_template_base']).'/Public/Css/'.$sCss.'.css';
						!is_file($sCssfile) && $sCssfile=WINDSFORCE_PATH.'/user/theme/Default/Public/Css/'.$sCss.'.css';
					}elseif(strpos($sExtra,'t_')===0){
						$sCssfile=WINDSFORCE_PATH.'/user/theme/'.ucfirst($arrData['qeephp_template_base']).'/Public/Style/'.$sCss.'/style.css';
					}else{
						$bAppcss=true;
						$sCssfile=WINDSFORCE_PATH.'/System/app/'.$sType.'/Theme/'.ucfirst($arrData['qeephp_template_base']).'/Public/Css/'.$sCss.'.css';
						!is_file($sCssfile) && $sCssfile=WINDSFORCE_PATH.'/System/app/'.$sType.'/Theme/Default/Public/Css/'.$sCss.'.css';
					}

					if(is_file($sCssfile)){
						$sCssData.=file_get_contents($sCssfile);
					}
				}

				if(empty($sCssData)){
					continue;
				}

				// 主题变量替换
				$sCssData=@preg_replace("/\{([A-Z0-9_]+)\}/e",'\$arrData[strtolower(\'\1\')]',stripslashes($sCssData));
				
				// {test.jpg}类似的变量替换
				if($bAppcss===true){
					$sCssData=@preg_replace("/\{([0-9a-zA-Z\_\-\.\/]+)\}/e",'UpdateCacheStyle::imagePath(\'\1\',\''.$sType.'\')',stripslashes($sCssData));
				}
				$sCssData=preg_replace("/<\?.+?\?>\s*/",'',$sCssData);
				$sCssData=preg_replace(array('/\s*([,;:\{\}])\s*/','/[\t\n\r]/','/\/\*.+?\*\//'),array('\\1','',''),$sCssData);
				if(!file_put_contents($sStyleIdPath.'/'.($sType!='@' && strpos($sExtra,'t_')!==0?$sType.'_':'').$sExtra.'.css',stripslashes($sCssData)) && !C::makeDir($sStyleIdPath)){
					Q::E(Q::L('无法写入缓存文件,请检查缓存目录 %s 的权限是否为0777','__COMMON_LANG__@Common',null,$sStyleIdPath));
				}else{
					$arrCurscriptCss=Glob($sStyleIdPath.'/scriptstyle_*.css');
					foreach($arrCurscriptCss as $sCurscriptCss){
						@unlink($sCurscriptCss);
					}
				}
			}
		}
	}

	private static function imagePath($sFile,$sApp){
		if(strpos($sFile,'/')){
			return __ROOT__.'/'.$sFile;
		}else{
			return Appt::path($sFile,$sApp,true);
		}
	}

	private static function setCssBackground(&$arrStyle,$sKey){
		$sCss=$sCodeValue='';
		if(!empty($arrStyle[$sKey.'_color'])){
			$sCss.=strtolower($arrStyle[$sKey.'_color']);
			$sCodeValue=strtoupper($arrStyle[$sKey.'_color']);
		}

		if(!empty($arrStyle[$sKey.'_img'])){
			if(preg_match('/^http:\/\//i',$arrStyle[$sKey.'_img'])){
				$sCss.=' url("'.$arrStyle[$sKey.'_img'].'") ';
			}else{
				$sCss.=' url("'.$arrStyle['style_img_dir'].'/'.$arrStyle[$sKey.'_img'].'") ';
			}
		}

		if(!empty($arrStyle[$sKey.'_extra'])){
			$sCss.=' '.$arrStyle[$sKey.'_extra'];
		}

		$arrStyle[$sKey.'_color']=$sCodeValue;
		$sCss=trim($sCss);
		return $sCss?'background: '.$sCss:'';
	}

}
