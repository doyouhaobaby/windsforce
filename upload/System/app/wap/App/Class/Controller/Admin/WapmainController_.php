<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   Wap入口控制器($$)*/

!defined('Q_PATH') && exit;

class WapmainController extends AController{

	public function index($sModel=null,$bDisplay=true){
		$this->assign('arrOptions',$GLOBALS['_option_']);
		$this->display(Admin_Extend::template('wap','wapmain/index'));
	}

	public function update_option(){
		if(isset($_POST['options'])){
			$arrOptions=Q::G('options','P');
		}else{
			$arrOptions=array();
		}

		foreach($arrOptions as $sKey=>$val){
			$val=trim($val);
			$oOptionModel=OptionModel::F('option_name=?',$sKey)->getOne();
			$oOptionModel->option_value=$val;
			$oOptionModel->save('update');
			if($oOptionModel->isError()){
				$this->E($oOptionModel->getErrorMessage());
			}
		}

		if(!Q::classExists('Cache_Extend')){
			require_once(Core_Extend::includeFile('function/Cache_Extend'));
		}
		Cache_Extend::updateCache('option');

		$this->S(Q::L('配置更新成功','Controller'));
	}

}
