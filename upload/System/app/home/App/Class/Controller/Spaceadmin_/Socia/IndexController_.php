<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   社会化帐号管理($$)*/

!defined('Q_PATH') && exit;

// 导入社会化登录组件
Q::import(WINDSFORCE_PATH.'/System/extension/socialization');

class Index_C_Controller extends InitController{

	public function index(){
		Core_Extend::loadCache('sociatype');

		$arrBindedData=array();
		$arrSociausers=Model::F_('sociauser','user_id=?',$GLOBALS['___login___']['user_id'])
			->setColumns('sociauser_vendor')
			->getAll();
		if(is_array($arrSociausers)){
			foreach($arrSociausers as $oSociauser){
				if(isset($GLOBALS['_cache_']['sociatype'][$oSociauser['sociauser_vendor']])){
					$arrBindedData[]=$oSociauser['sociauser_vendor'];
				}
			}
		}

		Core_Extend::getSeo($this,array('title'=>Q::L('社会化帐号','Controller')));
		
		$this->assign('arrBindedData',$arrBindedData);
		$this->assign('arrBindeds',$GLOBALS['_cache_']['sociatype']);
		$this->display('spaceadmin+socia');
	}

}
