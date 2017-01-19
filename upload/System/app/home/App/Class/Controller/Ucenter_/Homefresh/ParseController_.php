<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   新鲜事URL解析($$)*/

!defined('Q_PATH') && exit;

class Parse_C_Controller extends InitController{

	public function index(){
		$sType=trim(Q::G('type'));
		$sUrl=trim(Q::G('url'));
		$sDesc=trim(Q::G('desc'));

		$arrSupportType=array('music','video','movie');
		if(!in_array($sType,$arrSupportType)){
			exit(json_encode(array('error'=>Q::L('不支持的解析类型','Controller'))));
		}

		/** 导入URL解析组件 */
		require_once(Core_Extend::includeFile('class/UrlParse'));

		$sMethod='set'.$sType;
		$arrReturn=Q::instance('UrlParse')->$sMethod($sUrl,$sDesc);
		echo json_encode($arrReturn);
	}

}
