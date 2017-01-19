<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   Wap应用($$)*/

!defined('Q_PATH') && exit;

class AppsController extends WInitController{
	
	public function index(){
		$arrAppinfos=array();
		
		$arrApps=Model::F_('app','app_status=?',1)->setColumns('app_id,app_name,app_identifier')->order('app_id DESC')->getAll();
		foreach($arrApps as $arrApp){
			if(is_file(WINDSFORCE_PATH.'/System/app/'.$arrApp['app_identifier'].'/App/Class/Controller/WapController.class.php')){
				$arrAppinfos[$arrApp['app_identifier']]=$arrApp;
				$arrAppinfos[$arrApp['app_identifier']]['logo']=is_file(WINDSFORCE_PATH.'/System/app/'.$arrApp['app_identifier'].'/logo.png')?
					__ROOT__.'/System/app/'.$arrApp['app_identifier'].'/logo.png':
					__ROOT__.'/System/app/logo.png';
			}
		}

		Core_Extend::getSeo($this,array('title'=>Q::L('应用列表','Controller')));
		
		$this->assign('nApps',count($arrAppinfos));
		$this->assign('arrAppinfos',$arrAppinfos);
		$this->display('apps+index');
	}

}
