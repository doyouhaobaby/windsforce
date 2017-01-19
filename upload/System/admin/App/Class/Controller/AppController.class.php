<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   应用管理控制器($$)*/

!defined('Q_PATH') && exit;

class AppController extends AController{
	
	public function bIndex_(){
		$arrOptionData=$GLOBALS['_option_'];
		
		// 默认应用
		if(is_file(WINDSFORCE_PATH.'/~@~/Config.inc.php')){
			$arrConfigs=(array)(include(WINDSFORCE_PATH.'/~@~/Config.inc.php'));
			$sDefaultAppname=isset($arrConfigs['DEFAULT_APP'])?$arrConfigs['DEFAULT_APP']:'home';
			unset($arrConfigs);
		}else{
			$sDefaultAppname=$arrOptionData['default_app'];
		}

		$this->assign('sDefaultAppname',$sDefaultAppname);
	}

	public function filter_(&$arrMap){
		$arrMap['A.app_name']=array('like',"%".Q::G('app_name')."%");
		$arrMap['A.app_description']=array('like',"%".Q::G('app_description')."%");
		$arrMap['A.app_identifier']=array('like',"%".Q::G('app_identifier')."%");
		$arrMap['A.app_url']=array('like',"%".Q::G('app_url')."%");
		$arrMap['A.app_author']=array('like',"%".Q::G('app_author')."%");
		$arrMap['A.app_email']=array('like',"%".Q::G('app_email')."%");
	}

	public function AInsertObject_($oModel){
		$oModel->filterAppindentifier();
	}

	public function config(){
		$nId=intval(Q::G('id'));
		if(empty($nId)){
			$this->E(Q::L('你没有指定待设置的应用','Controller'));
		}else{
			$arrAppModel=AppModel::F('app_id=?',$nId)->getOne();
			if(empty($arrAppModel['app_id'])){
				$this->assign('__JumpUrl__',Q::U('app/index'));
				$this->E(Q::L('你指定待设置的应用不存在','Controller'));
			}
			
			if(!$arrAppModel['app_status']){
				$this->assign('__JumpUrl__',Q::U('app/index'));
				$this->E(Q::L('你指定待设置的应用尚未启用','Controller'));
			}

			// 定义应用的语言包
			define('__APP'.strtoupper($arrAppModel['app_identifier']).'_COMMON_LANG__',WINDSFORCE_PATH.'/System/app/'.$arrAppModel['app_identifier'].'/App/Lang/Admin');

			// 导入应用扩展函数
			$sExtensionDir=WINDSFORCE_PATH.'/System/app/'.$arrAppModel['app_identifier'].'/App/Class/Extension';
			if(is_dir($sExtensionDir)){
				Q::import($sExtensionDir);
			}

			// 导入应用模型
			$sModelDir=WINDSFORCE_PATH.'/System/app/'.$arrAppModel['app_identifier'].'/App/Class/Model';
			if(is_dir($sModelDir)){
				Q::import($sModelDir);
			}
			
			$this->assign('nId',$nId);
			$this->assign('arrAppModel',$arrAppModel);
			
			$sController=trim(Q::G('controller','G'));
			$sAction=strtolower(trim(Q::G('action','G')));

			// 查找模块
			if(empty($sController)){
				$sController=ucfirst($arrAppModel['app_identifier']).'mainController';
				$_GET['controller']=strtolower($arrAppModel['app_identifier']).'main';
			}else{
				$sController=ucfirst($sController).'Controller';
			}
			
			// 查找方法
			if(empty($sAction)){
				$sAction='index';
				$_GET['action']='index';
			}
			
			$sControllerPath=WINDSFORCE_PATH.'/System/app/'.$arrAppModel['app_identifier'].'/App/Class/Controller/Admin/'.$sController.'_.php';
			if(is_file($sControllerPath)){
				// 加载缓存
				if(Q::classExists(ucfirst($arrAppModel['app_identifier']).'optionModel')){
					Core_Extend::loadCache($arrAppModel['app_identifier'].'_option');
				}

				require_once($sControllerPath);
				$oController=null;
				eval('$oController=Q::instance(\''.$sController.'\');');
				
				$callback=array($oController,$sAction);
				if(is_callable($callback)){
					call_user_func($callback);
				}else{
					$this->assign('__JumpUrl__',Q::U('app/index'));
					$this->E(Q::L('后台管理模块 %s 回调不存在','Controller',null,C::dump($callback,false)));
				}

				exit();
			}else{
				$this->assign('__JumpUrl__',Q::U('app/index'));
				$this->E(Q::L('后台管理模块文件 %s 不存在','Controller',null,str_replace(C::tidyPath(WINDSFORCE_PATH),'{WINDSFORCE_PATH}',C::tidyPath($sControllerPath))));
			}
		}
	}

	public function bEdit_(){
		$this->check_appdevelop();
	}

	public function bAdd_(){
		$this->bEdit_();
	}

	public function disable(){
		$this->check_();
		$this->change_status_('status',0,'app');
	}

	protected function aForbid(){
		$this->updatecachenav_();
		$this->updatecacheapp_();
	}

	public function enable(){
		$this->check_();
		$this->change_status_('status',1,'app');
	}

	protected function aResume(){
		$this->aForbid();
	}

	protected function aInsert($nId=null){
		$this->aForbid();
	}

	protected function aUpdate($nId=null){
		$this->aForbid();
	}
	
	public function aForeverdelete_deep($sId){
		$this->aForbid();
	}

	public function aForeverdelete($sId){
		$this->aForbid();
	}

	public function export(){
		$nAppId=intval(Q::G('id','G'));
		if(empty($nAppId)){
			$this->E(Q::L('你没有指定待设置的应用','Controller'));
		}else{
			$arrApp=AppModel::F('app_id=?',$nAppId)->asArray()->getOne();
			if(empty($arrApp['app_id'])){
				$this->E(Q::L('你指定待设置的应用不存在','Controller'));
			}
			unset($arrApp['app_id']);
			unset($arrApp['app_status']);

			$arrAppData=array();
			$arrAppData['title']='WindsForce App';
			$arrAppData['version']=WINDSFORCE_SERVER_VERSION;
			$arrAppData['time']=WINDSFORCE_SERVER_RELEASE;
			$arrAppData['copyright']='WindsForce Team';

			foreach($arrApp as $key=>$value){
				$arrAppData['data'][str_replace('app_','',$key)]=$value;
			}

			$sName='APP_'.$arrApp['app_identifier'].'_'.date('Y_m_d_H_i_s',CURRENT_TIMESTAMP).'.xml';
			$arrAppData=C::stripslashes($arrAppData);
			$sXmlData=Xml::xmlSerialize($arrAppData,true);

			header('Content-type: text/xml');
			header('Content-Disposition: attachment; filename="'.$sName.'"');

			exit($sXmlData);
		}
	}

	public function nav(){
		$nId=intval(Q::G('id'));
		if(empty($nId)){
			$this->E(Q::L('你没有指定待设置的应用','Controller'));
		}else{
			$oApp=AppModel::F('app_id=?',$nId)->query();
			if(empty($oApp['app_id'])){
				$this->E(Q::L('你指定待设置的应用不存在','Controller'));
			}

			// 判断菜单是否已经存在
			$oTryNav=NavModel::F('nav_identifier=?','app_'.$oApp['app_identifier'])->getOne();
			if(!empty($oTryNav['nav_id'])){
				$this->E(Q::L('菜单已经存在','Controller'));
			}

			// 将菜单数据写入
			$oNav=new NavModel();
			$oNav->nav_title=$oApp['app_identifier'];
			$oNav->nav_name=$oApp['app_name'];
			$oNav->nav_url=$oApp['app_identifier'].'://public/index';
			$oNav->nav_identifier='app_'.$oApp['app_identifier'];
			$oNav->nav_status=1;
			$oNav->save();
			if($oNav->isError()){
				$this->E($oNav->getErrorMessage());
			}else{
				$this->updatecachenav_();
				$this->S(Q::L('菜单写入成功','Controller'));
			}
		}
	}

	public function unnav(){
		$nId=intval(Q::G('id'));
		if(empty($nId)){
			$this->E(Q::L('你没有指定待设置的应用','Controller'));
		}else{
			$oApp=AppModel::F('app_id=?',$nId)->query();
			if(empty($oApp['app_id'])){
				$this->E(Q::L('你指定待设置的应用不存在','Controller'));
			}

			// 判断菜单是否已经存在
			$oTryNav=NavModel::F('nav_identifier=?','app_'.$oApp['app_identifier'])->getOne();
			if(empty($oTryNav['nav_id'])){
				$this->E(Q::L('菜单已经被取消','Controller'));
			}else{
				$oTryNav->destroy();
			}

			$this->updatecachenav_();
			$this->S(Q::L('菜单取消成功','Controller'));
		}
	}

	public function app_nav_exists($nAppId){
		$nAppId=intval($nAppId);
		if(empty($nAppId)){
			return false;
		}else{
			$oApp=AppModel::F('app_id=?',$nAppId)->query();
			if(empty($oApp['app_id'])){
				return false;
			}

			// 判断菜单是否已经存在
			$oTryNav=NavModel::F('nav_identifier=?','app_'.$oApp['app_identifier'])->getOne();
			if(!empty($oTryNav['nav_id'])){
				return true;
			}

			return false;
		}
	}

	public function check_identifier(){
		$sAppIdentifier=trim(Q::G('app_identifier'));
		$nId=intval(Q::G('id'));

		if(!$sAppIdentifier){
			exit('false');
		}

		// 查询条件
		$arrWhere=array();
		$arrWhere['app_identifier']=$sAppIdentifier;
		if($nId){
			$arrWhere['app_id']=array('neq',$nId);
		}

		$oApp=AppModel::F()->where($arrWhere)->setColumns('app_id')->getOne();
		if(empty($oApp['app_id'])){
			exit('true');
		}else{
			exit('false');
		}
	}

	protected function updatecachenav_(){
		$bIsFilecache=$GLOBALS['_commonConfig_']['RUNTIME_CACHE_BACKEND'];
		$bAllowMem=Core_Extend::memory('check');
		$bAllowMem && self::memory('delete','nav');

		$sCachefile=WINDSFORCE_PATH.'/~@~/data/~@nav.php';
		$bIsFilecache && (is_file($sCachefile) && @unlink($sCachefile));
	}

	protected function check_(){
		if($GLOBALS['___login___']['user_id']!=1){
			$this->E(Q::L('只有用户ID为1的超级管理员才能够访问本页','Controller'));
		}
	}

	protected function updatecacheapp_($nId=null){
		if(!Q::classExists('Cache_Extend')){
			require_once(Core_Extend::includeFile('function/Cache_Extend'));
		}
		Cache_Extend::updateCache('app');
		Cache_Extend::updateCache('apps');
	}
	
}
