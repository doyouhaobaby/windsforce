<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   语言包缓存($$)*/

!defined('Q_PATH') && exit;

class UpdateCacheLang{

	public static function cache(){
		$arrData=array();
		
		$arrLangs=C::listDir(WINDSFORCE_PATH.'/user/language');
		if(is_array($arrLangs)){
			foreach($arrLangs as $sLang){
				$arrData[]=strtolower($sLang);
			}
		}
		
		Core_Extend::saveSyscache('lang', $arrData);
	}

}
