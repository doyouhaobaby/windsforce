<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   国际化语言包处理控制器($$)*/

!defined('Q_PATH') && exit;

class LanguageoptionController extends OptionController{

	public function index($sModel=null,$bDisplay=true){
		$arrAdminlanguages=C::listDir(APP_PATH.'/App/Lang');
		Core_Extend::loadCache('lang');

		$this->assign('arrAdminlanguages',$arrAdminlanguages);
		$this->assign('sCurrentAdminlanguage',$GLOBALS['_option_']['admin_language_name']);
		$this->assign('arrFrontlanguages',$GLOBALS['_cache_']['lang']);
		$this->assign('sCurrentFrontlanguage',$GLOBALS['_option_']['front_language_name']);
		$this->assign('arrOptions',$GLOBALS['_option_']);
		$this->display();
	}

	public function update_option(){
		$arrOptions=Q::G('options','P');
		$sAdminlanguage=trim($arrOptions['admin_language_name']);
		$sFrontlanguage=trim($arrOptions['front_language_name']);
		$nFrontlanguageSwitch=intval($arrOptions['language_switch_on']);

		// 修改配置
		Core_Extend::updateOption(
			array(
				'admin_language_name'=>strtolower($sAdminlanguage),
				'front_language_name'=>strtolower($sFrontlanguage),
				'language_switch_on'=>$nFrontlanguageSwitch
			)
		);
		
		Core_Extend::updateOption(
			array(
				'admin_language_name'=>strtolower($sAdminlanguage),
				'front_language_name'=>strtolower($sFrontlanguage),
				'language_switch_on'=>$nFrontlanguageSwitch
			)
		);
		
		Core_Extend::changeAppconfig(
			array(
				'ADMIN_LANG_DIR'=>ucfirst($sAdminlanguage),
				'FRONT_LANG_DIR'=>ucfirst($sFrontlanguage),
				'LANG_SWITCH'=>$nFrontlanguageSwitch==1?true:false,
			)
		);
		
		Q::cookie('admin_language',strtolower($sAdminlanguage));
		Q::cookie('language',strtolower($sFrontlanguage));
		
		$this->S(Q::L('修改国际化语言成功','Controller'));
	}

}
