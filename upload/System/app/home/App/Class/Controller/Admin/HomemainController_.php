<?php
/* [$WindsForce] (C)WindsForce TEAM Since 2012.03.17.
   主页入口控制器($$)*/

!defined('Q_PATH') && exit;

class HomemainController extends AController{

	public function index($sModel=null,$bDisplay=true){
		$sType=trim(Q::G('type','G'));
		$this->assign('nId',intval(Q::G('id','G')));
		$this->assign('arrOptions',$GLOBALS['_cache_']['home_option']);
		$this->display(Admin_Extend::template('home','homeoption/'.($sType?$sType:'index')));
	}

	public function update_option(){
		$arrOptions=Q::G('options','P');

		foreach($arrOptions as $sKey=>$val){
			$val=trim($val);
			if(in_array($sKey,array('homefreshcomment_limit_num','homefreshchildcomment_limit_num','homefreshchildcomment_list_num'))){
				if($val<1){
					$val=4;
				}
			}
			
			$oOptionModel=HomeoptionModel::F('homeoption_name=?',$sKey)->getOne();
			$oOptionModel->homeoption_value=C::html($val);
			$oOptionModel->save('update');
		}

		if(!Q::classExists('Cache_Extend')){
			require_once(Core_Extend::includeFile('function/Cache_Extend'));
		}
		Cache_Extend::updateCache('home_option');

		$this->S(Q::L('配置更新成功','__APPHOME_COMMON_LANG__@Controller'));
	}

}
