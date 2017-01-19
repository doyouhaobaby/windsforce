<?php
/* [$QeePHP] (C)WindsForce TEAM Since 2010.10.04.
   URL行为扩展类($$)*/

!defined('Q_PATH') && exit;

class SpecialurlBehavior{

	public function RUN($arrParam=array()){
		// 分析出二级域名
		$arrTemp=explode('.',$_SERVER['HTTP_HOST']);
		array_pop($arrTemp);
		array_pop($arrTemp);
		$sSubDomain=array_pop($arrTemp);

		if(APP_NAME==='jiaju' && in_array($sSubDomain,array('so','vip'))){
			$_GET['c']='shop';
		}
	}

}
