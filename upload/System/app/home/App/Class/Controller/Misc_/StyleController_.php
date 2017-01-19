<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   主题切换控制器($$)*/

!defined('Q_PATH') && exit;

class Style_C_Controller extends InitController{

	public function index(){
		if($GLOBALS['_option_']['style_switch_on']==0){
			$this->E(Q::L('系统已经关闭了主题切换功能','Controller'));
		}

		$nStyleId=intval(Q::G('id','G'));
		if(empty($nStyleId)){
			$this->E(Q::L('主题切换失败','Controller'));
		}

		$arrStyle=Model::F_('style','style_id=? AND style_status=1',$nStyleId)->setColumns('style_id')->getOne();
		if(empty($arrStyle['style_id'])){
			$this->E(Q::L('主题切换失败','Controller'));
		}

		$arrTheme=Model::F_('theme','theme_id=?',$arrStyle['theme_id'])->setColumns('theme_id,theme_dirname')->getOne();
		if(empty($arrTheme['theme_id'])){
			$this->E(Q::L('主题切换失败','Controller'));
		}

		$sThemeDir=WINDSFORCE_PATH.'/user/theme/'.ucfirst(strtolower($arrTheme['theme_dirname']));
		if(!is_dir($sThemeDir)){
			$this->E(Q::L('主题切换失败','Controller'));
		}

		// 发送主题COOKIE
		Q::cookie('style_id',$nStyleId);
		Q::cookie('template',ucfirst(strtolower($arrTheme['theme_dirname'])));
		Q::cookie('extend_style_id',isset($GLOBALS['_style_']['_current_style_'])?$GLOBALS['_style_']['_current_style_']:'');

		$this->S(Q::L('主题切换成功','Controller'));
	}

}
