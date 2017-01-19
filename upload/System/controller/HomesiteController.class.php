<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   前台站点信息($$)*/

!defined('Q_PATH') && exit;

class HomesiteController extends InitController{

	public function aboutus(){
		$this->site_('aboutus');
	}

	public function contactus(){
		$this->site_('contactus');
	}

	public function agreement(){
		$this->site_('agreement');
	}

	public function privacy(){
		$this->site_('privacy');
	}

	public function site_($sName){
		$arrHomesite=Model::F_('homesite',"homesite_name='{$sName}'")->getOne();
		$this->assign('sContent',Core_Extend::replaceSiteVar($arrHomesite['homesite_content']));
		$this->assign('sTitle',$arrHomesite['homesite_nikename']);
		
		$arrHomesites=Model::F_('homesite')->getAll();
		$this->assign('arrHomesites',$arrHomesites);

		Core_Extend::getSeo($this,array('title'=>$arrHomesite['homesite_nikename'],'description'=>$GLOBALS['_option_']['site_name'].$arrHomesite['homesite_nikename']));

		$this->display('homesite+index');
	}

}
