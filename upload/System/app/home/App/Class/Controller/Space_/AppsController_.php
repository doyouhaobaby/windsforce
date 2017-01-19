<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   其他应用个人空间($$)*/

!defined('Q_PATH') && exit;

class Apps_C_Controller extends InitController{

	public function index(){
		$nId=intval(Q::G('id','G'));
		
		$arrUserInfo=Model::F_('user')
			->setColumns('user_id,user_name')
			->where(array('user_status'=>1,'user_id'=>$nId))
			->getOne();

		if(empty($arrUserInfo['user_id'])){
			$this->E(Q::L('你指定的用户不存在','Controller'));
		}else{
			$this->assign('arrUserInfo',$arrUserInfo);
		}

		$this->_arrUserInfo=$arrUserInfo;

		$arrAppinfos=array();
		$arrApps=Model::F_('app','app_status=? AND app_identifier<>?',1,'wap')->order('app_id DESC')->getAll();
		if(is_array($arrApps)){
			foreach($arrApps as $arrApp){
				if(is_file(WINDSFORCE_PATH.'/System/app/'.$arrApp['app_identifier'].'/App/Class/Controller/SpaceController.class.php')){
					$arrAppinfos[$arrApp['app_identifier']]=$arrApp;
					$arrAppinfos[$arrApp['app_identifier']]['logo']=is_file(WINDSFORCE_PATH.'/System/app/'.$arrApp['app_identifier'].'/logo.png')?
						__ROOT__.'/System/app/'.$arrApp['app_identifier'].'/logo.png':
						__ROOT__.'/System/app/logo.png';
				}
			}
		}

		Core_Extend::getSeo($this,array('title'=>$arrUserInfo['user_name'].' - '.Q::L('应用个人空间','Controller')));

		$this->assign('arrAppinfos',$arrAppinfos);
		$this->assign('nId',$nId);
		$this->display('space+apps');
	}

}
