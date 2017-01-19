<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   主题管理类($$)*/

!defined('Q_PATH') && exit;

class Style{

	public $_arrOkStyles=array();
	public $_arrBrokenStyles=array();

	public function getStyles($arrCurrentStyles){
		$arrOkStyles=array();
		$arrBrokenStyles=array();

		foreach((array)$arrCurrentStyles as $sStyle){
			$sStylePath=is_file($sStyle.'/Public/Css/style.css')?$sStyle.'/Public/Css/style.css':$sStyle.'/Public/Css/style_append.css';

			$arrTemps=Q::normalize(C::tidyPath($sStyle),'/');
			$sStyleDir=end($arrTemps);
			unset($arrTemps);

			if(!is_file($sStylePath)){
				$arrBrokenStyles[]=array('Name'=>$sStyleDir,'Path'=>str_replace(C::tidyPath(WINDSFORCE_PATH),'{WINDSFORCE_PATH}',C::tidyPath($sStylePath)),'Description'=>Q::L('主题样式表丢失','__COMMON_LANG__@Common'));
				continue;
			}

			if(!is_readable($sStylePath)){
				$arrBrokenStyles[]=array('Name'=>$sStyleDir,'Path'=>str_replace(C::tidyPath(WINDSFORCE_PATH),'{APP_PATH}',C::tidyPath($sStylePath)),'Description'=>Q::L('主题样式表不可读','__COMMON_LANG__@Common'));
				continue;
			}

			$arrStyleData=$this->getStyleData($sStylePath);
			if($arrStyleData===false){
				$arrBrokenStyles[]=array('Name'=>$sStyleDir,'Path'=>str_replace(C::tidyPath(WINDSFORCE_PATH),'{APP_PATH}',C::tidyPath($sStylePath)),'Description'=>Q::L('主题样式表已经损坏','__COMMON_LANG__@Common'));
				continue;
			}

			$sName=$arrStyleData['Name'];
			$sTitle=$arrStyleData['Title'];
			$sDescription=$arrStyleData['Description'];
			$nVersion=$arrStyleData['Version'];
			$sAuthor=$arrStyleData['Author'];
			$sStyle=$arrStyleData['Style'];
			$sStylesheet=dirname(dirname(dirname($sStylePath)));
			$arrPreviews=array(
				'_mini'=>'',
				''=>'',
				'_large'=>''
			);
			
			$sPreview=$sPreviewMini=$sPreviewLarge='';

			foreach($arrPreviews as $sKey=>$sType){
				foreach(array('png','gif','jpg','jpeg') as $sExt){
					if(is_file("{$sStylesheet}/windsforce_preview{$sType}.{$sExt}")){
						$arrPreviews[$sKey]="windsforce_preview{$sKey}.{$sExt}";
					}
				}

				if(empty($arrPreviews[$sKey])){
					$arrPreviews[$sKey]=$this->getNoneimg($sKey);
				}
			}

			if(empty($sName)){
				$sName=dirname(dirname(dirname($sStylePath)));
				$sTitle=$sName;
			}

			if(empty($sStyle)){
				$sStyle=$sStyleDir;
			}

			$arrOkStyles[$sStyle]=array(
				'Name'=>$sName,
				'Title'=>$sTitle,
				'Description'=>$sDescription,
				'Author'=>$sAuthor,
				'Author Name'=>$arrStyleData['AuthorName'],
				'Author URI'=>$arrStyleData['AuthorURI'],
				'Version'=>$nVersion,
				'Style'=>$sStyle,
				'Stylesheet'=>str_replace(C::tidyPath(WINDSFORCE_PATH),'{WINDSFORCE_PATH}',C::tidyPath($sStylePath)),
				'Style Dir'=>str_replace(C::tidyPath(WINDSFORCE_PATH),'{WINDSFORCE_PATH}',C::tidyPath($sStylesheet)),
				'Stylesheet Dir'=>str_replace(C::tidyPath(WINDSFORCE_PATH),'{WINDSFORCE_PATH}',C::tidyPath($sStyle.'/Public/Css/')),
				'Status'=>$arrStyleData['Status'],
				'preview'=>$arrPreviews[''],
				'preview_mini'=>$arrPreviews['_mini'],
				'preview_large'=>$arrPreviews['_large'],
				'Tags'=>$arrStyleData['Tags'],
				'Style Root'=>str_replace(C::tidyPath(WINDSFORCE_PATH),'{WINDSFORCE_PATH}',C::tidyPath(dirname($sStylesheet))),
				'Style Root URI'=>str_replace(C::tidyPath(WINDSFORCE_PATH),'{WINDSFORCE_PATH}',C::tidyPath(dirname($sStylesheet))),
			);
		}

		unset($arrCurrentStyles);

		$this->_arrOkStyles=$arrOkStyles;
		$this->_arrBrokenStyles=$arrBrokenStyles;

		return true;
	}
	
	public function getStyleData($sStylePath){
		$arrDefaultHeaders=array(
			'Name'=>'Style Name',
			'URI'=>'Style URI',
			'Description'=>'Description',
			'Author'=>'Author',
			'AuthorURI'=>'Author URI',
			'Version'=>'Version',
			'Style'=>'Style',
			'Status'=>'Status',
			'Tags'=>'Tags'
		);

		$arrStyleData=$this->getFileData($sStylePath,$arrDefaultHeaders);

		$arrTestTemp=Q::normalize($arrStyleData);
		if(empty($arrTestTemp)){
			return false;
		}

		$arrStyleData['Name']=$arrStyleData['Title']=strip_tags($arrStyleData['Name']);
		$arrStyleData['URI']=htmlspecialchars($arrStyleData['URI']);
		$arrStyleData['Description']=htmlspecialchars($arrStyleData['Description']);
		$arrStyleData['AuthorURI']=htmlspecialchars($arrStyleData['AuthorURI']);
		$arrStyleData['Style']=htmlspecialchars($arrStyleData['Style']);
		$arrStyleData['Version']=htmlspecialchars($arrStyleData['Version']);

		if($arrStyleData['Status']==''){
			$arrStyleData['Status']='publish';
		}else{
			$arrStyleData['Status']=$arrStyleData['Status'];
		}

		if($arrStyleData['Tags']==''){
			$arrStyleData['Tags']=array();
		}else{
			$arrStyleData['Tags']=array_map('trim',explode(',',htmlspecialchars($arrStyleData['Tags'])));
		}

		if($arrStyleData['Author']==''){
			$arrStyleData['Author']=$arrStyleData['AuthorName']='NoBoby';
		}else{
			$arrStyleData['AuthorName']=htmlspecialchars($arrStyleData['Author']);
			
			if(empty($arrStyleData['AuthorURI'])){
				$arrStyleData['Author']=$arrStyleData['AuthorName'];
			}else{
				$arrStyleData['Author']=sprintf('<a href="%1$s" title="%2$s">%3$s</a>',$arrStyleData['AuthorURI'],Q::L('访问作者的主页','__COMMON_LANG__@Common'),$arrStyleData['AuthorName']);
			}
		}

		return $arrStyleData;
	}

	public function getNoneimg($sKey){
		return "windsforce_preview{$sKey}.png";
	}

	public function cleanupHeadercomment($sValue){
		return trim(preg_replace("/\s*(?:\*\/|\?>).*/",'',$sValue));
	}

	public function getFileData($sFile,$arrDefaultHeaders){
		$hFp=fopen($sFile,'r');
		$sFileData=fread($hFp,8192);
		fclose($hFp);

		$arrHeaders=&$arrDefaultHeaders;
		foreach($arrHeaders as $sField=>$sRegex){
			preg_match('/^[\t\/*#@]*'.preg_quote($sRegex,'/').':(.*)$/mi',$sFileData,${$sField});
			if(!empty(${$sField})){
				${$sField}=$this->cleanupHeaderComment(${$sField}[1]);
			}else{
				${$sField}='';
			}
		}

		return compact(array_keys($arrHeaders));
	}

}
