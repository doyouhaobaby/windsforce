<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   界面设置控制器($$)*/

!defined('Q_PATH') && exit;

class StyleoptionController extends OptionController{

	public function index($sModel=null,$bDisplay=true){
		$sLogo=$GLOBALS['_option_']['site_logo']?$GLOBALS['_option_']['site_logo']:__ROOT__.'/user/theme/Default/Public/Images/logo.png';
		$sFavicon=$GLOBALS['_option_']['site_favicon']?$GLOBALS['_option_']['site_favicon']:__ROOT__.'/user/theme/Default/favicon.png';

		$this->assign('arrOptions',$GLOBALS['_option_']);
		$this->assign('sLogo',$sLogo);
		$this->assign('sFavicon',$sFavicon);
		$this->display();
	}

}
