<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   活动App入口控制器($$)*/

!defined('Q_PATH') && exit;

class EventmainController extends AController{

	public function index($sModel=null,$bDisplay=true){
		$this->assign('nId',intval(Q::G('id','G')));
		$this->assign('arrOptions',$GLOBALS['_cache_']['event_option']);
		$this->display(Admin_Extend::template('event','eventmain/index'));
	}

	public function update_option(){
		$arrOptions=Q::G('options','P');

		foreach($arrOptions as $sKey=>$val){
			$val=trim($val);
			$oOptionModel=EventoptionModel::F('eventoption_name=?',$sKey)->getOne();
			$oOptionModel->eventoption_value=C::html($val);
			$oOptionModel->save('update');
			if($oOptionModel->isError()){
				$this->E($oOptionModel->getErrorMessage());
			}
		}

		if(!Q::classExists('Cache_Extend')){
			require_once(Core_Extend::includeFile('function/Cache_Extend'));
		}
		Cache_Extend::updateCache('event_option');

		$this->S(Q::L('配置更新成功','__APPEVENT_COMMON_LANG__@Controller'));
	}

}
